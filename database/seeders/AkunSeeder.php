<?php

namespace Database\Seeders;

use App\Models\AlamatPengguna;
use App\Models\JenisSampah;
use App\Models\Konten;
use App\Models\KurirProfil;
use App\Models\Penjemputan;
use App\Models\Setoran;
use App\Models\SetoranDetail;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun contoh untuk tiap role + contoh TPS & setoran masuk untuk diuji.
 * Kata sandi semua akun: password
 *
 * Aman dijalankan berulang (updateOrCreate / firstOrCreate).
 */
class AkunSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public function run(): void
    {
        // ---- 1. Admin ----
        $admin = $this->akun([
            'role' => User::ROLE_ADMIN,
            'nama' => 'Administrator',
            'email' => 'admin@example.com',
            'no_hp' => '081200000001',
        ]);

        // ---- 2. Pengguna (Aktif) ----
        $pengguna = $this->akun([
            'role' => User::ROLE_PENGGUNA,
            'nama' => 'Siti Rahmawati',
            'email' => 'pengguna@example.com',
            'no_hp' => '081200000002',
        ]);

        $alamatPengguna = AlamatPengguna::updateOrCreate(
            ['user_id' => $pengguna->user_id, 'label' => 'Rumah'],
            [
                'alamat_lengkap' => 'Jl. Kenanga No. 12, RT 03/RW 05, Kel. Bandar Lor, Kota Kediri',
                'latitude' => -7.8115000,
                'longitude' => 112.0050000,
                'is_default' => true,
            ]
        );

        // ---- 3. Pengelola TPS 1 (Aktif) -> 1 Akun = 1 TPS (TPS Kenanga Bandar Lor) ----
        $pengelola = $this->akun([
            'role' => User::ROLE_PENGELOLA_TPS,
            'nama' => 'Budi Santoso',
            'email' => 'pengelola@example.com',
            'no_hp' => '081200000003',
        ]);

        $tps1 = Tps::updateOrCreate(
            ['nama_tps' => 'TPS Kenanga Bandar Lor'],
            [
                'pengelola_id' => $pengelola->user_id,
                'alamat' => 'Jl. Kenanga No. 45, Kel. Bandar Lor, Kota Kediri',
                'latitude' => -7.8142000,
                'longitude' => 112.0085000,
                'kapasitas_total_kg' => 2500,
                'kapasitas_terpakai_kg' => 420,
                'jam_buka' => '07:00',
                'jam_tutup' => '17:00',
                'no_izin' => 'IZN-DLHKP/2026/014',
                'izin_limbah_medis' => true,
                'status' => 'buka',
                'is_verified' => true,
            ]
        );

        // ---- Pengelola TPS 2 (Contoh TPS Menunggu Verifikasi Admin) ----
        $pengelola2 = $this->akun([
            'role' => User::ROLE_PENGELOLA_TPS,
            'nama' => 'Hendro Wijaya',
            'email' => 'pengelola2@example.com',
            'no_hp' => '081200000006',
        ]);

        $tps2 = Tps::updateOrCreate(
            ['nama_tps' => 'TPS Terpadu Mojoroto'],
            [
                'pengelola_id' => $pengelola2->user_id,
                'alamat' => 'Jl. Veteran No. 88, Kec. Mojoroto, Kota Kediri',
                'latitude' => -7.8012000,
                'longitude' => 111.9964000,
                'kapasitas_total_kg' => 4000,
                'kapasitas_terpakai_kg' => 0,
                'jam_buka' => '06:30',
                'jam_tutup' => '16:30',
                'no_izin' => 'IZN-DLHKP/2026/029',
                'izin_limbah_medis' => true,
                'status' => 'buka',
                'is_verified' => false,
            ]
        );

        $jenisMedis = JenisSampah::where('kategori', 'medis')->get();
        foreach ($jenisMedis as $j) {
            $tps1->jenisSampah()->syncWithoutDetaching([
                $j->jenis_id => [
                    'kapasitas_maks_kg' => 300,
                    'terpakai_kg' => str_contains($j->nama, 'Infeksius') ? 42 : (str_contains($j->nama, 'Sharps') ? 15 : 0),
                    'diterima' => true,
                ],
            ]);
            $tps2->jenisSampah()->syncWithoutDetaching([
                $j->jenis_id => [
                    'kapasitas_maks_kg' => 400,
                    'terpakai_kg' => 0,
                    'diterima' => true,
                ],
            ]);
        }

        // ---- 4. Kurir (Aktif) ----
        $kurir = $this->akun([
            'role' => User::ROLE_KURIR,
            'nama' => 'Agus Prasetyo',
            'email' => 'kurir@example.com',
            'no_hp' => '081200000004',
        ]);

        KurirProfil::updateOrCreate(
            ['user_id' => $kurir->user_id],
            [
                'jenis_kendaraan' => 'Motor roda tiga',
                'plat_nomor' => 'AG 4512 KR',
                'no_sim' => '1234-5678-000001',
                'kapasitas_angkut_kg' => 150,
                'status_ketersediaan' => 'online',
            ]
        );

        // ---- 5. Contoh Pendaftar Kurir Pending (Untuk Diuji Verifikasi oleh Admin) ----
        $kurirPending = User::firstOrCreate(
            ['email' => 'kurir.baru@example.com'],
            [
                'role' => User::ROLE_KURIR,
                'nama' => 'Rudi Hartono',
                'no_hp' => '081200000005',
                'password_hash' => self::PASSWORD,
                'status' => 'pending',
            ]
        );

        KurirProfil::updateOrCreate(
            ['user_id' => $kurirPending->user_id],
            [
                'jenis_kendaraan' => 'Mobil pikap',
                'plat_nomor' => 'AG 8821 KB',
                'no_sim' => '1234-5678-000099',
                'kapasitas_angkut_kg' => 450,
                'status_ketersediaan' => 'offline',
            ]
        );

        // ---- 6. Contoh Setoran Masuk di TPS Kenanga (Siap Ditimbang Pengelola TPS) ----
        if (! Setoran::where('kode_setoran', 'STR-20261106-0001')->exists() && $jenisMedis->isNotEmpty()) {
            $s1 = Setoran::create([
                'kode_setoran' => 'STR-20261106-0001',
                'pengguna_id' => $pengguna->user_id,
                'tps_id' => $tps1->tps_id,
                'metode' => 'dijemput',
                'catatan_pengguna' => 'Safety box jarum suntik tersegel rapi + 1 kantong biohazard kuning.',
            ]);

            $medis1 = $jenisMedis->first(fn ($item) => str_contains($item->nama, 'Infeksius')) ?? $jenisMedis->first();
            $medis2 = $jenisMedis->first(fn ($item) => str_contains($item->nama, 'Sharps')) ?? $jenisMedis->skip(1)->first();
            $medis3 = $jenisMedis->first(fn ($item) => str_contains($item->nama, 'Farmasi')) ?? $jenisMedis->skip(2)->first();

            if ($medis1) {
                SetoranDetail::create([
                    'setoran_id' => $s1->setoran_id,
                    'jenis_id' => $medis1->jenis_id,
                    'berat_estimasi_kg' => 2.40,
                ]);
            }
            if ($medis2) {
                SetoranDetail::create([
                    'setoran_id' => $s1->setoran_id,
                    'jenis_id' => $medis2->jenis_id,
                    'berat_estimasi_kg' => 1.50,
                ]);
            }
            if ($medis3) {
                SetoranDetail::create([
                    'setoran_id' => $s1->setoran_id,
                    'jenis_id' => $medis3->jenis_id,
                    'berat_estimasi_kg' => 0.80,
                ]);
            }

            Penjemputan::create([
                'setoran_id' => $s1->setoran_id,
                'alamat_id' => $alamatPengguna->alamat_id,
                'kurir_id' => $kurir->user_id,
                'jadwal_jemput' => now()->subHour(),
                'status' => 'tiba_di_tps',
                'diterima_at' => now()->subMinutes(50),
                'diangkut_at' => now()->subMinutes(30),
                'tiba_at' => now()->subMinutes(5),
            ]);

            $s1->update(['status' => 'tiba_di_tps']);
        }

        if (! Setoran::where('kode_setoran', 'STR-20261106-0002')->exists() && $jenisMedis->isNotEmpty()) {
            $s2 = Setoran::create([
                'kode_setoran' => 'STR-20261106-0002',
                'pengguna_id' => $pengguna->user_id,
                'tps_id' => $tps1->tps_id,
                'metode' => 'antar_sendiri',
                'estimasi_tiba' => now()->addMinutes(25),
                'catatan_pengguna' => 'Limbah kemasan jarum dan ampul medis dikemas dalam safety box berlabel biohazard.',
            ]);

            $medisTajam = $jenisMedis->first(fn ($item) => str_contains($item->nama, 'Sharps')) ?? $jenisMedis->first();
            $medisKimia = $jenisMedis->first(fn ($item) => str_contains($item->nama, 'Kimiawi')) ?? $jenisMedis->last();

            if ($medisTajam) {
                SetoranDetail::create([
                    'setoran_id' => $s2->setoran_id,
                    'jenis_id' => $medisTajam->jenis_id,
                    'berat_estimasi_kg' => 4.50,
                ]);
            }
            if ($medisKimia) {
                SetoranDetail::create([
                    'setoran_id' => $s2->setoran_id,
                    'jenis_id' => $medisKimia->jenis_id,
                    'berat_estimasi_kg' => 1.20,
                ]);
            }
        }

        // ---- 7. Contoh Konten Edukasi Awal ----
        Konten::updateOrCreate(
            ['slug' => 'panduan-memilah-sampah-b3-dan-medis-rumah-tangga'],
            [
                'admin_id' => $admin->user_id,
                'judul' => 'Panduan Memilah Sampah B3 dan Medis Rumah Tangga',
                'jenis' => 'edukasi',
                'ringkasan' => 'Baterai bekas, lampu neon, dan kemasan obat wajib dipisahkan dari sampah plastik dan organik sebelum disetor ke TPS.',
                'isi' => "Sampah Bahan Berbahaya dan Beracun (B3) seperti baterai bekas, aki kecil, dan pecahan lampu tidak boleh dicampur dengan sampah dapur.\n\nKhusus limbah medis (masker bekas perawatan, jarum insulin, obat kedaluwarsa) hanya dapat disetorkan ke TPS yang memiliki tanda Berizin Limbah Medis.",
                'status' => 'terbit',
                'published_at' => now(),
            ]
        );
    }

    private function akun(array $data): User
    {
        return User::updateOrCreate(
            ['email' => $data['email']],
            $data + [
                'password_hash' => self::PASSWORD,
                'status' => 'aktif',
            ]
        );
    }
}
