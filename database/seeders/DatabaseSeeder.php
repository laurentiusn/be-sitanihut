<?php

namespace Database\Seeders;

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
            KomoditasSeeder::class,
            UserSeeder::class,

            // UNCOMMENT: to create dummy data of KTH and Laporan for demo purposes
            
            // Note: You need to uncomment the related code in UserSeeder.php
            // to be able run the following line without errors

            // KthSeeder::class,
            // LaporanSeeder::class
        ]);
    }
}