<?php

namespace App\Http\Controllers\Api;

use App\Helpers\RoleHelper;
use App\Http\Controllers\Controller;
use App\Models\Kph;
use App\Models\Kth;
use App\Models\Laporan;
use App\Models\MasaPanen;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\StatistikService;

/**
 * Class StatistikController
 * 
 * Controller untuk menyediakan ragam titik akhir (*endpoints*) komputasi statistik analitik.
 * Berperan sentral dalam mensuplai *Dashboard* utama dengan agregat data silang dari beberapa tabel.
 *
 * @package App\Http\Controllers\Api
 */
class StatistikController extends Controller
{
    protected StatistikService $statistikService;

    /**
     * StatistikController Constructor.
     * 
     * @param StatistikService $statistikService Layanan bisnis yang membawahi *query* kompleks.
     */
    public function __construct(StatistikService $statistikService)
    {
        $this->statistikService = $statistikService;
    }

    /**
     * Menjalankan serangkaian komputasi basis data secara komprehensif.
     * Termasuk: Pengelompokkan demografi Petani/Penyuluh berdasar gender, daftar *top performer* KTH,
     * serta pemeringkatan omset NTE dan volume komoditas. Seluruh *response* dikompilasi ke dalam satu balasan JSON.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $rolePetaniId = RoleHelper::id('petani');
        $rolePenyuluhId = RoleHelper::id('penyuluh');

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

        $rangeUmurPetani = $this->statistikService->getRangeUmurPetani($rolePetaniId);

        $data = [
            'jumlah_petani'     => $jumlahPetani,
            'jumlah_kph'        => $jumlahKph,
            'jumlah_kth'        => $jumlahKth,
            'jumlah_penyuluh'   => $jumlahPenyuluh,
            'petani_per_kph'    => $petaniPerKph,
            'top_10_kth_by_nte' => $top10KthByNte,
            'top_10_komoditas_by_volume'  => $top10KomoditasByVolume,
            'top_10_komoditas_by_nte' => $top10KomoditasByNte,
            'range_umur_petani' => $rangeUmurPetani,
        ];

        return response()->json([
            'statusCode' => 200,
            'message'    => 'Data statistik berhasil diambil',
            'data'       => $data,
        ], 200);
    }
}