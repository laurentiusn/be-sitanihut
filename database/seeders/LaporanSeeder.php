<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Petani;
use App\Models\Kth;
use App\Models\Komoditas;
use App\Models\Laporan;

class LaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. FETCH DEPENDENCIES
        $petani1 = User::where('email', 'petani@example.com')->first();
        $petani2 = User::where('email', 'petani2@example.com')->first();
        $sampleKth1 = Kth::where('nama_kth', 'KTH 11')->first();
        $sampleKth2 = Kth::where('nama_kth', 'KTH 21')->first();
        $padi = Komoditas::where('kode', 'PDI-01')->first();
        $jagung = Komoditas::where('kode', 'JGN-01')->first();
        $kedelai = Komoditas::where('kode', 'KDL-01')->first();

        // Prerequisite checks
        if (!$petani1 || !$petani2 || !$sampleKth1 || !$sampleKth2) {
            $this->command->error('Satu atau lebih User Petani atau KTH Sampel tidak ditemukan. Pastikan UserSeeder dan KthSeeder sudah dijalankan.');
            return;
        }

        // 3. DEFINE THE LAPORAN DATA
        // This structure supports arrays for masa_tanam and masa_panen.
        $laporansData = [
            // --- Laporan 1: Has 2 masa tanam and 2 masa panen records ---
            [
                'laporan_main' => [
                    'id_user'           => $petani1->id,
                    'periode'           => 2024,
                    'bulan'             => 'februari',
                    'tanggal'           => '2024-08-15',
                    'modal'             => 7500000,
                    'nte'               => 1.85,
                    'status'            => 'disetujui',
                    'catatan_petani'    => 'Laporan gabungan untuk padi dan jagung.',
                    'catatan_penyuluh'  => 'Data valid, biaya operasional sesuai untuk dua komoditas.',
                ],
                'masa_tanam' => [ // This is an array of arrays
                    [
                        'tanggal' => '2024-04-10',
                        'id_komoditas' => $padi->id,
                        'jumlah' => 120,
                        'satuan' => 'benih'
                    ],
                    [
                        'tanggal'           => '2024-04-15',
                        'id_komoditas' => $jagung->id,
                        'jumlah'            => 50,
                        'satuan'            => 'benih',
                    ],
                ],
                'masa_panen' => [
                    [
                        'tanggal'           => '2024-08-05',
                        'id_komoditas' => $padi->id,
                        'jumlah'            => 7500,
                        'harga_satuan'      => 3000,
                    ],
                    [
                        'tanggal'           => '2024-07-20',
                        'id_komoditas' => $jagung->id,
                        'jumlah'            => 1200,
                        'harga_satuan'      => 2500,
                    ]
                ]
            ],
            [
                'laporan_main' => [
                    'id_user'           => $petani2->id,
                    'periode'           => 2024,
                    'bulan'             => 'september',
                    'tanggal'           => '2024-09-20',
                    'modal'             => 12000000,
                    'nte'               => 2.10,
                    'status'            => 'menunggu',
                    'catatan_petani'    => 'Pengajuan laporan untuk komoditas jagung.',
                    'catatan_penyuluh'  => null,
                ],
                'masa_tanam' => [
                    [
                        'tanggal'           => '2024-05-01',
                        'id_komoditas' => $kedelai->id,
                        'jumlah'            => 250,
                        'satuan'            => 'bibit',
                    ]
                ],
                'masa_panen' => [
                    [
                        'tanggal'           => '2024-09-10',
                        'id_komoditas' => $kedelai->id,
                        'jumlah'            => 10000,
                        'harga_satuan'      => 3000,
                    ]
                ]
            ]
        ];

        // 4. CREATE THE LAPORAN RECORDS
        foreach ($laporansData as $data) {
            DB::transaction(function () use ($data) {

                // --- THIS IS THE FIX ---
                // 1. Calculate harga_total and the sum for NTE
                $nteTotal = 0;
                $processedMasaPanen = [];
                if (!empty($data['masa_panen'])) {
                    foreach ($data['masa_panen'] as $panen) {
                        $hargaTotal = (float)($panen['jumlah'] ?? 0) * (float)($panen['harga_satuan'] ?? 0);
                        $panen['harga_total'] = $hargaTotal;
                        $processedMasaPanen[] = $panen;
                        $nteTotal += $hargaTotal;
                    }
                }

                // 2. Add the calculated NTE to the main laporan data
                $laporanData = $data['laporan_main'];
                $laporanData['nte'] = $nteTotal;

                // 3. Create the parent Laporan record with the correct data
                $laporan = Laporan::updateOrCreate(
                    [
                        'id_user' => $laporanData['id_user'],
                        'periode' => $laporanData['periode']
                    ],
                    $laporanData
                );

                // 4. Delete old records and create new ones with the processed data
                $laporan->masaTanam()->delete();
                $laporan->masaPanen()->delete();

                $laporan->masaTanam()->createMany($data['masa_tanam']);
                $laporan->masaPanen()->createMany($processedMasaPanen);
            });
        }
    }
}
