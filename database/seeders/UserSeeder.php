<?php

namespace Database\Seeders;

use App\Models\Kph;
use App\Models\Kth;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'id_role' => Role::where('slug', 'sekretariat')->first()->id,
                'email' => 'aldowijayasept27@gmail.com',
                'password' => bcrypt('Aldo123@'),
            ],
            // [
            //     'id_role' => Role::where('slug', 'auditor')->first()->id,
            //     'email' => 'auditor@example.com',
            //     'password' => bcrypt('audit12345'),
            // ],
            // [
            //     'id_role' => Role::where('slug', 'penanggung-jawab')->first()->id,
            //     'email' => 'pj@example.com',
            //     'password' => bcrypt('password123'),
            // ],
            // [
            //     'id_role' => Role::where('slug', 'penyuluh')->first()->id,
            //     'id_kph' => Kph::where('nama_kph', 'UPTD KPH Kota Agung Utara')->first()->id,
            //     'email' => 'penyuluh@example.com',
            //     'password' => bcrypt('password123'),
            // ],
            // [
            //     'id_role' => Role::where('slug', 'penyuluh')->first()->id,
            //     'id_kph' => Kph::where('nama_kph', 'UPTD KPH Sungai Buaya')->first()->id,
            //     'email' => 'penyuluh2@example.com',
            //     'password' => bcrypt('password123'),
            // ],
            // [
            //     'id_role' => Role::where('slug', 'petani')->first()->id,
            //     'id_kph' => Kph::where('nama_kph', 'UPTD KPH Kota Agung Utara')->first()->id,
            //     'id_kth' => Kth::where('nama_kth', 'KTH Curah Sejuk')->first()->id,
            //     'email' => 'ardora.334@gmail.com',
            //     'password' => bcrypt('password123'),
            // ],
            // [
            //     'id_role' => Role::where('slug', 'petani')->first()->id,
            //     'id_kph' => Kph::where('nama_kph', 'UPTD KPH Kota Agung Utara')->first()->id,
            //     'id_kth' => Kth::where('nama_kth', 'KTH Baker Lestari')->first()->id,
            //     'email' => 'petani2@example.com',
            //     'password' => bcrypt('password123'),
            // ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate([
                'email' => $user['email']
            ], $user);
        }
    }
}
