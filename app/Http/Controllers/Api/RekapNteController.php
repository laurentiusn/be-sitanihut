<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Rekap\RekapNteRequest;
use App\Http\Resources\RekapNteResource;
use App\Services\RekapNteService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\Rekap\RekapExportRequest;

/** Class RekapNteController
 * @package App\Http\Controllers\Api
 */
class RekapNteController extends Controller
{
    protected RekapNteService $rekapNteService;

    /** RekapNteController constructor
     * @param RekapNteService $rekapNteService
     */
    public function __construct(RekapNteService $rekapNteService)
    {
        $this->rekapNteService = $rekapNteService;
    }

    /** Menampilkan data rekapitulasi NTE
     * @param RekapNteRequest $request Object terotorisasi untuk validasi input
     * @return JsonResponse
     */
    public function index(RekapNteRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $result = $this->rekapNteService->getRekapitulasi($filters);

        $formattedGrandTotal = (float)$result['grand_total'];
        if ($formattedGrandTotal == (int)$formattedGrandTotal) {
            $formattedGrandTotal = (int)$formattedGrandTotal;
        }

        return response()->json([
            'statusCode' => 200,
            'message'    => 'Rekapitulasi NTE berhasil diambil',
            'data'       => [
                'total_nte_keseluruhan' => $formattedGrandTotal,
                'rekap' => [
                    'current_page' => $result['paginator']->currentPage(),
                    'data'         => RekapNteResource::collection($result['paginator']),
                    'total'        => $result['paginator']->total(),
                    'totalPages'   => $result['paginator']->lastPage(),
                ],
            ]
        ], 200);
    }

    /** Ekspor hasil rekapitulasi
     * @param RekapExportRequest $request Formulir request validasi (Mencakup penentuan format ekspor)
     * @return mixed Stream Response / Binary File Download / JsonResponse (Bila gagal)
     */
    public function export(RekapExportRequest $request)
    {
        $validated = $request->validated();
        
        try {
            return $this->rekapNteService->handleExport($validated);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'statusCode' => 400,
                'message'    => $e->getMessage(),
            ], 400);
        }
    }
}