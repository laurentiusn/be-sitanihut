<?php

namespace Database\Seeders;

use App\Models\Kph;
use App\Models\Kth;
use Illuminate\Database\Seeder;

class KthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kph = Kph::where('nama_kph', 'UPTD KPHK Tahura Wan Abdul Rachman')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Wana Bakti Pesawaran',
                'desa'        => 'Desa Hurun',
                'kecamatan'   => 'Teluk Pandan',
                'kabupaten'   => 'Pesawaran',
                'koordinator' => 'Ahmad Fauzi',
                'nomor_wa'    => '081211223344',
                'id_kph'      => $kph->id,
            ]);
        }

        // 2. UPTD KPH Pesisir Barat
        $kph = Kph::where('nama_kph', 'UPTD KPH Pesisir Barat')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Krui Lestari',
                'desa'        => 'Pekon Walur',
                'kecamatan'   => 'Krui Selatan',
                'kabupaten'   => 'Pesisir Barat',
                'koordinator' => 'Bambang Irawan',
                'nomor_wa'    => '082144556677',
                'id_kph'      => $kph->id,
            ]);
        }

        // 3. UPTD KPH Liwa
        $kph = Kph::where('nama_kph', 'UPTD KPH Liwa')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Sumber Makmur Liwa',
                'desa'        => 'Pekon Watas',
                'kecamatan'   => 'Balik Bukit',
                'kabupaten'   => 'Lampung Barat',
                'koordinator' => 'Candra Wijaya',
                'nomor_wa'    => '085788990011',
                'id_kph'      => $kph->id,
            ]);
        }

        // 4. UPTD KPH Bukit Punggur
        $kph = Kph::where('nama_kph', 'UPTD KPH Bukit Punggur')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Way Kanan Sejahtera',
                'desa'        => 'Kampung Gistang',
                'kecamatan'   => 'Blambangan Umpu',
                'kabupaten'   => 'Way Kanan',
                'koordinator' => 'Dedi Setiawan',
                'nomor_wa'    => '081322334455',
                'id_kph'      => $kph->id,
            ]);
        }

        // 5. UPTD KPH Muara Dua
        $kph = Kph::where('nama_kph', 'UPTD KPH Muara Dua')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Tani Jaya Tulang Bawang',
                'desa'        => 'Kampung Warga Makmur Jaya',
                'kecamatan'   => 'Banjar Agung',
                'kabupaten'   => 'Tulang Bawang',
                'koordinator' => 'Eka Saputra',
                'nomor_wa'    => '089877665544',
                'id_kph'      => $kph->id,
            ]);
        }

        // 6. UPTD KPH Sungai Buaya
        $kph = Kph::where('nama_kph', 'UPTD KPH Sungai Buaya')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Rawa Jitu Lestari',
                'desa'        => 'Kampung Bumi Dipasena',
                'kecamatan'   => 'Rawajitu Timur',
                'kabupaten'   => 'Mesuji',
                'koordinator' => 'Fajar Nugroho',
                'nomor_wa'    => '082211335577',
                'id_kph'      => $kph->id,
            ]);
        }

        // 7. UPTD KPH Way Terusan
        $kph = Kph::where('nama_kph', 'UPTD KPH Way Terusan')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Seputih Banyak Makmur',
                'desa'        => 'Kampung Tanjung Harapan',
                'kecamatan'   => 'Seputih Banyak',
                'kabupaten'   => 'Lampung Tengah',
                'koordinator' => 'Guntur Pribadi',
                'nomor_wa'    => '085611228899',
                'id_kph'      => $kph->id,
            ]);
        }

        // 8. UPTD KPH Way Waya
        $kph = Kph::where('nama_kph', 'UPTD KPH Way Waya')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Abung Timur Bersatu',
                'desa'        => 'Desa Papan Rejo',
                'kecamatan'   => 'Abung Timur',
                'kabupaten'   => 'Lampung Utara',
                'koordinator' => 'Hadi Santoso',
                'nomor_wa'    => '087833445566',
                'id_kph'      => $kph->id,
            ]);
        }

        // 9. UPTD KPH Tangkit Teba
        $kph = Kph::where('nama_kph', 'UPTD KPH Tangkit Teba')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Terbanggi Besar Hijau',
                'desa'        => 'Kelurahan Bandar Jaya',
                'kecamatan'   => 'Terbanggi Besar',
                'kabupaten'   => 'Lampung Tengah',
                'koordinator' => 'Indra Gunawan',
                'nomor_wa'    => '081299887766',
                'id_kph'      => $kph->id,
            ]);
        }

        // 10. UPTD KPH Batu Tegi
        $kph = Kph::where('nama_kph', 'UPTD KPH Batu Tegi')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Air Naningan Asri',
                'desa'        => 'Pekon Datar Lebuay',
                'kecamatan'   => 'Air Naningan',
                'kabupaten'   => 'Tanggamus',
                'koordinator' => 'Joko Widodo',
                'nomor_wa'    => '082155667788',
                'id_kph'      => $kph->id,
            ]);
        }
        
        // 11. UPTD KPH Pematang Neba
        $kph = Kph::where('nama_kph', 'UPTD KPH Pematang Neba')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Talang Padang Jaya',
                'desa'        => 'Pekon Sinar Banten',
                'kecamatan'   => 'Talang Padang',
                'kabupaten'   => 'Tanggamus',
                'koordinator' => 'Lukman Hakim',
                'nomor_wa'    => '081388776655',
                'id_kph'      => $kph->id,
            ]);
        }

        // 12. UPTD KPH Pesawaran
        $kph = Kph::where('nama_kph', 'UPTD KPH Pesawaran')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Kedondong Rindang',
                'desa'        => 'Desa Pasar Baru',
                'kecamatan'   => 'Kedondong',
                'kabupaten'   => 'Pesawaran',
                'koordinator' => 'Muhammad Ali',
                'nomor_wa'    => '085711223344',
                'id_kph'      => $kph->id,
            ]);
        }

        // 13. UPTD KPH Gunung Balak
        $kph = Kph::where('nama_kph', 'UPTD KPH Gunung Balak')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Sekampung Udik Mandiri',
                'desa'        => 'Desa Gunung Pasir Jaya',
                'kecamatan'   => 'Sekampung Udik',
                'kabupaten'   => 'Lampung Timur',
                'koordinator' => 'Nurhadi',
                'nomor_wa'    => '089955443322',
                'id_kph'      => $kph->id,
            ]);
        }

        // 14. UPTD KPH Way Pisang
        $kph = Kph::where('nama_kph', 'UPTD KPH Way Pisang')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Kalianda Hijau Lestari',
                'desa'        => 'Desa Maja',
                'kecamatan'   => 'Kalianda',
                'kabupaten'   => 'Lampung Selatan',
                'koordinator' => 'Prabowo Subianto',
                'nomor_wa'    => '081234567890',
                'id_kph'      => $kph->id,
            ]);
        }

        // 15. UPTD KPH Batu Serampok
        $kph = Kph::where('nama_kph', 'UPTD KPH Batu Serampok')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Natar Sejuk',
                'desa'        => 'Desa Merak Batin',
                'kecamatan'   => 'Natar',
                'kabupaten'   => 'Lampung Selatan',
                'koordinator' => 'Rizki Amelia',
                'nomor_wa'    => '082299887766',
                'id_kph'      => $kph->id,
            ]);
        }

        // 16. UPTD KPH Gedong Wani
        $kph = Kph::where('nama_kph', 'UPTD KPH Gedong Wani')->first();
        if ($kph) {
            Kth::create([
                'nama_kth'    => 'KTH Jati Agung Wana Raya',
                'desa'        => 'Desa Karang Anyar',
                'kecamatan'   => 'Jati Agung',
                'kabupaten'   => 'Lampung Selatan',
                'koordinator' => 'Siti Badriah',
                'nomor_wa'    => '085811223344',
                'id_kph'      => $kph->id,
            ]);
        }

        // 17. UPTD KPH Kota Agung Utara (Data Lengkap dari Gambar)
        $kph = Kph::where('nama_kph', 'UPTD KPH Kota Agung Utara')->first();
        if ($kph) {
            $kths = [
                ['nama_kth' => 'KTH Curah Sejuk', 'desa' => 'Pekon Gisting Atas', 'kecamatan' => 'Gisting', 'kabupaten' => 'Tanggamus', 'koordinator' => 'Budi Santoso', 'nomor_wa' => '081287654321'],
                ['nama_kth' => 'KTH Tanggamus Lestari', 'desa' => 'Pekon Tanjung Anom', 'kecamatan' => 'Kotaagung Timur', 'kabupaten' => 'Tanggamus', 'koordinator' => 'Siti Aminah', 'nomor_wa' => '087812345678'],
                ['nama_kth' => 'KTH Baker Lestari', 'desa' => 'Pekon Batu Keramat', 'kecamatan' => 'Kotaagung Timur', 'kabupaten' => 'Tanggamus', 'koordinator' => 'Eko Prasetyo', 'nomor_wa' => '085698765432'],
                ['nama_kth' => 'KTH Rilau Jaya 1', 'desa' => 'Pekon Datarajan', 'kecamatan' => 'Kotaagung Pulau Panggung', 'kabupaten' => 'Tanggamus', 'koordinator' => 'Agus Wijaya', 'nomor_wa' => '089955443322'],
                ['nama_kth' => 'KTH Rilau Jaya 2', 'desa' => 'Pekon Datarajan', 'kecamatan' => 'Kotaagung Pulau Panggung', 'kabupaten' => 'Tanggamus', 'koordinator' => 'Dewi Lestari', 'nomor_wa' => '081311223344'],
            ];

            foreach ($kths as $kthData) {
                Kth::create(array_merge($kthData, ['id_kph' => $kph->id]));
            }
        }
    }
}
