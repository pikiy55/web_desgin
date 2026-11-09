<?php

namespace App\Http\Controllers\Kurir;

use App\Http\Controllers\Controller;
use App\Models\KurirProfil;
use App\Models\Notifikasi;
use App\Models\Penjemputan;
use App\Models\Setoran;
use App\Models\Ulasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class KurirController extends Controller
{
    /**
     * Dapatkan profil kurir yang sedang login.
     * Buat otomatis profil default jika belum ada di database.
     */
    private function kurirProfil(Request $request): KurirProfil
    {
        $user = $request->user();

        return KurirProfil::firstOrCreate(
            ['user_id' => $user->user_id],
            [
                'jenis_kendaraan' => 'Sepeda Motor Roda Dua / Tiga',
                'plat_nomor' => 'Belum Diatur',
                'kapasitas_angkut_kg' => 150,
                'status_ketersediaan' => 'online',
                'rating_rata' => 0,
            ]
        );
    }

    /**
     * Atur @app_user_id untuk pencatatan di trigger database MySQL.
     */
    private function setAppUserId(Request $request): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('SET @app_user_id = ?', [$request->user()->user_id]);
        }
    }

    /**
     * Halaman Utama Dasbor Kurir (/kurir)
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $profil = $this->kurirProfil($request);

        // Perbarui rating rata-rata dari ulasan warga jika ada
        $ratingTerbaru = Ulasan::whereHas('setoran.penjemputan', function ($q) use ($user) {
            $q->where('kurir_id', $user->user_id);
        })->whereNotNull('rating_kurir')->avg('rating_kurir');

        if ($ratingTerbaru !== null && (float) $ratingTerbaru != (float) $profil->rating_rata) {
            $profil->update(['rating_rata' => round($ratingTerbaru, 2)]);
        }

        // 1. Tugas yang sedang aktif dikerjakan kurir ini (status: diterima atau diangkut)
        $tugasAktif = Penjemputan::with([
            'setoran.pengguna',
            'setoran.tps',
            'setoran.detail.jenisSampah',
            'alamat',
        ])
            ->where('kurir_id', $user->user_id)
            ->whereIn('status', ['diterima', 'diangkut'])
            ->first();

        // 2. Daftar antrean tugas baru yang menunggu dijemput (belum ada kurir)
        $antreanTugas = Penjemputan::with([
            'setoran.pengguna',
            'setoran.tps',
            'setoran.detail.jenisSampah',
            'alamat',
        ])
            ->whereNull('kurir_id')
            ->where('status', 'menunggu')
            ->whereHas('setoran', function ($q) {
                $q->where('status', 'menunggu_kurir');
            })
            ->orderBy('jadwal_jemput', 'asc')
            ->orderBy('penjemputan_id', 'asc')
            ->get();

        // 3. Ringkasan statistik kurir
        $riwayatHariIni = Penjemputan::where('kurir_id', $user->user_id)
            ->whereIn('status', ['tiba_di_tps', 'selesai'])
            ->whereDate('tiba_at', today());

        $beratHariIniKg = (float) Setoran::whereHas('penjemputan', function ($q) use ($user) {
            $q->where('kurir_id', $user->user_id)
                ->whereIn('status', ['tiba_di_tps', 'selesai'])
                ->whereDate('tiba_at', today());
        })->sum(DB::raw('COALESCE(total_berat_aktual_kg, 0)'));

        // Jika belum ditimbang, gunakan estimasi berat
        if ($beratHariIniKg <= 0) {
            $beratHariIniKg = (float) DB::table('setoran_detail')
                ->join('penjemputan', 'penjemputan.setoran_id', '=', 'setoran_detail.setoran_id')
                ->where('penjemputan.kurir_id', $user->user_id)
                ->whereIn('penjemputan.status', ['tiba_di_tps', 'selesai'])
                ->whereDate('penjemputan.tiba_at', today())
                ->sum('setoran_detail.berat_estimasi_kg');
        }

        $totalSelesaiSemua = Penjemputan::where('kurir_id', $user->user_id)
            ->whereIn('status', ['tiba_di_tps', 'selesai'])
            ->count();

        // Beban muatan saat ini jika sedang mengangkut
        $bebanMuatanAktifKg = 0;
        if ($tugasAktif && $tugasAktif->status === 'diangkut') {
            $bebanMuatanAktifKg = (float) $tugasAktif->setoran->detail->sum('berat_estimasi_kg');
        }

        $ringkasan = [
            'tugas_aktif' => $tugasAktif ? 1 : 0,
            'antrean_tersedia' => $antreanTugas->count(),
            'selesai_hari_ini' => (clone $riwayatHariIni)->count(),
            'berat_hari_ini_kg' => $beratHariIniKg,
            'total_selesai' => $totalSelesaiSemua,
            'beban_aktif_kg' => $bebanMuatanAktifKg,
            'rating_rata' => (float) $profil->rating_rata,
        ];

        // 4. Notifikasi belum dibaca untuk kurir
        $notifikasiBelumBaca = Notifikasi::where('penerima_id', $user->user_id)
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // 5. Riwayat pengantaran terakhir (5 item)
        $riwayatSingkat = Penjemputan::with([
            'setoran.tps',
            'setoran.pengguna',
            'setoran.ulasan',
        ])
            ->where('kurir_id', $user->user_id)
            ->whereIn('status', ['tiba_di_tps', 'selesai'])
            ->orderByDesc('tiba_at')
            ->limit(5)
            ->get();

        return view('kurir.dashboard', compact(
            'profil',
            'tugasAktif',
            'antreanTugas',
            'ringkasan',
            'notifikasiBelumBaca',
            'riwayatSingkat'
        ));
    }

    /**
     * Beralih status ketersediaan kurir (Online / Offline).
     */
    public function ubahStatusKetersediaan(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profil = $this->kurirProfil($request);

        $request->validate([
            'status' => ['required', 'in:online,offline'],
        ]);

        $statusBaru = $request->input('status');

        // Jika kurir sedang mengangkut atau menerima tugas aktif, tidak boleh langsung offline
        $adaTugasAktif = Penjemputan::where('kurir_id', $user->user_id)
            ->whereIn('status', ['diterima', 'diangkut'])
            ->exists();

        if ($adaTugasAktif && $statusBaru === 'offline') {
            return back()->with('galat', 'Anda tidak dapat beralih ke mode Offline karena masih memiliki tugas penjemputan aktif.');
        }

        $profil->update([
            'status_ketersediaan' => $statusBaru,
            'lokasi_update_at' => now(),
        ]);

        $pesan = $statusBaru === 'online'
            ? 'Status Anda kini AKTIF (ONLINE). Anda siap menerima pesanan penjemputan sampah.'
            : 'Status Anda kini NONAKTIF (OFFLINE). Anda tidak akan menerima notifikasi tugas baru.';

        return back()->with('sukses', $pesan);
    }

    /**
     * Kurir mengambil tugas penjemputan dari antrean warga (Race-condition safe).
     */
    public function terimaTugas(Request $request, Penjemputan $penjemputan): RedirectResponse
    {
        $user = $request->user();
        $profil = $this->kurirProfil($request);

        // Cek status verifikasi akun kurir
        if ($user->status !== 'aktif') {
            return back()->with('galat', 'Akun Anda masih dalam tahap verifikasi oleh Administrator. Anda belum dapat mengambil tugas.');
        }

        // Cek apakah kurir sudah memiliki tugas aktif
        $sedangBertugas = Penjemputan::where('kurir_id', $user->user_id)
            ->whereIn('status', ['diterima', 'diangkut'])
            ->exists();

        if ($sedangBertugas) {
            return back()->with('galat', 'Anda masih memiliki tugas penjemputan yang sedang berjalan. Selesaikan tugas tersebut terlebih dahulu.');
        }

        $this->setAppUserId($request);

        // Eksekusi atomic update bersyarat agar tidak rebutan antar kurir
        $diambil = Penjemputan::where('penjemputan_id', $penjemputan->penjemputan_id)
            ->whereNull('kurir_id')
            ->where('status', 'menunggu')
            ->update([
                'kurir_id' => $user->user_id,
                'status' => 'diterima',
                'diterima_at' => now(),
            ]);

        if ($diambil === 0) {
            return back()->with('galat', 'Maaf, tugas penjemputan ini telah diambil oleh kurir lain atau sudah dibatalkan.');
        }

        // Sinkronisasi status setoran & profil kurir (fallback jika trigger tidak aktif)
        $penjemputan->refresh();
        $penjemputan->setoran->update(['status' => 'kurir_ditugaskan']);
        $profil->update(['status_ketersediaan' => 'bertugas']);

        return back()->with('sukses', "Tugas penjemputan {$penjemputan->setoran->kode_setoran} berhasil Anda ambil! Segera meluncur ke lokasi warga.");
    }

    /**
     * Konfirmasi sampah telah dimuat/diangkut oleh kurir dari rumah warga.
     */
    public function konfirmasiAngkut(Request $request, Penjemputan $penjemputan): RedirectResponse
    {
        $user = $request->user();

        abort_unless($penjemputan->kurir_id === $user->user_id, 403);

        if ($penjemputan->status !== 'diterima') {
            return back()->with('galat', 'Status tugas tidak valid untuk konfirmasi pengangkutan.');
        }

        $request->validate([
            'foto_bukti_ambil' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'catatan_kurir' => ['nullable', 'string', 'max:255'],
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_bukti_ambil')) {
            $folder = public_path('uploads/bukti');
            File::ensureDirectoryExists($folder);

            $filename = 'jemput_' . $penjemputan->penjemputan_id . '_' . time() . '.' . $request->file('foto_bukti_ambil')->getClientOriginalExtension();
            $request->file('foto_bukti_ambil')->move($folder, $filename);
            $fotoPath = 'uploads/bukti/' . $filename;
        }

        $this->setAppUserId($request);

        $penjemputan->update([
            'status' => 'diangkut',
            'diangkut_at' => now(),
            'catatan_kurir' => $request->input('catatan_kurir', $penjemputan->catatan_kurir),
            'foto_bukti_ambil' => $fotoPath ?? $penjemputan->foto_bukti_ambil,
        ]);

        // Sinkronisasi status setoran (fallback jika DB bukan MySQL)
        $penjemputan->setoran->update(['status' => 'diangkut']);

        return back()->with('sukses', "Sampah untuk setoran {$penjemputan->setoran->kode_setoran} berhasil diangkut. Silakan bawa ke TPS tujuan.");
    }

    /**
     * Konfirmasi kurir telah tiba di TPS & menyerahkan sampah ke petugas timbang TPS.
     */
    public function konfirmasiTibaDiTps(Request $request, Penjemputan $penjemputan): RedirectResponse
    {
        $user = $request->user();
        $profil = $this->kurirProfil($request);

        abort_unless($penjemputan->kurir_id === $user->user_id, 403);

        if ($penjemputan->status !== 'diangkut') {
            return back()->with('galat', 'Status tugas belum dalam tahap pengangkutan.');
        }

        $request->validate([
            'foto_bukti_serah' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'catatan_kurir' => ['nullable', 'string', 'max:255'],
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_bukti_serah')) {
            $folder = public_path('uploads/bukti');
            File::ensureDirectoryExists($folder);

            $filename = 'serah_' . $penjemputan->penjemputan_id . '_' . time() . '.' . $request->file('foto_bukti_serah')->getClientOriginalExtension();
            $request->file('foto_bukti_serah')->move($folder, $filename);
            $fotoPath = 'uploads/bukti/' . $filename;
        }

        $this->setAppUserId($request);

        $penjemputan->update([
            'status' => 'tiba_di_tps',
            'tiba_at' => now(),
            'foto_bukti_serah' => $fotoPath ?? $penjemputan->foto_bukti_serah,
            'catatan_kurir' => $request->filled('catatan_kurir') ? $request->input('catatan_kurir') : $penjemputan->catatan_kurir,
        ]);

        // Sinkronisasi status setoran & ketersediaan kurir
        $penjemputan->setoran->update(['status' => 'tiba_di_tps']);
        $profil->update(['status_ketersediaan' => 'online']);

        return back()->with('sukses', "Setoran {$penjemputan->setoran->kode_setoran} telah diserahkan di Pos TPS. Tugas penjemputan selesai, siap ditimbang!");
    }

    /**
     * Kurir membatalkan tugas yang baru diambil sebelum proses pengangkutan dilakukan.
     */
    public function batalkanTugas(Request $request, Penjemputan $penjemputan): RedirectResponse
    {
        $user = $request->user();
        $profil = $this->kurirProfil($request);

        abort_unless($penjemputan->kurir_id === $user->user_id, 403);

        if ($penjemputan->status !== 'diterima') {
            return back()->with('galat', 'Tugas yang sudah diangkut tidak dapat dibatalkan.');
        }

        $this->setAppUserId($request);

        // Kembalikan status penjemputan ke antrean publik
        $penjemputan->update([
            'kurir_id' => null,
            'status' => 'menunggu',
            'diterima_at' => null,
            'catatan_kurir' => 'Tugas dilepas kembali ke antrean oleh kurir: ' . ($request->input('alasan') ?? 'Kendala perjalanan'),
        ]);

        $penjemputan->setoran->update(['status' => 'menunggu_kurir']);
        $profil->update(['status_ketersediaan' => 'online']);

        return back()->with('sukses', "Tugas penjemputan {$penjemputan->setoran->kode_setoran} telah dilepas kembali ke antrean umum.");
    }

    /**
     * Riwayat Seluruh Pengantaran Kurir (/kurir/riwayat)
     */
    public function riwayat(Request $request): View
    {
        $user = $request->user();
        $profil = $this->kurirProfil($request);

        $daftarRiwayat = Penjemputan::with([
            'setoran.pengguna',
            'setoran.tps',
            'setoran.detail.jenisSampah',
            'setoran.ulasan',
            'alamat',
        ])
            ->where('kurir_id', $user->user_id)
            ->whereIn('status', ['tiba_di_tps', 'selesai'])
            ->orderByDesc('tiba_at')
            ->paginate(10);

        $totalKgDiangkut = (float) DB::table('setoran_detail')
            ->join('setoran', 'setoran.setoran_id', '=', 'setoran_detail.setoran_id')
            ->join('penjemputan', 'penjemputan.setoran_id', '=', 'setoran.setoran_id')
            ->where('penjemputan.kurir_id', $user->user_id)
            ->whereIn('penjemputan.status', ['tiba_di_tps', 'selesai'])
            ->sum(DB::raw('COALESCE(setoran_detail.berat_aktual_kg, setoran_detail.berat_estimasi_kg)'));

        $totalSelesai = Penjemputan::where('kurir_id', $user->user_id)
            ->whereIn('status', ['tiba_di_tps', 'selesai'])
            ->count();

        $ulasanWarga = Ulasan::with('pengguna', 'setoran')
            ->whereHas('setoran.penjemputan', function ($q) use ($user) {
                $q->where('kurir_id', $user->user_id);
            })
            ->whereNotNull('rating_kurir')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('kurir.riwayat', compact(
            'profil',
            'daftarRiwayat',
            'totalKgDiangkut',
            'totalSelesai',
            'ulasanWarga'
        ));
    }

    /**
     * Pengaturan Profil & Data Kendaraan Kurir (/kurir/profil)
     */
    public function profil(Request $request): View
    {
        $user = $request->user();
        $profil = $this->kurirProfil($request);

        return view('kurir.profil', compact('user', 'profil'));
    }

    /**
     * Simpan Perubahan Profil & Kendaraan Kurir
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profil = $this->kurirProfil($request);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'max:20'],
            'jenis_kendaraan' => ['required', 'string', 'max:30'],
            'plat_nomor' => ['required', 'string', 'max:15'],
            'no_sim' => ['nullable', 'string', 'max:30'],
            'kapasitas_angkut_kg' => ['required', 'numeric', 'min:10', 'max:5000'],
        ]);

        $user->update([
            'nama' => $validated['nama'],
            'no_hp' => $validated['no_hp'],
        ]);

        $profil->update([
            'jenis_kendaraan' => $validated['jenis_kendaraan'],
            'plat_nomor' => strtoupper(trim($validated['plat_nomor'])),
            'no_sim' => $validated['no_sim'] ?? $profil->no_sim,
            'kapasitas_angkut_kg' => $validated['kapasitas_angkut_kg'],
            'lokasi_update_at' => now(),
        ]);

        return back()->with('sukses', 'Data profil kurir dan kendaraan operasional berhasil diperbarui.');
    }

    /**
     * Tandai notifikasi kurir sebagai sudah dibaca
     */
    public function bacaNotifikasi(Request $request): RedirectResponse
    {
        Notifikasi::where('penerima_id', $request->user()->user_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('sukses', 'Semua pemberitahuan telah ditandai dibaca.');
    }
}

