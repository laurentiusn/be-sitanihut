<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Informasi\CreateInformasiRequest;
use App\Http\Requests\Api\Informasi\UpdateInformasiRequest;
use App\Http\Resources\Informasi\InformasiResource;
use App\Http\Resources\Informasi\InformasiDetailResource;
use App\Models\Informasi;
use App\Services\InformasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Class InformasiController
 * @package App\Http\Controllers\Api
 */
class InformasiController extends Controller
{
    protected InformasiService $informasiService;

    /** InformasiController constructor
     * @param InformasiService $informasiService
     */
    public function __construct(InformasiService $informasiService)
    {
        $this->informasiService = $informasiService;
    }

    /** Menampilkan daftar entri Informasi
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search'         => 'nullable|string|max:100',
            'page'           => 'nullable|integer|min:1',
            'limit'          => 'nullable|integer|min:10|max:50',
            'sort_by'        => ['nullable', 'string', Rule::in(['date', 'created_at'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $query = Informasi::query();

        // Pendelegasian ke InformasiService
        $this->informasiService->applySearchFilter($query, $request->query('search'));

        $query->orderBy(
            $request->query('sort_by', 'date'), 
            $request->query('sort_direction', 'desc')
        );

        $paginator = $query->paginate($request->query('limit', 10));

        return response()->json([
            'statusCode' => 200,
            'message'    => 'Data Informasi berhasil diambil',
            'data'       => [
                'data'       => InformasiResource::collection($paginator->items()),
                'totalPages' => $paginator->lastPage(),
                'count'      => $paginator->total(),
            ]
        ], 200);
    }

    /** Menyimpan data Informasi baru
     * @param CreateInformasiRequest $request Form request terotorisasi
     * @return JsonResponse
     */
    public function store(CreateInformasiRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        if ($request->hasFile('image')) {
            $validatedData = array_merge($validatedData, $this->informasiService->handleImageUpload($request->file('image')));
        }

        $informasi = Informasi::create($validatedData);

        return response()->json([
            'statusCode' => 201,
            'message'    => 'Informasi berhasil ditambahkan',
            'data'       => new InformasiDetailResource($informasi)
        ], 201);
    }

    /** Menampilkan detail Informasi
     * @param Informasi $informasi Implicit Route Model Binding
     * @return JsonResponse
     */
    public function show(Informasi $informasi): JsonResponse
    {
        return response()->json([
            'statusCode' => 200,
            'message'    => 'Data Informasi berhasil diambil',
            'data'       => new InformasiDetailResource($informasi)
        ], 200);
    }

    /** Memperbarui data Informasi
     * @param UpdateInformasiRequest $request Form request untuk aturan validasi update
     * @param Informasi $informasi Target data yang akan di-update
     * @return JsonResponse
     */
    public function update(UpdateInformasiRequest $request, Informasi $informasi): JsonResponse
    {
        $validatedData = $request->validated();

        // Pendelegasian pemrosesan gambar ke Service
        $validatedData = $this->informasiService->processImageUpdate($request, $informasi, $validatedData);

        $informasi->update($validatedData);

        return response()->json([
            'statusCode' => 200,
            'message'    => 'Informasi berhasil diperbarui',
            'data'       => new InformasiDetailResource($informasi)
        ], 200);
    }

    /** Menghapus artikel Informasi
     * @param Informasi $informasi
     * @return JsonResponse
     */
    public function destroy(Informasi $informasi): JsonResponse
    {
        // Pendelegasian penghapusan file ke Service
        $this->informasiService->deleteImageIfExists($informasi->path_image);

        $informasi->delete();

        return response()->json([
            'statusCode' => 204,
            'message'    => 'Informasi berhasil dihapus',
            'data'       => null
        ], 200);
    }
}