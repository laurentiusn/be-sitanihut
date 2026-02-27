<?php

namespace Database\Seeders;

use App\Models\Kph;
use App\Models\Kth;
use App\Models\User;
use Illuminate\Database\Seeder;

class KthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semuaKph = Kph::all();

        foreach ($semuaKph as $kph) {
            // Ditentukan acak 2 s.d 4 KTH per KPH
            $jumlahKth = rand(2, 4);

            for ($i = 1; $i <= $jumlahKth; $i++) {
                
                // Format penamaan persis seperti instruksi Anda
                // Contoh jika KPH = "Tahura WAR":
                // KTH: "KTH Tahura WAR 1"
                // Penyuluh: "Penyuluh Tahura WAR 1"
                
                $namaKth = "KTH {$kph->nama_kph} {$i}";
                $namaPenyuluh = "Penyuluh {$kph->nama_kph} {$i}";

                // 1. Buat Penyuluh spesifik untuk KTH ini
                $penyuluh = User::factory()->penyuluh()->withDetail([
                    'nama_user' => $namaPenyuluh,
                ])->create([
                    'id_kph' => $kph->id,
                ]);

                // 2. Buat KTH dan hubungkan ke Penyuluh di atas
                $kth = Kth::factory()->create([
                    'nama_kth'    => $namaKth,
                    'id_kph'      => $kph->id,
                    'id_penyuluh' => $penyuluh->id,
                ]);

                // 3. Buat beberapa anggota Petani default di dalam KTH tersebut
                User::factory()->petani()->count(3)->withDetail()->create([
                    'id_kph' => $kph->id,
                    'id_kth' => $kth->id,
                ]);
            }
        }
    }
}