<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LogLaporanResource;
use App\Models\LogLaporan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LogLaporanController extends Controller
{
    /**
     * Display a listing of the Laporan logs.
     * This is intended for Sekretariat use only.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'start_date'    => 'nullable|date_format:Y-m-d',
            'end_date'      => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'search'        => 'nullable|string|max:100',
            'page'          => 'nullable|integer|min:1',
            'limit'         => 'nullable|integer|min:10|max:50',
            'periode'       => 'nullable|integer|digits:4',
            'bulan'         => ['nullable', 'string', Rule::in(['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'])],
            'status'        => ['nullable', 'string', Rule::in(['disetujui', 'diverifikasi',  'ditolak'])],
            'sort_by'       => ['nullable', 'string', Rule::in(['created_by', 'periode', 'status'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $query = LogLaporan::with(['pengaju.userDetail', 'pemeriksa.userDetail']);

        //Search
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->query('search');
            return $q->where(function ($subQuery) use ($search) {
                $subQuery->whereHas('pengaju.userDetail', function ($q_detail) use ($search) {
                    $q_detail->where('nama_user', 'like', "%{$search}%");
                })->orWhereHas('pemeriksa.userDetail', function ($q_detail) use ($search) {
                    $q_detail->where('nama_user', 'like', "%{$search}%");
                });
            });
        });

        // --- Start & End Date Filter ---x
        $query->when($request->filled('start_date'), function ($q) use ($request) {
            return $q->whereDate('created_at', '>=', $request->query('start_date'));
        });

        $query->when($request->filled('end_date'), function ($q) use ($request) {
            return $q->whereDate('created_at', '<=', $request->query('end_date'));
        });

        // $query->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
        //     return $q->whereBetween('created_at', [$request->query('start_date') . ' 00:00:00', $request->query('end_date') . ' 23:59:59']);
        // });

        // --- Period (Year) Filter ---
        $query->when($request->filled('periode'), function ($q) use ($request) {
            return $q->where('periode', $request->query('periode'));
        });

        // --- Bulan Filter ---
        $query->when($request->filled('bulan'), function ($q) use ($request) {
            return $q->where('bulan', $request->query('bulan'));
        });

        // --- Status Filter ---
        $query->when($request->filled('status'), function ($q) use ($request) {
            return $q->where('status', $request->query('status'));
        });

        // --- Sorting ---
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $limit = $request->query('limit', 10);
        $paginator = $query->paginate($limit)->withQueryString();

        return response()->json([
            'statusCode' => 200,
            'message'   => 'Log Laporan berhasil diambil',
            'data'      => [
                'data'          => LogLaporanResource::collection($paginator),
                'totalPages'    => $paginator->lastPage(),
                'count'         => $paginator->total(),
            ]
        ], 200);
    }
}
