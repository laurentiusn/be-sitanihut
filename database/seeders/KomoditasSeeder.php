<?php

namespace Database\Seeders;

use App\Models\Komoditas;
use Illuminate\Database\Seeder;

class KomoditasSeeder extends Seeder
{
    public function run(): void
    {
        $komoditas = [
            ['kode' => 'PDI-01', 'nama_komoditas' => 'Padi Sawah', 'jenis_komoditas' => 'Pangan'],
            ['kode' => 'JGN-01', 'nama_komoditas' => 'Jagung Hibrida', 'jenis_komoditas' => 'Pangan'],
            ['kode' => 'KDL-01', 'nama_komoditas' => 'Kedelai', 'jenis_komoditas' => 'Pangan'],
            ['kode' => 'KPR-01', 'nama_komoditas' => 'Kopi Robusta', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'KPA-01', 'nama_komoditas' => 'Kopi Arabika', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'DRN-01', 'nama_komoditas' => 'Durian Monthong', 'jenis_komoditas' => 'Hortikultura'],
        ];

        foreach ($komoditas as $item) {
            Komoditas::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}