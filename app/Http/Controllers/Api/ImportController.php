<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImportService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\Import\KomoditasImportRequest;
use App\Imports\KomoditasImport;
use App\Http\Requests\Api\Import\KthImportRequest;
use App\Imports\KthImport;

/** Class ImportController
 * @package App\Http\Controllers\Api
 */
class ImportController extends Controller
{
    protected ImportService $importService;

    /** ImportController constructor
     * @param ImportService $importService
     */
    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    /** Import data Komoditas
     * @param KomoditasImportRequest $request Request yang telah ter-otorisasi
     * @return JsonResponse Response sukses atau gagal berupa rekap baris error
     */
    public function komoditasImport(KomoditasImportRequest $request): JsonResponse
    {
        $request->validated();

        return $this->importService->handleImport(
            $request->file('file'),
            new KomoditasImport(),
            ['kode', 'nama_komoditas', 'jenis_komoditas', 'komoditas_kehutanan']
        );
    }

    /** Import data KTH
     * @param KthImportRequest $request
     * @return JsonResponse
     */
    public function kthImport(KthImportRequest $request): JsonResponse
    {
        $request->validated();

        return $this->importService->handleImport(
            $request->file('file'),
            new KthImport(),
            ['nama_kth', 'desa', 'kecamatan', 'kabupaten', 
            'koordinator', 'nomor_wa','id_kph','id_penyuluh']
        );
    }
}