<?php

namespace Database\Seeders;

use App\Models\Kph;
use App\Models\Kth;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Database\Seeder;

class LaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kphTahura = Kph::where('nama_kph', 'Tahura WAR')->first();
        $kthDemo = Kth::where('nama_kth', 'KTH Demo Tahura WAR')->first();

        if ($kphTahura && $kthDemo) {
            // Buat akun khusus petani yang sudah memiliki histori laporan sukses
            $petaniLaporan = User::factory()->petani()->withDetail([
                'nama_user' => 'Demo Petani Laporan Sukses',
            ])->create([
                'email'  => 'petani_laporan@demo.com',
                'id_kph' => $kphTahura->id,
                'id_kth' => $kthDemo->id,
            ]);

            // Membuat 3 Laporan Berstatus Disetujui
            Laporan::factory()
                ->count(3)
                ->status('disetujui') 
                ->withMasaTanamPanen(2) // Mengisi otomatis 2 detail masa tanam & panen per laporan
                ->create([
                    'id_user' => $petaniLaporan->id,
                    'periode' => date('Y'),
                ]);
        }

        $semuaKph = Kph::all();

        foreach ($semuaKph as $kph) {
            // Ambil HANYA 1 KTH secara acak dari KPH ini.
            // Kita juga mengecualikan KTH Demo agar tidak dobel data dengan kode di atas.
            $kthAcak = Kth::where('id_kph', $kph->id)
                ->where('nama_kth', '!=', 'KTH Demo Tahura WAR')
                ->inRandomOrder()
                ->first();

            // Pastikan KTH ditemukan di dalam KPH tersebut
            if ($kthAcak) {
                // Ambil 1 petani secara acak dari KTH terpilih
                $petani = User::where('id_kth', $kthAcak->id)
                    ->inRandomOrder()
                    ->first();

                // Jika petani ada, buatkan laporannya
                if ($petani) {
                    Laporan::factory()
                        ->count(rand(1, 2)) // Bikin 1 atau 2 laporan saja biar tidak terlalu banyak
                        ->status('disetujui')
                        ->withMasaTanamPanen(2)
                        ->create([
                            'id_user' => $petani->id,
                            'periode' => date('Y'),
                        ]);
                }
            }
        }
    }
}