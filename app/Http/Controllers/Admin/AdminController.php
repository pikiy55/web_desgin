<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSampah;
use App\Models\Konten;
use App\Models\LogAktivitasAdmin;
use App\Models\Notifikasi;
use App\Models\Setoran;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Halaman utama Admin: Antrean Verifikasi, Kapasitas TPS, Setoran Terbaru, & Log Admin.
     */
    public function index(): View
    {
        $akunPending = User::with('kurirProfil')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $tpsBelumVerifikasi = Tps::with('pengelola')
            ->where('is_verified', false)
            ->orderBy('created_at', 'asc')
            ->get();

        $daftarTps = Tps::with('pengelola')
            ->orderByDesc('is_verified')
            ->orderBy('nama_tps')
            ->limit(8)
            ->get();

        $setoranTerbaru = Setoran::with(['pengguna', 'tps', 'penjemputan.kurirUser'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $logAdmin = LogAktivitasAdmin::with('admin')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $ringkasan = [
            'akun_pending' => $akunPending->count(),
            'tps_belum_verifikasi' => $tpsBelumVerifikasi->count(),
            'tps_aktif' => Tps::where('is_verified', true)->where('status', 'buka')->count(),
            'kurir_online' => User::where('role', User::ROLE_KURIR)
                ->where('status', 'aktif')
                ->whereHas('kurirProfil', fn ($q) => $q->whereIn('status_ketersediaan', ['online', 'bertugas']))
                ->count(),
            'setoran_berjalan' => Setoran::whereNotIn('status', ['selesai', 'ditolak_tps', 'dibatalkan'])->count(),
            'total_berat_selesai_kg' => (float) Setoran::where('status', 'selesai')->sum('total_berat_aktual_kg'),
        ];

        return view('admin.dashboard', compact(
            'akunPending',
            'tpsBelumVerifikasi',
            'daftarTps',
            'setoranTerbaru',
            'logAdmin',
            'ringkasan'
        ));
    }

    /**
     * Kelola Akun & Verifikasi Pendaftaran (pengguna, pengelola_tps, kurir, admin).
     */
    public function akun(Request $request): View
    {
        $query = User::with(['kurirProfil', 'tpsDikelola'])->orderByRaw("FIELD(status, 'pending', 'aktif', 'nonaktif', 'ditolak')")->orderByDesc('created_at');

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('cari')) {
            $cari = '%'.$request->string('cari')->trim().'%';
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', $cari)
                    ->orWhere('email', 'like', $cari)
                    ->orWhere('no_hp', 'like', $cari);
            });
        }

        $daftarAkun = $query->paginate(15)->withQueryString();

        $hitungPerStatus = [
            'semua' => User::count(),
            'pending' => User::where('status', 'pending')->count(),
            'aktif' => User::where('status', 'aktif')->count(),
            'nonaktif' => User::where('status', 'nonaktif')->count(),
            'ditolak' => User::where('status', 'ditolak')->count(),
        ];

        return view('admin.akun', compact('daftarAkun', 'hitungPerStatus'));
    }

    /**
     * Ubah status akun (verifikasi / tolak / nonaktifkan / aktifkan kembali).
     */
    public function ubahStatusAkun(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['aktif', 'nonaktif', 'ditolak'])],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        if ($user->user_id === $request->user()->user_id && $data['status'] !== 'aktif') {
            return back()->with('galat', 'Anda tidak dapat menonaktifkan akun admin Anda sendiri.');
        }

        $statusLama = $user->status;
        $user->update(['status' => $data['status']]);

        if ($statusLama === 'pending' && $data['status'] === 'aktif') {
            Notifikasi::create([
                'penerima_id' => $user->user_id,
                'tipe' => 'akun_diverifikasi',
                'judul' => 'Akun Anda telah diverifikasi',
                'pesan' => 'Selamat, akun '.$user->role.' Anda telah disetujui admin dan siap digunakan.',
            ]);
        } elseif ($data['status'] === 'ditolak') {
            $alasan = ! empty($data['catatan']) ? ' Alasan: '.$data['catatan'] : '';
            Notifikasi::create([
                'penerima_id' => $user->user_id,
                'tipe' => 'akun_diverifikasi',
                'judul' => 'Pendaftaran akun belum disetujui',
                'pesan' => 'Pengajuan akun '.$user->role.' Anda ditolak oleh admin.'.$alasan,
            ]);
        }

        $aksi = match ($data['status']) {
            'aktif' => $statusLama === 'pending' ? 'verifikasi_akun' : 'aktifkan_akun',
            'ditolak' => 'tolak_akun',
            'nonaktif' => 'nonaktifkan_akun',
        };

        $this->catatLog($request, $aksi, 'users', $user->user_id, [
            'nama' => $user->nama,
            'role' => $user->role,
            'status_lama' => $statusLama,
            'status_baru' => $data['status'],
            'catatan' => $data['catatan'] ?? null,
        ]);

        return back()->with('sukses', "Status akun {$user->nama} diubah menjadi {$data['status']}.");
    }

    /**
     * Kelola & Verifikasi TPS.
     */
    public function tps(Request $request): View
    {
        $daftarTps = Tps::with(['pengelola', 'jenisSampah'])
            ->orderBy('is_verified', 'asc')
            ->orderBy('nama_tps', 'asc')
            ->get();

        $pengelolaTps = User::where('role', User::ROLE_PENGELOLA_TPS)
            ->where('status', 'aktif')
            ->whereDoesntHave('tps')
            ->orderBy('nama')
            ->get();

        $jenisSampah = JenisSampah::orderBy('jenis_id')->get();

        return view('admin.tps', compact('daftarTps', 'pengelolaTps', 'jenisSampah'));
    }

    public function storeTps(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pengelola_id' => ['required', 'exists:users,user_id', 'unique:tps,pengelola_id'],
            'nama_tps' => ['required', 'string', 'max:120'],
            'alamat' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'kapasitas_total_kg' => ['required', 'numeric', 'min:10'],
            'jam_buka' => ['nullable', 'date_format:H:i'],
            'jam_tutup' => ['nullable', 'date_format:H:i'],
            'no_izin' => ['nullable', 'string', 'max:100'],
            'izin_limbah_medis' => ['nullable', 'boolean'],
        ], [
            'pengelola_id.unique' => 'Akun pengelola ini sudah mengelola 1 TPS (1 akun hanya untuk 1 TPS).',
        ]);

        $tps = Tps::create([
            'pengelola_id' => $data['pengelola_id'],
            'nama_tps' => $data['nama_tps'],
            'alamat' => $data['alamat'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'kapasitas_total_kg' => $data['kapasitas_total_kg'],
            'jam_buka' => $data['jam_buka'] ?? '07:00',
            'jam_tutup' => $data['jam_tutup'] ?? '17:00',
            'no_izin' => $data['no_izin'] ?? null,
            'izin_limbah_medis' => $request->boolean('izin_limbah_medis'),
            'status' => 'buka',
            'is_verified' => true,
        ]);

        // Daftarkan kapasitas awal untuk jenis sampah sesuai izin medisnya
        $jenisList = JenisSampah::all();
        $kapasitasPerJenis = round($tps->kapasitas_total_kg / max(1, $jenisList->count()), 2);
        foreach ($jenisList as $jenis) {
            if ($jenis->kategori === 'medis' && ! $tps->izin_limbah_medis) {
                continue;
            }
            $tps->jenisSampah()->attach($jenis->jenis_id, [
                'kapasitas_maks_kg' => $kapasitasPerJenis,
                'terpakai_kg' => 0,
                'diterima' => true,
            ]);
        }

        $this->catatLog($request, 'tambah_tps', 'tps', $tps->tps_id, [
            'nama_tps' => $tps->nama_tps,
            'kapasitas_total_kg' => $tps->kapasitas_total_kg,
            'izin_limbah_medis' => $tps->izin_limbah_medis,
        ]);

        return back()->with('sukses', "TPS {$tps->nama_tps} berhasil ditambahkan dan diverifikasi.");
    }

    public function verifikasiTps(Request $request, Tps $tps): RedirectResponse
    {
        $data = $request->validate([
            'is_verified' => ['required', 'boolean'],
            'izin_limbah_medis' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['buka', 'tutup', 'penuh'])],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $tps->update([
            'is_verified' => (bool) $data['is_verified'],
            'izin_limbah_medis' => $request->has('izin_limbah_medis') ? $request->boolean('izin_limbah_medis') : $tps->izin_limbah_medis,
            'status' => $data['status'] ?? $tps->status,
        ]);

        $this->catatLog($request, $tps->is_verified ? 'verifikasi_tps' : 'batalkan_verifikasi_tps', 'tps', $tps->tps_id, [
            'nama_tps' => $tps->nama_tps,
            'no_izin' => $tps->no_izin,
            'is_verified' => $tps->is_verified,
            'status' => $tps->status,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return back()->with('sukses', "Data verifikasi {$tps->nama_tps} berhasil diperbarui.");
    }

    /**
     * Master Jenis Sampah & Poin per Kg.
     */
    public function jenisSampah(): View
    {
        $daftarJenis = JenisSampah::withCount('setoranDetail')
            ->orderByRaw("FIELD(kategori, 'organik', 'anorganik', 'elektronik', 'b3', 'medis')")
            ->orderBy('nama')
            ->get();

        return view('admin.jenis-sampah', compact('daftarJenis'));
    }

    public function storeJenisSampah(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:80', 'unique:jenis_sampah,nama'],
            'kategori' => ['required', Rule::in(['organik', 'anorganik', 'b3', 'elektronik', 'medis'])],
            'deskripsi' => ['nullable', 'string', 'max:255'],
            'perlu_penanganan_khusus' => ['nullable', 'boolean'],
            'poin_per_kg' => ['required', 'integer', 'min:0', 'max:10000'],
        ]);

        $jenis = JenisSampah::create([
            'nama' => $data['nama'],
            'kategori' => $data['kategori'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'perlu_penanganan_khusus' => $request->boolean('perlu_penanganan_khusus'),
            'poin_per_kg' => $data['poin_per_kg'],
        ]);

        $this->catatLog($request, 'tambah_jenis_sampah', 'jenis_sampah', $jenis->jenis_id, [
            'nama' => $jenis->nama,
            'kategori' => $jenis->kategori,
            'poin_per_kg' => $jenis->poin_per_kg,
        ]);

        return back()->with('sukses', "Jenis sampah '{$jenis->nama}' berhasil ditambahkan.");
    }

    public function updateJenisSampah(Request $request, JenisSampah $jenisSampah): RedirectResponse
    {
        $data = $request->validate([
            'poin_per_kg' => ['required', 'integer', 'min:0', 'max:10000'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
            'perlu_penanganan_khusus' => ['nullable', 'boolean'],
        ]);

        $poinLama = $jenisSampah->poin_per_kg;

        $jenisSampah->update([
            'poin_per_kg' => $data['poin_per_kg'],
            'deskripsi' => $data['deskripsi'] ?? $jenisSampah->deskripsi,
            'perlu_penanganan_khusus' => $request->boolean('perlu_penanganan_khusus'),
        ]);

        $this->catatLog($request, 'ubah_jenis_sampah', 'jenis_sampah', $jenisSampah->jenis_id, [
            'nama' => $jenisSampah->nama,
            'poin_lama' => $poinLama,
            'poin_baru' => $jenisSampah->poin_per_kg,
        ]);

        return back()->with('sukses', "Poin & ketentuan '{$jenisSampah->nama}' diperbarui.");
    }

    /**
     * Konten Edukasi, Artikel, Pengumuman, & Banner.
     */
    public function konten(): View
    {
        $daftarKonten = Konten::with('admin')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.konten', compact('daftarKonten'));
    }

    /**
     * Validasi isi buku: admin mengirim `halaman[]` (satu textarea per halaman).
     * Hasilnya digabung ke kolom `isi` dengan penanda Konten::PEMISAH_HALAMAN.
     */
    private function isiDariHalaman(Request $request): string
    {
        if (! $request->has('halaman') && $request->filled('isi')) {
            return trim((string) $request->input('isi'));
        }

        $request->validate([
            'halaman' => ['required', 'array', 'min:1', 'max:60'],
            'halaman.*' => ['nullable', 'string', 'max:20000'],
        ], [
            'halaman.required' => 'Isi buku minimal satu halaman.',
            'halaman.max' => 'Buku maksimal 60 halaman.',
            'halaman.*.max' => 'Teks satu halaman terlalu panjang (maks. 20.000 karakter).',
        ]);

        $isi = Konten::gabungHalaman($request->input('halaman', []));

        if ($isi === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'halaman' => 'Isi minimal satu halaman dengan teks.',
            ]);
        }

        return $isi;
    }

    public function storeKonten(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'jenis' => ['required', Rule::in(['artikel', 'edukasi', 'pengumuman', 'banner'])],
            'ringkasan' => ['nullable', 'string', 'max:300'],
            'status' => ['required', Rule::in(['draft', 'terbit'])],
        ]);
        $data['isi'] = $this->isiDariHalaman($request);
        $isiPolos = str_replace(Konten::PEMISAH_HALAMAN, ' ', $data['isi']);

        $slugDasar = Str::slug($data['judul']);
        $slug = $slugDasar;
        $i = 2;
        while (Konten::where('slug', $slug)->exists()) {
            $slug = $slugDasar.'-'.$i++;
        }

        $terbit = $data['status'] === 'terbit';

        $konten = Konten::create([
            'admin_id' => $request->user()->user_id,
            'judul' => $data['judul'],
            'slug' => $slug,
            'jenis' => $data['jenis'],
            'ringkasan' => $data['ringkasan'] ?? Str::limit(strip_tags($isiPolos), 160),
            'isi' => $data['isi'],
            'status' => $data['status'],
            'published_at' => $terbit ? now() : null,
        ]);

        $this->catatLog($request, $terbit ? 'terbitkan_konten' : 'simpan_draft_konten', 'konten', $konten->konten_id, [
            'judul' => $konten->judul,
            'jenis' => $konten->jenis,
            'status' => $konten->status,
        ]);

        return back()->with('sukses', "Konten '{$konten->judul}' berhasil disimpan ({$konten->status}).");
    }

    public function statusKonten(Request $request, Konten $konten): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['draft', 'terbit', 'arsip'])],
        ]);

        $konten->update([
            'status' => $data['status'],
            'published_at' => $data['status'] === 'terbit' ? ($konten->published_at ?? now()) : $konten->published_at,
        ]);

        $this->catatLog($request, 'ubah_status_konten', 'konten', $konten->konten_id, [
            'judul' => $konten->judul,
            'status' => $konten->status,
        ]);

        return back()->with('sukses', "Status konten '{$konten->judul}' diubah menjadi {$konten->status}.");
    }

    public function updateKonten(Request $request, Konten $konten): RedirectResponse
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'jenis' => ['required', Rule::in(['artikel', 'edukasi', 'pengumuman', 'banner'])],
            'ringkasan' => ['nullable', 'string', 'max:300'],
            'status' => ['required', Rule::in(['draft', 'terbit', 'arsip'])],
        ]);
        $data['isi'] = $this->isiDariHalaman($request);
        $isiPolos = str_replace(Konten::PEMISAH_HALAMAN, ' ', $data['isi']);

        $slug = $konten->slug;
        if ($data['judul'] !== $konten->judul) {
            $slugDasar = Str::slug($data['judul']);
            $slug = $slugDasar;
            $i = 2;
            while (Konten::where('slug', $slug)->where('konten_id', '!=', $konten->konten_id)->exists()) {
                $slug = $slugDasar.'-'.$i++;
            }
        }

        $terbit = $data['status'] === 'terbit';

        $konten->update([
            'judul' => $data['judul'],
            'slug' => $slug,
            'jenis' => $data['jenis'],
            'ringkasan' => $data['ringkasan'] ?: Str::limit(strip_tags($isiPolos), 160),
            'isi' => $data['isi'],
            'status' => $data['status'],
            'published_at' => $terbit ? ($konten->published_at ?? now()) : $konten->published_at,
        ]);

        $this->catatLog($request, 'ubah_konten', 'konten', $konten->konten_id, [
            'judul' => $konten->judul,
            'jenis' => $konten->jenis,
            'status' => $konten->status,
        ]);

        return back()->with('sukses', "Konten '{$konten->judul}' berhasil diperbarui.");
    }

    public function destroyKonten(Request $request, Konten $konten): RedirectResponse
    {
        $judul = $konten->judul;
        $idKonten = $konten->konten_id;

        $konten->delete();

        $this->catatLog($request, 'hapus_konten', 'konten', $idKonten, [
            'judul' => $judul,
        ]);

        return back()->with('sukses', "Konten '{$judul}' berhasil dihapus.");
    }

    private function catatLog(Request $request, string $aksi, string $tabel, int $idTarget, array $detail = []): void
    {
        LogAktivitasAdmin::create([
            'admin_id' => $request->user()->user_id,
            'aksi' => $aksi,
            'tabel_target' => $tabel,
            'id_target' => $idTarget,
            'detail' => $detail,
            'ip_address' => $request->ip(),
        ]);
    }
}

