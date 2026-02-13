<?php

namespace Database\Seeders;

use App\Models\Laporan;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            KphSeeder::class,
            UserSeeder::class,
            UserDetailSeeder::class,
            // PenyuluhSeeder::class,
            // PetaniSeeder::class,
            // KomoditasSeeder::class,
            // KthSeeder::class,
            // LaporanSeeder::class
        ]);
    }
}
