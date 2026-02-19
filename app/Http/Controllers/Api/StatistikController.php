<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kph;
use App\Models\Kth;
use App\Models\Laporan;
use App\Models\MasaPanen;
use App\Models\Role;
use App\Models\User;
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
        $rolePetaniId = Role::where('slug', 'petani')->value('id');
        $rolePenyuluhId = Role::where('slug', 'penyuluh')->value('id');

        if (!$rolePetaniId || !$rolePenyuluhId) {
            return response()->json([
                'message' => 'Role tidak ditemukan'
            ], 500);
        }

        $petaniByGender = User::query()
            ->join('user_details', 'users.id', '=', 'user_details.id_user')
            ->where('users.id_role', $rolePetaniId)
            ->select(
                'user_details.jenis_kelamin',
                DB::raw('COUNT(users.id) as total')
            )
            ->groupBy('user_details.jenis_kelamin')
            ->pluck('total', 'user_details.jenis_kelamin');

        $jumlahPetani = [
            'pria'   => $petaniByGender->get('pria', 0),
            'wanita' => $petaniByGender->get('wanita', 0),
            'total'  => $petaniByGender->sum(),
        ];

        $penyuluhByGender = User::query()
            ->join('user_details', 'users.id', '=', 'user_details.id_user')
            ->where('users.id_role', $rolePenyuluhId)
            ->select(
                'user_details.jenis_kelamin',
                DB::raw('COUNT(users.id) as total')
            )
            ->groupBy('user_details.jenis_kelamin')
            ->pluck('total', 'user_details.jenis_kelamin');

        $jumlahPenyuluh = [
            'pria'   => $penyuluhByGender->get('pria', 0),
            'wanita' => $penyuluhByGender->get('wanita', 0),
            'total'  => $penyuluhByGender->sum(),
        ];

        $jumlahKph = Kph::count();

        $jumlahKth = Kth::count();

        $petaniPerKph = Kph::query()
            ->leftJoin('users', function ($join) use ($rolePetaniId) {
                $join->on('kph.id', '=', 'users.id_kph')
                    ->where('users.id_role', $rolePetaniId);
            })
            ->select(
                'kph.id',
                'kph.nama_kph',
                DB::raw('COUNT(users.id) as total_petani')
            )
            ->groupBy('kph.id', 'kph.nama_kph')
            ->orderByDesc('total_petani')
            ->get();

        $top10KthByNte = Laporan::query()
            ->select('kth.nama_kth', DB::raw('SUM(laporan.nte) as total_nte'))
            ->where('laporan.status', 'disetujui')
            ->whereYear('laporan.tanggal', now()->year)
            ->join('users', 'laporan.id_user', '=', 'users.id')
            ->join('kth', 'users.id_kth', '=', 'kth.id')
            ->where('laporan.nte', '>', 0)
            ->groupBy('kth.nama_kth')
            ->orderByDesc('total_nte')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $totalNte = (float)$item->total_nte;
                $item->total_nte = ($totalNte == (int)$totalNte) ? (int)$totalNte : $totalNte;
                return $item;
            });

        $top10KomoditasByVolume = MasaPanen::query()
            ->join('laporan', 'masa_panen.id_laporan', '=', 'laporan.id')
            ->join('komoditas', 'masa_panen.id_komoditas', '=', 'komoditas.id')
            ->where('laporan.status', 'disetujui')
            ->whereYear('laporan.tanggal', now()->year)
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

        $top10KomoditasByNte = MasaPanen::query()
            ->join('laporan', 'masa_panen.id_laporan', '=', 'laporan.id')
            ->join('komoditas', 'masa_panen.id_komoditas', '=', 'komoditas.id')
            ->where('laporan.status', 'disetujui')
            ->whereYear('laporan.tanggal', now()->year)
            ->select(
                'komoditas.nama_komoditas',
                'komoditas.kode',
                DB::raw('SUM(masa_panen.harga_total) as nte')
            )
            ->groupBy('komoditas.nama_komoditas', 'komoditas.kode')
            ->orderByDesc('nte')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $nteValue = (float)$item->nte;
                $item->nte = ($nteValue == (int)$nteValue) ? (int)$nteValue : $nteValue;
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
            'top_10_komoditas_by_nte' => $top10KomoditasByNte
        ];

        return response()->json([
            'statusCode' => 200,
            'message'    => 'Data statistik berhasil diambil',
            'data'       => $data,
        ], 200);
    }
}