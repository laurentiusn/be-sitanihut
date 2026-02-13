<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kph;
use App\Models\Kth; 
use App\Models\Petani;
use App\Models\Penyuluh; 
use App\Models\Laporan; 
use App\Models\MasaPanen; 
use App\Models\Komoditas;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    /**
     * Get summary statistics for the dashboard.
     * This endpoint is intended for Sekretariat use only.
     */
    public function index(): JsonResponse
    {
        $petaniByGender = Petani::query()
            ->select('jenis_kelamin', DB::raw('count(*) as total'))
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');

        $jumlahPetani = [
            'pria' => $petaniByGender->get('pria', 0),
            'wanita' => $petaniByGender->get('wanita', 0),
            'total' => $petaniByGender->sum(),
        ];


        $jumlahKph = Kph::count();


        $jumlahKth = Kth::count();



        $penyuluhByGender = Penyuluh::query()
            ->select('jenis_kelamin', DB::raw('count(*) as total'))
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');
        
        $jumlahPenyuluh = [
            'pria' => $penyuluhByGender->get('pria', 0),
            'wanita' => $penyuluhByGender->get('wanita', 0),
            'total' => $penyuluhByGender->sum(),
        ];

        $petaniPerKph = Kph::query()
            ->select('kph.nama_kph', DB::raw('COUNT(petani.id) as total_petani'))
            // Start with all KPHs, then join to their KTHs.
            ->leftJoin('kth', 'kph.id', '=', 'kth.id_kph')
            // From KTHs, join to their Petani.
            ->leftJoin('petani', 'kth.id', '=', 'petani.id_kth')
            ->groupBy('kph.id', 'kph.nama_kph') // Group by ID as well for safety
            ->orderBy('total_petani', 'desc')
            ->get();
        
        $top10KthByNte = Laporan::query()
            ->select('kth.nama_kth', DB::raw('SUM(laporan.nte) as total_nte'))
            ->where('laporan.status', 'disetujui')
            ->join('users', 'laporan.id_user', '=', 'users.id')
            ->join('kth', 'users.id_kth', '=', 'kth.id')
            ->where('laporan.nte', '>', 0)
            ->groupBy('kth.nama_kth')
            ->orderByDesc('total_nte')
            ->limit(10)
            ->get()
            ->map(function ($item){
                $totalNte = (float)$item->total_nte;
                $item->total_nte = ($totalNte == (int)$totalNte) ? (int)$totalNte : $totalNte;
                return $item;
            });

        $top10KomoditasByVolume = MasaPanen::query()
            ->join('laporan', 'masa_panen.id_laporan', '=', 'laporan.id')
            ->join('komoditas', 'masa_panen.id_komoditas', '=', 'komoditas.id')
            ->where('laporan.status', 'disetujui')
            ->select('komoditas.nama_komoditas as komoditas', DB::raw('SUM(masa_panen.jumlah) as total_volume'))
            ->groupBy('komoditas.nama_komoditas')
            ->orderByDesc('total_volume')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                // This number formatting logic is still correct and needed.
                $totalJumlah = (float)$item->total_volume;
                $item->total_volume = ($totalJumlah == (int)$totalJumlah) ? (int)$totalJumlah : $totalJumlah;
                return $item;
            });

         $top15KomoditasByNte = Komoditas::query()
            ->select('nama_komoditas', 'kode', 'nte')
            ->where('nte', '>', 0)
            ->orderByDesc('nte')
            ->limit(15)
            ->get()
            ->map(function ($item) {
                $nteValue = (float)$item->nte;
                $item->nte = ($nteValue == (int)$nteValue) ? (int)$nteValue : $nteValue;
                if (isset($item->total_volume)) {
                    $volumeValue = (float)$item->total_volume;
                    $item->total_volume = ($volumeValue == (int)$volumeValue) ? (int)$volumeValue : $volumeValue;
                }

                return $item;
            });

        $data = [
            'jumlah_petani'     => $jumlahPetani,
            'jumlah_kph'        => $jumlahKph,
            'jumlah_kth'        => $jumlahKth,
            'jumlah_penyuluh'   => $jumlahPenyuluh,
            'petani_per_kph'    => $petaniPerKph,
            'top_10_kth_by_nte' => $top10KthByNte,
            'top_10_komoditas_by_volume'  => $top10KomoditasByVolume,
            'top_15_komoditas_by_nte' => $top15KomoditasByNte
        ];

        return response()->json([
            'statusCode' => 200,
            'message'    => 'Data statistik berhasil diambil',
            'data'       => $data,
        ], 200);
    }
}