<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LaporanBulkService;
use App\Http\Requests\Api\Laporan\CreateBulkLaporanRequest;
use App\Http\Requests\Api\Laporan\UpdateBulkLaporanRequest;
use App\Http\Requests\Api\Laporan\DestroyBulkLaporanRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/** Class LaporanBulkController
 * @package App\Http\Controllers\Api
 */
class LaporanBulkController extends Controller
{
    protected LaporanBulkService $bulkService;

    /** LaporanBulkController constructor
     * @param LaporanBulkService $bulkService
     */
    public function __construct(LaporanBulkService $bulkService)
    {
        $this->bulkService = $bulkService;
    }

    /** Membuat laporan massal
     * @param CreateBulkLaporanRequest $request Validasi form array laporan
     * @return JsonResponse Hasil eksekusi (Sukses vs Ditolak)
     */
    public function store(CreateBulkLaporanRequest $request): JsonResponse
    {
        try {
            return response()->json([
                'statusCode' => 201, 
                'message'    => 'Laporan massal berhasil dibuat', 
                'data'       => $this->bulkService->createBulk($request->validated()['laporan'], Auth::user(), $request->file('laporan') ?? [])
            ], 201);
        } catch (\Exception $e) {
            return $this->handleError('Gagal menyimpan data', $e);
        }
    }

    /** Memperbarui laporan massal
     * @param UpdateBulkLaporanRequest $request Validasi form update array
     * @return JsonResponse
     */
    public function update(UpdateBulkLaporanRequest $request): JsonResponse
    {
        try {
            return response()->json([
                'statusCode' => 200, 
                'message'    => 'Update laporan massal selesai', 
                'data'       => $this->bulkService->updateBulk($request->validated()['laporan'], Auth::user(), $request->file('laporan') ?? [])
            ], 200);
        } catch (\Exception $e) {
            return $this->handleError('Gagal memperbarui data', $e);
        }
    }

    /** Menghapus laporan massal
     * @param DestroyBulkLaporanRequest $request Validasi form hapus
     * @return JsonResponse
     */
    public function destroy(DestroyBulkLaporanRequest $request): JsonResponse
    {
        try {
            return response()->json([
                'statusCode' => 200, 
                'message'    => 'Penghapusan laporan massal selesai', 
                'data'       => $this->bulkService->deleteBulk($request->validated()['laporan_ids'], Auth::user())
            ], 200);
        } catch (\Exception $e) {
            return $this->handleError('Gagal menghapus data', $e);
        }
    }

    /** Standar Response Error
     * @param string $message Pesan generik
     * @param \Exception $e Objek Error Exception
     * @return JsonResponse
     */
    private function handleError(string $message, \Exception $e): JsonResponse
    {
        return response()->json(['statusCode' => 500, 'message' => $message, 'error' => $e->getMessage()], 500);
    }
}