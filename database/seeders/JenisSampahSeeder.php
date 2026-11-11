<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DATA AWAL: JENIS SAMPAH (KHUSUS KLASIFIKASI MEDIS & B3 MEDIS)
 */
class JenisSampahSeeder extends Seeder
{
    public function run(): void
    {
        $kolom = ['nama', 'kategori', 'deskripsi', 'perlu_penanganan_khusus', 'poin_per_kg'];

        $data = [
            [
                'Medis - Infeksius (Kassa, Masker, APD)',
                'medis',
                'Kassa, perban, sarung tangan medis, masker medis, selang infus, kateter, dan APD terkontaminasi cairan/darah',
                1,
                0,
            ],
            [
                'Medis - Benda Tajam / Sharps (Jarum Suntik, Skalpel, Ampul)',
                'medis',
                'Jarum suntik (spuit), jarum jahit bedah, pisau bisturi, lanset, pecahan ampul kaca dan vial obat',
                1,
                0,
            ],
            [
                'Medis - Farmasi & Obat Kedaluwarsa',
                'medis',
                'Obat kedaluwarsa, sisa sirup antibiotik, blister kapsul/tablet, sisa sediaan farmasi cair',
                1,
                0,
            ],
            [
                'Medis - Patologis & Jaringan Tubuh',
                'medis',
                'Jaringan tubuh manusia, sampel biopsi, plasenta, organ klinis, dan cairan tubuh hasil pembedahan',
                1,
                0,
            ],
            [
                'Medis - Sitotoksik & Kemoterapi',
                'medis',
                'Sisa obat penghambat pertumbuhan sel (kemoterapi), vial sitotoksik, spuit dan selang infus kemoterapi',
                1,
                0,
            ],
            [
                'Medis - Kimiawi & Reagen Laboratorium Medis',
                'medis',
                'Reagen kimia sisa tes klinis, formalin, larutan pewarna sampel/gram, disinfektan medis pekat kedaluwarsa',
                1,
                0,
            ],
            [
                'Medis - Logam Berat (Merkuri & Amalgam)',
                'medis',
                'Pecahan termometer air raksa, manometer tensimeter merkuri bekas klinik, residu tambalan amalgam gigi',
                1,
                0,
            ],
            [
                'Medis - Kemasan Bertekanan & Gas Medis',
                'medis',
                'Kaleng spray aerosol medis, cartridge inhaler asma kosong, silinder tabung gas medis mini',
                1,
                0,
            ],
            [
                'Medis - Radioaktif Medis Tingkat Rendah',
                'medis',
                'Kapas dan peralatan pencacah radioisotop kedokteran nuklir, vial sisa kontras radiologi berpendar',
                1,
                0,
            ],
            [
                'Medis - Wadah Vaksin & Produk Biologis',
                'medis',
                'Vial vaksin kedaluwarsa, botol kultur mikroorganisme, serum dan sisa produk biologis imunisasi',
                1,
                0,
            ],
        ];

        // 1. Bersihkan tps_jenis_sampah & setoran_detail untuk sampah non-medis terlebih dahulu
        $nonMedisIds = DB::table('jenis_sampah')
            ->where('kategori', '!=', 'medis')
            ->pluck('jenis_id')
            ->toArray();

        if (! empty($nonMedisIds)) {
            DB::table('setoran_detail')->whereIn('jenis_id', $nonMedisIds)->delete();
            DB::table('tps_jenis_sampah')->whereIn('jenis_id', $nonMedisIds)->delete();
            DB::table('jenis_sampah')->whereIn('jenis_id', $nonMedisIds)->delete();
        }

        // 2. Upsert seluruh data jenis sampah medis baru
        DB::table('jenis_sampah')->upsert(
            array_map(fn (array $row) => array_combine($kolom, $row), $data),
            ['nama'],
            ['kategori', 'deskripsi', 'perlu_penanganan_khusus', 'poin_per_kg']
        );

        // 3. Hapus data medis lama yang namanya tidak ada di daftar baru (jika ada nama lama seperti 'Medis - infeksius')
        $namaBaru = array_column($data, 0);
        $medisLamaIds = DB::table('jenis_sampah')
            ->where('kategori', 'medis')
            ->whereNotIn('nama', $namaBaru)
            ->pluck('jenis_id')
            ->toArray();

        if (! empty($medisLamaIds)) {
            DB::table('setoran_detail')->whereIn('jenis_id', $medisLamaIds)->delete();
            DB::table('tps_jenis_sampah')->whereIn('jenis_id', $medisLamaIds)->delete();
            DB::table('jenis_sampah')->whereIn('jenis_id', $medisLamaIds)->delete();
        }
    }
}
