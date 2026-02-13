<?php

namespace Database\Seeders;

use App\Models\Kph;
use App\Models\Penyuluh;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PenyuluhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mengambil data KPH secara manual berdasarkan nama
        $kph1 = Kph::where('nama_kph', 'UPTD KPHK Tahura Wan Abdul Rachman')->first();
        $kph2 = Kph::where('nama_kph', 'UPTD KPH Pesisir Barat')->first();
        $kph3 = Kph::where('nama_kph', 'UPTD KPH Liwa')->first();
        $kph4 = Kph::where('nama_kph', 'UPTD KPH Bukit Punggur')->first();
        $kph5 = Kph::where('nama_kph', 'UPTD KPH Muara Dua')->first();
        $kph6 = Kph::where('nama_kph', 'UPTD KPH Sungai Buaya')->first();
        $kph7 = Kph::where('nama_kph', 'UPTD KPH Way Terusan')->first();
        $kph8 = Kph::where('nama_kph', 'UPTD KPH Way Waya')->first();
        $kph9 = Kph::where('nama_kph', 'UPTD KPH Tangkit Teba')->first();
        $kph10 = Kph::where('nama_kph', 'UPTD KPH Batu Tegi')->first();

        // Memastikan semua KPH ditemukan sebelum melanjutkan
        if (!$kph1 || !$kph2 || !$kph3 || !$kph4 || !$kph5 || !$kph6 || !$kph7 || !$kph8 || !$kph9 || !$kph10) {
            $this->command->info('Satu atau lebih data KPH tidak ditemukan. Pastikan KphSeeder sudah dijalankan.');
            return;
        }

        $penyuluhs = [
            [
                'nama_penyuluh' => 'Ahmad Subagja, S.Hut.',
                'nip'           => '198801012014021001',
                'nomor_wa'      => '081234567893',
                'jenis_kelamin' => 'pria',
                'jabatan'       => 'Penyuluh Kehutanan Ahli Muda',
                'id_kph'        => $kph1->id,
            ],
            [
                'nama_penyuluh' => 'Rina Wulandari, S.Hut, M.Si.',
                'nip'           => '199005202015032002',
                'nomor_wa'      => '081234567894',
                'jenis_kelamin' => 'wanita',
                'jabatan'       => 'Penyuluh Kehutanan Ahli Pertama',
                'id_kph'        => $kph2->id,
            ],
            [
                'nama_penyuluh' => 'Toni Firmansyah',
                'nip'           => '199211102017011003',
                'nomor_wa'      => '081234567895',
                'jenis_kelamin' => 'pria',
                'jabatan'       => 'Penyuluh Kehutanan Terampil',
                'id_kph'        => $kph3->id,
            ],
            [
                'nama_penyuluh' => 'Linda Setiawati',
                'nip'           => '199303152018022004',
                'nomor_wa'      => '081234567896',
                'jenis_kelamin' => 'wanita',
                'jabatan'       => 'Penyuluh Kehutanan Pelaksana',
                'id_kph'        => $kph4->id,
            ],
            [
                'nama_penyuluh' => 'Iwan Kurniawan, A.Md.Hut.',
                'nip'           => '199408222019031005',
                'nomor_wa'      => '081234567897',
                'jenis_kelamin' => 'pria',
                'jabatan'       => 'Penyuluh Kehutanan Pelaksana Lanjutan',
                'id_kph'        => $kph5->id,
            ],
            [
                'nama_penyuluh' => 'Maya Sari, S.Hut.',
                'nip'           => '198907112014032006',
                'nomor_wa'      => '081234567898',
                'jenis_kelamin' => 'wanita',
                'jabatan'       => 'Penyuluh Kehutanan Ahli Muda',
                'id_kph'        => $kph6->id,
            ],
            [
                'nama_penyuluh' => 'Rudi Hartono',
                'nip'           => '198702052013011007',
                'nomor_wa'      => '081234567899',
                'jenis_kelamin' => 'pria',
                'jabatan'       => 'Penyuluh Kehutanan Penyelia',
                'id_kph'        => $kph7->id,
            ],
            [
                'nama_penyuluh' => 'Fitriani, A.Md.',
                'nip'           => '199512302020032008',
                'nomor_wa'      => '081234567800',
                'jenis_kelamin' => 'wanita',
                'jabatan'       => 'Penyuluh Kehutanan Terampil',
                'id_kph'        => $kph8->id,
            ],
            [
                'nama_penyuluh' => 'Bambang Supriyanto, S.Hut.',
                'nip'           => '198604102012111009',
                'nomor_wa'      => '081234567801',
                'jenis_kelamin' => 'pria',
                'jabatan'       => 'Penyuluh Kehutanan Ahli Madya',
                'id_kph'        => $kph9->id,
            ],
            [
                'nama_penyuluh' => 'Siti Aminah, S.P.',
                'nip'           => '199109092016072010',
                'nomor_wa'      => '081234567802',
                'jenis_kelamin' => 'wanita',
                'jabatan'       => 'Penyuluh Kehutanan Ahli Pertama',
                'id_kph'        => $kph10->id,
            ],
        ];

        foreach ($penyuluhs as $penyuluh) {
            Penyuluh::create($penyuluh);
        }

    }
}
