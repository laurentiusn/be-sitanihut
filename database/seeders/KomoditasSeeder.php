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
            ['kode' => 'KRT-01', 'nama_komoditas' => 'Karet', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'SWT-01', 'nama_komoditas' => 'Sawit', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'CNG-01', 'nama_komoditas' => 'Cengkeh', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'LPT-01', 'nama_komoditas' => 'Lada Putih', 'jenis_komoditas' => 'Perkebunan'],

            ['kode' => 'PLA-01', 'nama_komoditas' => 'Pala', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'PLA-02', 'nama_komoditas' => 'Pala (Ari)', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'KMR-01', 'nama_komoditas' => 'Kemiri', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'KKO-01', 'nama_komoditas' => 'Kakao', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'KOP-01', 'nama_komoditas' => 'Kopi', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'TNG-01', 'nama_komoditas' => 'Tangkil', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'PNG-01', 'nama_komoditas' => 'Pinang', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'KLP-01', 'nama_komoditas' => 'Kelapa', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'ARE-01', 'nama_komoditas' => 'Aren', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'CBE-01', 'nama_komoditas' => 'Cabe', 'jenis_komoditas' => 'Hortikultura'],
            ['kode' => 'PIS-01', 'nama_komoditas' => 'Pisang', 'jenis_komoditas' => 'Hortikultura'],
            ['kode' => 'LBS-01', 'nama_komoditas' => 'Labu Siam', 'jenis_komoditas' => 'Hortikultura'],
            ['kode' => 'KPG-01', 'nama_komoditas' => 'Kapulaga', 'jenis_komoditas' => 'Perkebunan'],
            ['kode' => 'DRN-02', 'nama_komoditas' => 'Durian', 'jenis_komoditas' => 'Hortikultura'],
            ['kode' => 'PTA-01', 'nama_komoditas' => 'Petai', 'jenis_komoditas' => 'Hortikultura'],
            ['kode' => 'JGK-01', 'nama_komoditas' => 'Jengkol', 'jenis_komoditas' => 'Hortikultura'],
            ['kode' => 'APM-01', 'nama_komoditas' => 'Apel', 'jenis_komoditas' => 'Hortikultura'],
        ];

        foreach ($komoditas as $item) {
            Komoditas::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}