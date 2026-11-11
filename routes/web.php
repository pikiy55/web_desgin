<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Kurir\KurirController;
use App\Http\Controllers\Pengelola\PengelolaTpsController;
use App\Http\Controllers\Pengguna\PenggunaController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\KurirMiddleware;
use App\Http\Middleware\PengelolaTpsMiddleware;
use App\Http\Middleware\PenggunaMiddleware;
use App\Models\JenisSampah;
use App\Models\Konten;
use App\Models\Setoran;
use App\Models\Tps;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    try {
        $jenisSampah = JenisSampah::query()
            ->orderByRaw("FIELD(kategori, 'medis', 'b3', 'elektronik', 'anorganik', 'organik')")
            ->orderByDesc('perlu_penanganan_khusus')
            ->orderByDesc('poin_per_kg')
            ->get();

        $daftarTps = Tps::query()
            ->with('pengelola:user_id,nama')
            ->where('is_verified', true)
            ->orderByDesc('izin_limbah_medis')
            ->orderBy('nama_tps')
            ->get();

        $kontenEdukasi = Konten::query()
            ->terbit()
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        $ringkasan = [
            'total_berat_selesai_kg' => (float) Setoran::where('status', 'selesai')->sum('total_berat_aktual_kg'),
            'total_poin_terbit'      => (int) Setoran::where('status', 'selesai')->sum('total_poin'),
            'total_tps_medis'        => $daftarTps->where('izin_limbah_medis', true)->count(),
            'total_tps_aktif'        => $daftarTps->where('status', 'buka')->count(),
            'total_jenis_khusus'     => $jenisSampah->whereIn('kategori', ['medis', 'b3', 'elektronik'])->count(),
        ];
    } catch (\Throwable $e) {
        $jenisSampah = collect([
            (object) ['jenis_id' => 10, 'nama' => 'Medis - infeksius',   'kategori' => 'medis',      'deskripsi' => 'Perban, sarung tangan, masker bekas pakai',      'perlu_penanganan_khusus' => true,  'poin_per_kg' => 0],
            (object) ['jenis_id' => 11, 'nama' => 'Medis - benda tajam', 'kategori' => 'medis',      'deskripsi' => 'Jarum suntik, pisau bedah, ampul pecah',         'perlu_penanganan_khusus' => true,  'poin_per_kg' => 0],
            (object) ['jenis_id' => 12, 'nama' => 'Medis - farmasi',     'kategori' => 'medis',      'deskripsi' => 'Obat kedaluwarsa dan sisa obat',                 'perlu_penanganan_khusus' => true,  'poin_per_kg' => 0],
            (object) ['jenis_id' => 13, 'nama' => 'Medis - patologis',   'kategori' => 'medis',      'deskripsi' => 'Jaringan dan cairan tubuh dari fasilitas medis', 'perlu_penanganan_khusus' => true,  'poin_per_kg' => 0],
            (object) ['jenis_id' => 7,  'nama' => 'Baterai bekas',       'kategori' => 'b3',         'deskripsi' => 'Baterai dan aki kecil',                          'perlu_penanganan_khusus' => true,  'poin_per_kg' => 10],
            (object) ['jenis_id' => 8,  'nama' => 'Lampu bekas',         'kategori' => 'b3',         'deskripsi' => 'Lampu neon dan LED',                             'perlu_penanganan_khusus' => true,  'poin_per_kg' => 10],
            (object) ['jenis_id' => 9,  'nama' => 'Elektronik bekas',    'kategori' => 'elektronik', 'deskripsi' => 'Ponsel, kabel, peralatan elektronik',            'perlu_penanganan_khusus' => true,  'poin_per_kg' => 25],
            (object) ['jenis_id' => 5,  'nama' => 'Logam',               'kategori' => 'anorganik',  'deskripsi' => 'Kaleng, besi, aluminium',                        'perlu_penanganan_khusus' => false, 'poin_per_kg' => 30],
            (object) ['jenis_id' => 3,  'nama' => 'Plastik',             'kategori' => 'anorganik',  'deskripsi' => 'Botol, kemasan, kantong plastik',                'perlu_penanganan_khusus' => false, 'poin_per_kg' => 20],
            (object) ['jenis_id' => 4,  'nama' => 'Kertas dan kardus',   'kategori' => 'anorganik',  'deskripsi' => 'Kertas bekas, kardus, koran',                    'perlu_penanganan_khusus' => false, 'poin_per_kg' => 15],
            (object) ['jenis_id' => 6,  'nama' => 'Kaca',                'kategori' => 'anorganik',  'deskripsi' => 'Botol dan pecahan kaca',                         'perlu_penanganan_khusus' => true,  'poin_per_kg' => 10],
            (object) ['jenis_id' => 1,  'nama' => 'Sisa makanan',        'kategori' => 'organik',    'deskripsi' => 'Sisa dapur dan makanan',                         'perlu_penanganan_khusus' => false, 'poin_per_kg' => 5],
            (object) ['jenis_id' => 2,  'nama' => 'Daun dan ranting',    'kategori' => 'organik',    'deskripsi' => 'Sampah kebun',                                   'perlu_penanganan_khusus' => false, 'poin_per_kg' => 5],
        ]);

        $daftarTps = collect([
            (object) [
                'tps_id' => 1,
                'nama_tps' => 'TPS Kenanga Bandar Lor',
                'alamat' => 'Jl. KH. Wachid Hasyim No. 42, Bandar Lor, Kec. Mojoroto, Kota Kediri',
                'kapasitas_total_kg' => 1850.00,
                'kapasitas_terpakai_kg' => 125.80,
                'jam_buka' => '07:00:00',
                'jam_tutup' => '17:00:00',
                'no_izin' => 'DLHKP/TPS/2026/001',
                'izin_limbah_medis' => true,
                'status' => 'buka',
                'is_verified' => true,
            ],
        ]);

        $kontenEdukasi = collect([
            (object) [
                'judul' => 'Prosedur Pengemasan Benda Tajam Medis (Safety Box) & Limbah Infeksius',
                'slug' => 'prosedur-aman-limbah-medis-b3',
                'jenis' => 'panduan',
                'ringkasan' => 'Jarum suntik, lancet, dan ampul wajib dimasukkan ke wadah tahan tusuk (Safety Box) dan hanya diserahkan ke fasilitas berizin limbah medis.',
                'published_at' => now()->subDays(2),
            ],
            (object) [
                'judul' => 'Penanganan Baterai Bekas, Lampu Merkuri, dan Limbah B3 Rumah Tangga',
                'slug' => 'penanganan-baterai-lampu-limbah-b3',
                'jenis' => 'edukasi',
                'ringkasan' => 'Jangan mencampur baterai litium atau lampu neon pecah ke tempat sampah biasa. Pisahkan dalam kemasan kering tertutup untuk mendapatkan poin insentif B3.',
                'published_at' => now()->subDays(5),
            ],
        ]);

        $ringkasan = [
            'total_berat_selesai_kg' => 6.30,
            'total_poin_terbit'      => 118,
            'total_tps_medis'        => 1,
            'total_tps_aktif'        => 1,
            'total_jenis_khusus'     => 7,
        ];
    }

    return view('welcome', compact('jenisSampah', 'daftarTps', 'kontenEdukasi', 'ringkasan'));
})->name('welcome');

Route::get('/masuk', [LoginController::class, 'create'])->name('login');
Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:6,1');

Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
Route::post('/daftar', [RegisterController::class, 'store'])->middleware('throttle:6,1');

Route::view('/misi-pilah', 'game')->name('game.pilah');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user?->isPengelolaTps()) {
            return redirect()->route('pengelola.dashboard');
        }
        if ($user?->isKurir()) {
            return redirect()->route('kurir.dashboard');
        }

        return redirect()->route('pengguna.dashboard');
    })->name('dashboard');

    Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout');
});

// Panel Penghasil / Pengguna Limbah Medis & B3 (role: pengguna)
Route::prefix('pengguna')
    ->name('pengguna.')
    ->middleware(['auth', PenggunaMiddleware::class])
    ->group(function () {
        Route::get('/', [PenggunaController::class, 'index'])->name('dashboard');

        // Buat & Batalkan Manifes Setoran Baru
        Route::get('/setoran/baru', [PenggunaController::class, 'buatSetoran'])->name('setoran.create');
        Route::post('/setoran', [PenggunaController::class, 'storeSetoran'])->name('setoran.store');
        Route::patch('/setoran/{setoran}/batal', [PenggunaController::class, 'batalkanSetoran'])->name('setoran.batal');

        // Riwayat Manifes & Ulasan Layanan
        Route::get('/riwayat', [PenggunaController::class, 'riwayat'])->name('riwayat');
        Route::post('/setoran/{setoran}/ulasan', [PenggunaController::class, 'simpanUlasan'])->name('setoran.ulasan');

        // Dompet & Penukaran Poin Insentif B3
        Route::get('/poin', [PenggunaController::class, 'poin'])->name('poin');
        Route::post('/poin/tukar', [PenggunaController::class, 'tukarPoin'])->name('poin.tukar');

        // Alamat Titik Jemput & Profil Penghasil
        Route::get('/alamat', [PenggunaController::class, 'alamat'])->name('alamat');
        Route::post('/alamat', [PenggunaController::class, 'storeAlamat'])->name('alamat.store');
        Route::patch('/alamat/{alamat}/utama', [PenggunaController::class, 'setUtamaAlamat'])->name('alamat.utama');
        Route::delete('/alamat/{alamat}', [PenggunaController::class, 'hapusAlamat'])->name('alamat.destroy');
        Route::patch('/profil', [PenggunaController::class, 'updateProfil'])->name('profil.update');

        // Notifikasi Pengguna
        Route::post('/notifikasi/baca', [PenggunaController::class, 'bacaNotifikasi'])->name('notifikasi.baca');
    });

// Panel Admin (role: admin)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', AdminMiddleware::class])
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');

        // Akun & Verifikasi
        Route::get('/akun', [AdminController::class, 'akun'])->name('akun');
        Route::patch('/akun/{user}/status', [AdminController::class, 'ubahStatusAkun'])->name('akun.status');

        // TPS & Kapasitas
        Route::get('/tps', [AdminController::class, 'tps'])->name('tps');
        Route::post('/tps', [AdminController::class, 'storeTps'])->name('tps.store');
        Route::patch('/tps/{tps}/verifikasi', [AdminController::class, 'verifikasiTps'])->name('tps.verifikasi');

        // Master Jenis Sampah & Poin
        Route::get('/jenis-sampah', [AdminController::class, 'jenisSampah'])->name('jenis-sampah');
        Route::post('/jenis-sampah', [AdminController::class, 'storeJenisSampah'])->name('jenis-sampah.store');
        Route::patch('/jenis-sampah/{jenisSampah}', [AdminController::class, 'updateJenisSampah'])->name('jenis-sampah.update');

        // Konten Edukasi & Pengumuman (CRUD Lengkap)
        Route::get('/konten', [AdminController::class, 'konten'])->name('konten');
        Route::post('/konten', [AdminController::class, 'storeKonten'])->name('konten.store');
        Route::put('/konten/{konten:konten_id}', [AdminController::class, 'updateKonten'])->name('konten.update');
        Route::patch('/konten/{konten:konten_id}/status', [AdminController::class, 'statusKonten'])->name('konten.status');
        Route::delete('/konten/{konten:konten_id}', [AdminController::class, 'destroyKonten'])->name('konten.destroy');
    });

// Panel Pengelola TPS (role: pengelola_tps)
Route::prefix('pengelola')
    ->name('pengelola.')
    ->middleware(['auth', PengelolaTpsMiddleware::class])
    ->group(function () {
        Route::get('/', [PengelolaTpsController::class, 'index'])->name('dashboard');

        // Penimbangan & Konfirmasi Setoran Masuk
        Route::patch('/setoran/{setoran}/tiba', [PengelolaTpsController::class, 'konfirmasiTiba'])->name('setoran.tiba');
        Route::post('/setoran/{setoran}/timbang', [PengelolaTpsController::class, 'timbangSetoran'])->name('setoran.timbang');
        Route::patch('/setoran/{setoran}/tolak', [PengelolaTpsController::class, 'tolakSetoran'])->name('setoran.tolak');

        // Kapasitas per Jenis Sampah
        Route::get('/kapasitas', [PengelolaTpsController::class, 'kapasitas'])->name('kapasitas');
        Route::patch('/kapasitas/{jenisSampah}', [PengelolaTpsController::class, 'updateKapasitasJenis'])->name('kapasitas.update');
        Route::post('/kapasitas/{jenisSampah}/kosongkan', [PengelolaTpsController::class, 'kosongkanPetak'])->name('kapasitas.kosongkan');

        // Riwayat Setoran & Ulasan
        Route::get('/riwayat', [PengelolaTpsController::class, 'riwayat'])->name('riwayat');

        // Profil & Operasional TPS
        Route::get('/profil-tps', [PengelolaTpsController::class, 'profilTps'])->name('profil-tps');
        Route::patch('/profil-tps', [PengelolaTpsController::class, 'updateProfilTps'])->name('profil-tps.update');
        Route::post('/profil-tps/baru', [PengelolaTpsController::class, 'storeTpsBaru'])->name('profil-tps.store');

        // Notifikasi
        Route::post('/notifikasi/baca', [PengelolaTpsController::class, 'bacaNotifikasi'])->name('notifikasi.baca');
    });

// Panel Kurir Penjemputan (role: kurir)
Route::prefix('kurir')
    ->name('kurir.')
    ->middleware(['auth', KurirMiddleware::class])
    ->group(function () {
        Route::get('/', [KurirController::class, 'index'])->name('dashboard');

        // Status Ketersediaan Kurir (Online / Offline)
        Route::post('/status', [KurirController::class, 'ubahStatusKetersediaan'])->name('status');

        // Operasional Tugas Penjemputan
        Route::post('/tugas/{penjemputan}/terima', [KurirController::class, 'terimaTugas'])->name('tugas.terima');
        Route::patch('/tugas/{penjemputan}/angkut', [KurirController::class, 'konfirmasiAngkut'])->name('tugas.angkut');
        Route::patch('/tugas/{penjemputan}/tiba', [KurirController::class, 'konfirmasiTibaDiTps'])->name('tugas.tiba');
        Route::patch('/tugas/{penjemputan}/batal', [KurirController::class, 'batalkanTugas'])->name('tugas.batal');

        // Riwayat Pengantaran & Ulasan Warga
        Route::get('/riwayat', [KurirController::class, 'riwayat'])->name('riwayat');

        // Profil & Kendaraan Kurir
        Route::get('/profil', [KurirController::class, 'profil'])->name('profil');
        Route::patch('/profil', [KurirController::class, 'updateProfil'])->name('profil.update');

        // Notifikasi Kurir
        Route::post('/notifikasi/baca', [KurirController::class, 'bacaNotifikasi'])->name('notifikasi.baca');
    });
