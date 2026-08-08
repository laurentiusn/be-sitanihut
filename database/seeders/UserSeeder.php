<?php

namespace Database\Seeders;

use App\Models\Kph;
use App\Models\Kth;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data KPH Tahura WAR untuk kebutuhan Demo terpusat
        $kphTahura = Kph::where('nama_kph', 'Tahura WAR')->first();

        // Akun Demo 1: Sekretariat
        User::factory()->sekretariat()->withDetail([
            'nama_user' => 'Demo Sekretariat',
        ])->create(['email' => 'sekretariat@demo.com']);

        // Akun Demo 2: Auditor
        User::factory()->auditor()->withDetail([
            'nama_user' => 'Demo Auditor',
        ])->create(['email' => 'auditor@demo.com']);

        // UNCOMMENT: to create 'Penanggung Jawab', 'Penyuluh', and 'Petani' accounts

        // if ($kphTahura) {
        //     // Akun Demo 3: Penanggung Jawab (Tahura WAR)
        //     User::factory()->penanggungJawab()->withDetail([
        //         'nama_user' => 'Demo PJ Tahura WAR',
        //     ])->create([
        //         'email'  => 'pj@demo.com',
        //         'id_kph' => $kphTahura->id,
        //     ]);

        //     // Akun Demo 4: Penyuluh (Tahura WAR)
        //     $penyuluhDemo = User::factory()->penyuluh()->withDetail([
        //         'nama_user' => 'Demo Penyuluh Tahura WAR',
        //     ])->create([
        //         'email'  => 'penyuluh@demo.com',
        //         'id_kph' => $kphTahura->id,
        //     ]);

        //     // KTH Demo khusus untuk menampung Petani Demo utama
        //     $kthDemo = Kth::factory()->create([
        //         'nama_kth'    => 'KTH Demo Tahura WAR',
        //         'id_kph'      => $kphTahura->id,
        //         'id_penyuluh' => $penyuluhDemo->id,
        //     ]);

        //     // Akun Demo 5: Petani (Tahura WAR)
        //     User::factory()->petani()->withDetail([
        //         'nama_user' => 'Demo Petani Tahura WAR',
        //     ])->create([
        //         'email'  => 'petani@demo.com',
        //         'id_kph' => $kphTahura->id,
        //         'id_kth' => $kthDemo->id,
        //     ]);
        // }
    }
}