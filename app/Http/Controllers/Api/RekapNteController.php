<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\Laporan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RekapNteController extends Controller
{
    /**
     * Get a rekapitulasi of NTE values, with dynamic grouping, searching, and filtering.
     * This version correctly calculates based on Users with the 'petani' role.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. VALIDATION
        $request->validate([
            'periode' => 'nullable|integer|digits:4',
            'search'  => 'nullable|string|max:100',
            'type'    => ['required', 'string', Rule::in(['kph', 'kth', 'petani', 'komoditas'])],
        ]);

        $periode = $request->query('periode', now()->year);
        $type = $request->query('type');
        $search = $request->query('search');

        // --- 2. DYNAMIC QUERY BUILDING (CORRECTED LOGIC) ---
        
        // Start the query from Laporan, which is the source of the NTE value.
        if ($type === 'kph') {
            $query = Laporan::query()
                ->join('users', 'laporan.id_user', '=', 'users.id')
                ->join('kth', 'users.id_kth', '=', 'kth.id')
                ->join('kph', 'kth.id_kph', '=', 'kph.id')
                ->where('laporan.periode', $periode)
                ->where('laporan.status', 'disetujui')
                ->select('kph.nama_kph', DB::raw('SUM(laporan.nte) as total_nte'))
                ->groupBy('kph.nama_kph');

        } elseif ($type === 'kth') {
            $query = Laporan::query()
                ->join('users', 'laporan.id_user', '=', 'users.id')
                ->join('kth', 'users.id_kth', '=', 'kth.id')
                ->join('kph', 'kth.id_kph', '=', 'kph.id')
                ->where('laporan.periode', $periode)
                ->where('laporan.status', 'disetujui')
                ->select('kth.nama_kth', 'kph.nama_kph as asal_kph', DB::raw('SUM(laporan.nte) as total_nte'))
                ->groupBy('kth.nama_kth', 'kph.nama_kph');

        } elseif ($type === 'petani') {
            $query = Laporan::query()
                ->join('users', 'laporan.id_user', '=', 'users.id')
                ->join('user_details', 'users.id', '=', 'user_details.id_user')
                ->join('kth', 'users.id_kth', '=', 'kth.id')
                ->join('kph', 'kth.id_kph', '=', 'kph.id')
                ->where('laporan.periode', $periode)
                ->where('laporan.status', 'disetujui')
                ->select('user_details.nama_user as nama_petani', 'kth.nama_kth as asal_kth', 'kph.nama_kph as asal_kph', DB::raw('SUM(laporan.nte) as total_nte'))
                ->groupBy('user_details.nama_user', 'kth.nama_kth', 'kph.nama_kph');

        } elseif ($type === 'komoditas') {
            // This query is different. It does not need all the other joins.
            $query = Laporan::query()
                ->join('masa_panen', 'laporan.id', '=', 'masa_panen.id_laporan')
                ->join('komoditas', 'masa_panen.id_komoditas', '=', 'komoditas.id')
                ->where('laporan.periode', $periode)
                ->where('laporan.status', 'disetujui')
                // CRITICAL: We sum the actual harvest value, not an aggregate.
                ->select('komoditas.nama_komoditas as nama_komoditas', DB::raw('SUM(masa_panen.harga_total) as total_nte'))
                ->groupBy('komoditas.nama_komoditas');
        }

        // --- 3. APPLY FILTERS ---

        // General Search Feature
        $query->when($search, function ($q) use ($search, $type) {
            return $q->where(function ($subQuery) use ($search, $type) {
                if ($type === 'kph') {
                    $subQuery->where('kph.nama_kph', 'like', "%{$search}%");
                } elseif ($type === 'kth') {
                    $subQuery->where('kth.nama_kth', 'like', "%{$search}%")
                             ->orWhere('kph.nama_kph', 'like', "%{$search}%");
                } elseif ($type === 'petani') {
                    $subQuery->where('user_details.nama_user', 'like', "%{$search}%")
                             ->orWhere('kth.nama_kth', 'like', "%{$search}%")
                             ->orWhere('kph.nama_kph', 'like', "%{$search}%");
                } elseif ($type === 'komoditas') {
                    $subQuery->where('komoditas.nama_komoditas', 'like', "%{$search}%");
                }
            });
        });

        // Rule: Don't display results where total NTE is 0
        $query->having('total_nte', '>', 0);
        
        // --- 4. CALCULATE OVERALL TOTAL (BEFORE PAGINATION) ---
        $grandTotalNte = Laporan::query()
            ->where('periode', $periode)
            ->where('status', 'disetujui')
            ->sum('nte');

        // --- 5. EXECUTE AND PAGINATE THE MAIN QUERY ---
        $paginator = $query->orderBy('total_nte', 'desc')->paginate(20)->withQueryString();

        $formattedData = $paginator->getCollection()->map(function ($item) {
            if (isset($item->total_nte)) {
                $totalNte = (float)$item->total_nte;
                $item->total_nte = ($totalNte == (int)$totalNte) ? (int)$totalNte : $totalNte;
            }
            return $item;
        });

        // B. Format the grand total
        $formattedGrandTotal = (float)$grandTotalNte;
        if ($formattedGrandTotal == (int)$formattedGrandTotal) {
            $formattedGrandTotal = (int)$formattedGrandTotal;
        }

        // --- 6. ASSEMBLE THE RESPONSE ---
        $rekapTransformed = [
            'current_page' => $paginator->currentPage(),
            'data'         => $formattedData,
            'total'        => $paginator->total(),
            'totalPages'   => $paginator->lastPage(),
        ];

        return response()->json([
            'statusCode' => 200,
            'message'    => 'Rekapitulasi NTE berhasil diambil',
            'data'       => [
                'total_nte_keseluruhan' => $formattedGrandTotal,
                'rekap' => $rekapTransformed,
            ]
        ], 200);
    }
}