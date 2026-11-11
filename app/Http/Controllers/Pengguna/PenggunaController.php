<?php

namespace App\Http\Controllers\Pengguna;

use App\Http\Controllers\Controller;
use App\Models\AlamatPengguna;
use App\Models\JenisSampah;
use App\Models\Konten;
use App\Models\Notifikasi;
use App\Models\Penjemputan;
use App\Models\Setoran;
use App\Models\SetoranDetail;
use App\Models\Tps;
use App\Models\TpsJenisSampah;
use App\Models\TransaksiPoin;
use App\Models\Ulasan;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    private function setAppUserId(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('SET @app_user_id = ?', [auth()->id()]);
        }
    }

    /**
     * Dashboard Utama Penghasil / Pengguna Limbah Medis & B3
     */
    public function index(): View
    {
        $user = auth()->user();

        $setoranAktif = Setoran::with([
            'tps',
            'detail.jenisSampah',
            'penjemputan.kurirUser',
            'penjemputan.kurir',
            'penjemputan.alamat',
            'statusLog',
        ])
            ->where('pengguna_id', $user->user_id)
            ->whereIn('status', ['menunggu_kurir', 'kurir_ditugaskan', 'diangkut', 'menuju_tps', 'tiba_di_tps'])
            ->latest()
            ->get();

        $setoranTerakhir = Setoran::with(['tps', 'detail.jenisSampah', 'ulasan'])
            ->where('pengguna_id', $user->user_id)
            ->latest()
            ->limit(5)
            ->get();

        $totalSelesai = Setoran::where('pengguna_id', $user->user_id)
            ->where('status', 'selesai')
            ->count();

        $totalBeratKg = (float) Setoran::where('pengguna_id', $user->user_id)
            ->where('status', 'selesai')
            ->sum('total_berat_aktual_kg');

        $ringkasan = [
            'saldo_poin'       => $user->saldoPoin(),
            'manifes_aktif'    => $setoranAktif->count(),
            'manifes_selesai'  => $totalSelesai,
            'total_berat_kg'   => $totalBeratKg,
            'jumlah_alamat'    => $user->alamat()->count(),
        ];

        $notifikasiBelumBaca = Notifikasi::where('penerima_id', $user->user_id)
            ->belumDibaca()
            ->latest('created_at')
            ->limit(6)
            ->get();

        $daftarTps = Tps::where('is_verified', true)
            ->orderByDesc('izin_limbah_medis')
            ->orderBy('nama_tps')
            ->get();

        $kontenPanduan = Konten::terbit()
            ->orderByDesc('published_at')
            ->limit(2)
            ->get();

        return view('pengguna.dashboard', compact(
            'user',
            'ringkasan',
            'setoranAktif',
            'setoranTerakhir',
            'notifikasiBelumBaca',
            'daftarTps',
            'kontenPanduan'
        ));
    }

    /**
     * Halaman Formulir Buat Manifes Setoran Baru (Medis, B3 & Daur Ulang)
     */
    public function buatSetoran(): View
    {
        $user = auth()->user();

        $daftarTps = Tps::with('jenisSampah')
            ->where('is_verified', true)
            ->where('status', 'buka')
            ->orderByDesc('izin_limbah_medis')
            ->orderBy('nama_tps')
            ->get();

        $jenisSampah = JenisSampah::orderByRaw("FIELD(kategori, 'medis', 'b3', 'elektronik', 'anorganik', 'organik')")
            ->orderByDesc('perlu_penanganan_khusus')
            ->orderByDesc('poin_per_kg')
            ->get();

        $daftarAlamat = $user->alamat()
            ->orderByDesc('is_default')
            ->orderByDesc('alamat_id')
            ->get();

        return view('pengguna.setoran-baru', compact('user', 'daftarTps', 'jenisSampah', 'daftarAlamat'));
    }

    /**
     * Simpan Manifes Setoran Baru ke Database
     */
    public function storeSetoran(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'tps_id'              => ['required', 'exists:tps,tps_id'],
            'metode'              => ['required', 'in:dijemput,antar_sendiri'],
            'alamat_id'           => ['nullable', 'required_if:metode,dijemput', 'exists:alamat_pengguna,alamat_id'],
            'jadwal_waktu'        => ['nullable', 'date', 'after_or_equal:' . now()->subMinutes(5)->format('Y-m-d H:i:s')],
            'catatan_pengguna'    => ['nullable', 'string', 'max:255'],
            'pernyataan_sop'      => ['required', 'accepted'],
            'berat_estimasi'      => ['required', 'array'],
            'berat_estimasi.*'    => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'catatan_item'        => ['nullable', 'array'],
            'catatan_item.*'      => ['nullable', 'string', 'max:200'],
        ], [
            'alamat_id.required_if'   => 'Pilih alamat titik penjemputan terlebih dahulu untuk metode Dijemput Kurir.',
            'jadwal_waktu.after_or_equal' => 'Waktu penjemputan/kedatangan tidak boleh di waktu yang sudah lewat.',
            'pernyataan_sop.required' => 'Anda wajib menyetujui pernyataan standar kemasan SOP K3 sebelum menerbitkan manifes.',
            'pernyataan_sop.accepted' => 'Anda wajib menyetujui pernyataan standar kemasan SOP K3 sebelum menerbitkan manifes.',
        ]);

        // Ambil hanya item dengan jumlah > 0
        $itemTerpilih = collect($validated['berat_estimasi'])
            ->map(fn ($val) => (float) $val)
            ->filter(fn ($berat) => $berat > 0);

        if ($itemTerpilih->isEmpty()) {
            return back()
                ->withInput()
                ->with('galat', 'Isi jumlah (minimal 1 item) pada setidaknya satu jenis limbah medis atau B3 yang diserahkan.');
        }

        $tps = Tps::findOrFail($validated['tps_id']);
        if (! $tps->is_verified || $tps->status !== 'buka') {
            return back()
                ->withInput()
                ->with('galat', 'Fasilitas tujuan sedang tutup atau belum terverifikasi.');
        }

        $sisaKuotaKg = max(0.0, (float) $tps->kapasitas_total_kg - (float) $tps->kapasitas_terpakai_kg);
        $totalBeratEstimasi = (float) $itemTerpilih->sum();
        if ($totalBeratEstimasi > $sisaKuotaKg) {
            return back()
                ->withInput()
                ->with('galat', sprintf(
                    'Total jumlah item (%s item) melebihi sisa kuota daya tampung %s (%s item). Kurangi jumlah item atau pilih fasilitas lain.',
                    number_format($totalBeratEstimasi, 0, ',', '.'),
                    $tps->nama_tps,
                    number_format($sisaKuotaKg, 0, ',', '.')
                ));
        }

        // Cek apakah ada kategori medis tetapi TPS belum berizin medis
        $jenisMap = JenisSampah::whereIn('jenis_id', $itemTerpilih->keys())->get()->keyBy('jenis_id');
        $adaLimbahMedis = $jenisMap->contains(fn ($j) => $j->kategori === 'medis');

        if ($adaLimbahMedis && ! $tps->izin_limbah_medis) {
            return back()
                ->withInput()
                ->with('galat', 'Setoran Anda mengandung Limbah Medis. Pilih Fasilitas Penampungan yang memiliki lencana "Berizin Resmi Limbah Medis & B3".');
        }

        // Pastikan kepemilikan alamat jika dijemput
        if ($validated['metode'] === 'dijemput') {
            $alamatMilikUser = AlamatPengguna::where('alamat_id', $validated['alamat_id'])
                ->where('user_id', $user->user_id)
                ->exists();

            if (! $alamatMilikUser) {
                return back()->withInput()->with('galat', 'Alamat penjemputan tidak valid.');
            }
        }

        try {
            $this->setAppUserId();

            $setoran = DB::transaction(function () use ($user, $tps, $validated, $itemTerpilih, $jenisMap) {
                // Hasilkan kode unik STR-YYYYMMDD-XXXX
                $tanggal = now()->format('Ymd');
                $urutHariIni = Setoran::whereDate('created_at', today())->lockForUpdate()->count() + 1;
                $kodeSetoran = sprintf('STR-%s-%04d', $tanggal, $urutHariIni);
                while (Setoran::where('kode_setoran', $kodeSetoran)->exists()) {
                    $urutHariIni++;
                    $kodeSetoran = sprintf('STR-%s-%04d', $tanggal, $urutHariIni);
                }

                $statusAwal = $validated['metode'] === 'dijemput' ? 'menunggu_kurir' : 'menuju_tps';
                $waktuJadwal = ! empty($validated['jadwal_waktu']) ? $validated['jadwal_waktu'] : now()->addHours(2);

                $setoranBaru = Setoran::create([
                    'kode_setoran'     => $kodeSetoran,
                    'pengguna_id'      => $user->user_id,
                    'tps_id'           => $tps->tps_id,
                    'metode'           => $validated['metode'],
                    'status'           => $statusAwal,
                    'estimasi_tiba'    => $validated['metode'] === 'antar_sendiri' ? $waktuJadwal : null,
                    'catatan_pengguna' => $validated['catatan_pengguna'] ?? null,
                ]);

                foreach ($itemTerpilih as $jenisId => $beratKg) {
                    $jenisObj = $jenisMap->get($jenisId);
                    if (! $jenisObj) {
                        continue;
                    }

                    // Pastikan baris kuota tps_jenis_sampah tersedia agar trigger trg_setoran_detail_bi tidak gagal jika admin/pengelola belum menginisialisasi petak
                    $petak = TpsJenisSampah::firstOrCreate(
                        [
                            'tps_id'   => $tps->tps_id,
                            'jenis_id' => $jenisObj->jenis_id,
                        ],
                        [
                            'kapasitas_maks_kg' => 300,
                            'terpakai_kg'       => 0,
                            'diterima'          => ($jenisObj->kategori !== 'medis' || $tps->izin_limbah_medis),
                        ]
                    );

                    SetoranDetail::create([
                        'setoran_id'        => $setoranBaru->setoran_id,
                        'jenis_id'          => $jenisObj->jenis_id,
                        'berat_estimasi_kg' => $beratKg,
                        'catatan'           => $validated['catatan_item'][$jenisId] ?? null,
                    ]);
                }

                if ($validated['metode'] === 'dijemput') {
                    Penjemputan::create([
                        'setoran_id'    => $setoranBaru->setoran_id,
                        'alamat_id'     => $validated['alamat_id'],
                        'jadwal_jemput' => $waktuJadwal,
                        'status'        => 'menunggu',
                    ]);
                }

                return $setoranBaru;
            });

            return redirect()
                ->route('pengguna.dashboard')
                ->with('sukses', "Manifes setoran {$setoran->kode_setoran} berhasil dibuat dan diteruskan ke {$tps->nama_tps}.");
        } catch (QueryException $e) {
            // Tangkap pesan SIGNAL SQLSTATE '45000' dari trigger MySQL
            $pesanAsli = $e->errorInfo[2] ?? $e->getMessage();
            return back()
                ->withInput()
                ->with('galat', 'Validasi Fasilitas: ' . $pesanAsli);
        }
    }

    /**
     * Batalkan Manifes Setoran (hanya jika masih menunggu_kurir atau menuju_tps)
     */
    public function batalkanSetoran(Setoran $setoran): RedirectResponse
    {
        $user = auth()->user();

        if ((int) $setoran->pengguna_id !== (int) $user->user_id) {
            abort(403);
        }

        if (! in_array($setoran->status, ['menunggu_kurir', 'menuju_tps'], true)) {
            return back()->with('galat', 'Manifes yang sudah diangkut kurir atau sudah tiba di fasilitas tidak dapat dibatalkan.');
        }

        $this->setAppUserId();

        DB::transaction(function () use ($setoran) {
            if ($setoran->penjemputan) {
                $setoran->penjemputan->update(['status' => 'dibatalkan']);
            }
            $setoran->update(['status' => 'dibatalkan']);
        });

        return back()->with('sukses', "Manifes {$setoran->kode_setoran} telah dibatalkan.");
    }

    /**
     * Riwayat Manifes Setoran, Lini Masa Status & Pemberian Ulasan
     */
    public function riwayat(Request $request): View
    {
        $user = auth()->user();
        $filterStatus = $request->query('status', '');

        $query = Setoran::with([
            'tps',
            'detail.jenisSampah',
            'penjemputan.kurirUser',
            'penjemputan.alamat',
            'ulasan',
            'statusLog',
        ])->where('pengguna_id', $user->user_id);

        if ($filterStatus === 'berjalan') {
            $query->whereIn('status', ['menunggu_kurir', 'kurir_ditugaskan', 'diangkut', 'menuju_tps', 'tiba_di_tps']);
        } elseif ($filterStatus !== '') {
            $query->where('status', $filterStatus);
        }

        $daftarRiwayat = $query->latest()->paginate(10)->withQueryString();

        return view('pengguna.riwayat', compact('user', 'daftarRiwayat', 'filterStatus'));
    }

    /**
     * Simpan Ulasan & Rating untuk TPS dan/atau Kurir pada Setoran Selesai
     */
    public function simpanUlasan(Request $request, Setoran $setoran): RedirectResponse
    {
        $user = auth()->user();

        if ((int) $setoran->pengguna_id !== (int) $user->user_id || $setoran->status !== 'selesai') {
            abort(403, 'Hanya manifes yang telah selesai ditimbang yang dapat diberi ulasan.');
        }

        $data = $request->validate([
            'rating_tps'   => ['required', 'integer', 'between:1,5'],
            'rating_kurir' => ['nullable', 'integer', 'between:1,5'],
            'komentar'     => ['nullable', 'string', 'max:255'],
        ]);

        Ulasan::updateOrCreate(
            [
                'setoran_id'  => $setoran->setoran_id,
                'pengguna_id' => $user->user_id,
            ],
            [
                'rating_tps'   => $data['rating_tps'],
                'rating_kurir' => $setoran->metode === 'dijemput' ? ($data['rating_kurir'] ?? 5) : null,
                'komentar'     => $data['komentar'] ?? null,
            ]
        );

        // Perbarui rata-rata rating kurir jika ada
        if ($setoran->metode === 'dijemput' && $setoran->penjemputan?->kurir_id) {
            $kurirId = $setoran->penjemputan->kurir_id;
            $rataBaru = Ulasan::whereHas('setoran.penjemputan', fn ($q) => $q->where('kurir_id', $kurirId))
                ->whereNotNull('rating_kurir')
                ->avg('rating_kurir');

            if ($rataBaru !== null) {
                DB::table('kurir_profil')
                    ->where('user_id', $kurirId)
                    ->update(['rating_rata' => round((float) $rataBaru, 2)]);
            }
        }

        return back()->with('sukses', "Terima kasih! Ulasan untuk manifes {$setoran->kode_setoran} telah disimpan.");
    }

    /**
     * Halaman Dompet & Riwayat Transaksi Poin Insentif B3
     */
    public function poin(): View
    {
        $user = auth()->user();

        $saldoPoin = $user->saldoPoin();
        $totalMasuk = (int) $user->transaksiPoin()->where('tipe', 'masuk')->sum('jumlah');
        $totalKeluar = (int) $user->transaksiPoin()->where('tipe', 'keluar')->sum('jumlah');

        $riwayatPoin = TransaksiPoin::with('setoran.tps')
            ->where('pengguna_id', $user->user_id)
            ->latest('created_at')
            ->paginate(12);

        $katalogPenukaran = [
            [
                'kode'      => 'SAFETY-BOX-5L',
                'nama'      => 'Paket Safety Box Medis 5 Liter + 5 Kantong Kuning Biohazard',
                'deskripsi' => 'Wadah tahan tusuk standar WHO untuk jarum suntik/lancet beserta kantong limbah infeksius.',
                'poin'      => 50,
            ],
            [
                'kode'      => 'APD-SARUNG-MASKER',
                'nama'      => 'Kit APD Pemilahan Rumah/Klinik (Sarung Tangan Nitrile & Masker Medis)',
                'deskripsi' => 'Perlengkapan pelindung diri saat memilah limbah B3, obat kedaluwarsa, dan limbah infeksius.',
                'poin'      => 75,
            ],
            [
                'kode'      => 'KONTAINER-B3-MINI',
                'nama'      => 'Kontainer Kedap Penyimpanan Baterai & Lampu B3',
                'deskripsi' => 'Kotak penyimpanan bersegel aman untuk mengumpulkan baterai litium dan lampu neon bekas.',
                'poin'      => 100,
            ],
            [
                'kode'      => 'VOUCHER-KOMPOS-2KG',
                'nama'      => 'Pupuk Kompos Organik Hasil Olahan Fasilitas (5 Kg)',
                'deskripsi' => 'Kompos matang hasil pengolahan sampah organik terpilah untuk tanaman pekarangan.',
                'poin'      => 40,
            ],
        ];

        return view('pengguna.poin', compact('user', 'saldoPoin', 'totalMasuk', 'totalKeluar', 'riwayatPoin', 'katalogPenukaran'));
    }

    /**
     * Tukar Poin Insentif dengan Perlengkapan K3 / Safety Box / Kompos
     */
    public function tukarPoin(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'nama_item' => ['required', 'string', 'max:150'],
            'jumlah'    => ['required', 'integer', 'min:10', 'max:10000'],
        ]);

        $saldoSaatIni = $user->saldoPoin();
        if ($saldoSaatIni < (int) $data['jumlah']) {
            return back()->with('galat', "Saldo poin Anda ({$saldoSaatIni} pts) belum mencukupi untuk menukar {$data['nama_item']} ({$data['jumlah']} pts).");
        }

        TransaksiPoin::create([
            'pengguna_id' => $user->user_id,
            'setoran_id'  => null,
            'tipe'        => 'keluar',
            'jumlah'      => (int) $data['jumlah'],
            'keterangan'  => 'Penukaran poin: ' . $data['nama_item'],
        ]);

        return back()->with('sukses', "Berhasil menukar {$data['jumlah']} poin dengan \"{$data['nama_item']}\". Ambil perlengkapan Anda di fasilitas berizin terdekat.");
    }

    /**
     * Halaman Kelola Alamat Titik Penjemputan & Profil Penghasil
     */
    public function alamat(): View
    {
        $user = auth()->user();

        $daftarAlamat = $user->alamat()
            ->orderByDesc('is_default')
            ->orderByDesc('alamat_id')
            ->get();

        return view('pengguna.alamat', compact('user', 'daftarAlamat'));
    }

    /**
     * Tambah Alamat Titik Penjemputan Baru
     */
    public function storeAlamat(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'label'          => ['required', 'string', 'max:50'],
            'alamat_lengkap' => ['required', 'string', 'max:255'],
            'latitude'       => ['required', 'numeric', 'between:-90,90'],
            'longitude'      => ['required', 'numeric', 'between:-180,180'],
            'is_default'     => ['nullable', 'boolean'],
        ]);

        $jadikanUtama = $request->boolean('is_default') || $user->alamat()->count() === 0;

        if ($jadikanUtama) {
            $user->alamat()->update(['is_default' => false]);
        }

        AlamatPengguna::create([
            'user_id'        => $user->user_id,
            'label'          => $data['label'],
            'alamat_lengkap' => $data['alamat_lengkap'],
            'latitude'       => $data['latitude'],
            'longitude'      => $data['longitude'],
            'is_default'     => $jadikanUtama,
        ]);

        return back()->with('sukses', "Alamat titik jemput \"{$data['label']}\" berhasil ditambahkan.");
    }

    /**
     * Jadikan Alamat sebagai Alamat Utama (Default)
     */
    public function setUtamaAlamat(AlamatPengguna $alamat): RedirectResponse
    {
        $user = auth()->user();
        if ((int) $alamat->user_id !== (int) $user->user_id) {
            abort(403);
        }

        $user->alamat()->update(['is_default' => false]);
        $alamat->update(['is_default' => true]);

        return back()->with('sukses', "Alamat \"{$alamat->label}\" ditetapkan sebagai titik jemput utama.");
    }

    /**
     * Hapus Alamat Titik Jemput
     */
    public function hapusAlamat(AlamatPengguna $alamat): RedirectResponse
    {
        $user = auth()->user();
        if ((int) $alamat->user_id !== (int) $user->user_id) {
            abort(403);
        }

        if ($alamat->penjemputan()->exists()) {
            return back()->with('galat', 'Alamat ini sudah tercatat pada riwayat manifes penjemputan sehingga tidak dapat dihapus.');
        }

        $alamat->delete();

        return back()->with('sukses', 'Alamat titik jemput berhasil dihapus.');
    }

    /**
     * Perbarui Profil Penghasil / Pengguna
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'nama'          => ['required', 'string', 'max:100'],
            'no_hp'         => ['required', 'string', 'max:20', 'unique:users,no_hp,' . $user->user_id . ',user_id'],
            'password_baru' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $updateData = [
            'nama'  => $data['nama'],
            'no_hp' => $data['no_hp'],
        ];

        if (! empty($data['password_baru'])) {
            $updateData['password_hash'] = Hash::make($data['password_baru']);
        }

        $user->update($updateData);

        return back()->with('sukses', 'Profil akun Anda berhasil diperbarui.');
    }

    /**
     * Tandai Semua Notifikasi Pengguna Sebagai Dibaca
     */
    public function bacaNotifikasi(): RedirectResponse
    {
        Notifikasi::where('penerima_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('sukses', 'Semua pemberitahuan telah ditandai dibaca.');
    }
}

