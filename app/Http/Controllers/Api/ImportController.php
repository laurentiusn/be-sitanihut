<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImportService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\Import\KomoditasImportRequest;
use App\Imports\KomoditasImport;
use App\Http\Requests\Api\Import\KthImportRequest;
use App\Imports\KthImport;
use App\Http\Requests\Api\Import\UserImportRequest;
use App\Imports\UserImport;

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

    /** Import data User
     * @param UserImportRequest $request
     * @return JsonResponse
     */
    public function userImport(UserImportRequest $request): JsonResponse
    {
        $request->validated();

        return $this->importService->handleImport(
            $request->file('file'),
            new UserImport,
            ['email', 'password', 'id_role', 'id_kph', 'id_kth',
            'nama_user', 'nomor_induk', 'jenis_kelamin', 'alamat',
            'nomor_wa', 'pendidikan_terakhir', 'pekerjaan_sampingan',
            'luas_lahan', 'jabatan']
        );
    }
}