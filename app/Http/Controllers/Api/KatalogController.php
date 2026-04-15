<?php

namespace App\Http\Controllers\Api;

use App\Helpers\RoleHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Katalog\CreateKatalogRequest;
use App\Http\Requests\Api\Katalog\UpdateKatalogRequest;
use App\Http\Resources\Katalog\KatalogResource;
use App\Http\Resources\Katalog\KatalogDetailResource;
use App\Models\Katalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\KatalogService;

/**
 * Class KatalogController
 * 
 * Controller untuk mengelola data Katalog produk hasil hutan.
 * Menangani operasi CRUD (Create, Read, Update, Delete) lengkap dengan validasi akses.
 * Role Penyuluh hanya diperbolehkan mengelola katalog miliknya sendiri.
 * Sebagian besar logika bisnis didelegasikan ke KatalogService.
 *
 * @package App\Http\Controllers\Api
 */
class KatalogController extends Controller
{
    protected KatalogService $katalogService;

    /**
     * KatalogController Constructor.
     * Menginjeksi KatalogService dan menginisialisasi RoleHelper untuk men-cache ID Role penyuluh.
     *
     * @param KatalogService $katalogService
     */
    public function __construct(KatalogService $katalogService)
    {
        $this->katalogService = $katalogService;
    }

    /**
     * Menampilkan daftar data Katalog (Endpoint /katalog).
     * Mendukung fitur pencarian (*search*), filter lokasi, pengurutan (*sorting*), 
     * serta pembatasan jumlah item per halaman (*pagination*).
     * Jika role adalah Penyuluh, query dibatasi (dikunci) hanya untuk ID user miliknya.
     *
     * @param Request $request Object request global
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search'            => 'nullable|string|max:100',
            'limit'             => 'nullable|integer|min:5|max:100',
            'lokasi'            => 'nullable|string|max:100',
            'sort_by'           => ['nullable', 'string', Rule::in(['name', 'price'])],
            'sort_direction'    => ['nullable', 'string', Rule::in(['asc', 'desc'])]
        ]);

        $query = Katalog::with('pictures');
        $rolePenyuluh = RoleHelper::id('penyuluh');

        if (Auth::check() && Auth::user()->id_role === $rolePenyuluh) {
            $query->where('id_user', Auth::user()->id);
        }

        $query->when(isset($validated['search']), function ($q) use ($validated) {
            $search = $validated['search'];
            return $q->where(function($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                         ->orWhere('kth', 'like', "%{$search}%");
            });
        });

        $query->when(isset($validated['lokasi']), function ($q) use ($validated) {
            return $q->where('location', 'like', "%{$validated['lokasi']}%");
        });

        $sortBy = $validated['sort_by'] ?? 'name';
        $sortDirection = $validated['sort_direction'] ?? 'asc';
        
        $limit = $validated['limit'] ?? 10;
        $paginator = $query->orderBy($sortBy, $sortDirection)->paginate($limit);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Data Katalog berhasil diambil',
            'data' => [
                'data' => KatalogResource::collection($paginator->items()),
                'totalPages' => $paginator->lastPage(),
                'count' => $paginator->total(),
            ]
        ], 200);
    }

    /**
     * Menyimpan data Katalog baru beserta gambar fisiknya.
     * Menggunakan Database Transaction (`DB::transaction`) untuk menjamin konsistensi data,
     * apabila upload gambar gagal maka row data di tabel katalog juga dibatalkan (*rollback*).
     *
     * @param CreateKatalogRequest $request Form Request untuk validasi pembuatan katalog
     * @return JsonResponse
     */
    public function store(CreateKatalogRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $validatedData['id_user'] = Auth::id();
        
        $katalog = DB::transaction(function () use ($validatedData, $request) {
            $katalogData = Arr::except($validatedData, ['images']);
            $katalog = Katalog::create($katalogData);

            if ($request->hasFile('images')) {
                $this->katalogService->uploadAndSavePictures($katalog, $request->file('images'));
            }
            
            return $katalog;
        });

        $katalog->load('pictures');

        return response()->json([
            'statusCode'=> 201,
            'message'   => 'Katalog berhasil ditambah',
            'data'      => new KatalogDetailResource($katalog),
        ], 201);
    }

    /**
     * Menampilkan detail lengkap dari satu buah entitas Katalog.
     * Melakukan pengecekan otorisasi (*isAuthorized*) untuk mencegah manipulasi ID parameter URL.
     *
     * @param Katalog $katalog Model Binding otomatis dari Laravel
     * @return JsonResponse
     */
    public function show(Katalog $katalog): JsonResponse
    {
        if (!$this->katalogService->isAuthorized($katalog)) {
            return $this->forbiddenResponse();
        }
        
        $katalog->load('pictures');

        return response()->json([
            'statusCode'=> 200,
            'message'   => 'Detail Katalog berhasil diambil',
            'data'      => new KatalogDetailResource($katalog),
        ], 200);
    }

    /**
     * Memperbarui detail Katalog beserta penyelarasan data gambar lampiran.
     * Dapat menghapus gambar lama yang tidak dipakai, dan mengunggah gambar baru secara simultan.
     *
     * @param UpdateKatalogRequest $request Form Request untuk validasi update
     * @param Katalog $katalog
     * @return JsonResponse
     */
    public function update(UpdateKatalogRequest $request, Katalog $katalog): JsonResponse
    {
        if (!$this->katalogService->isAuthorized($katalog)) {
            return $this->forbiddenResponse();
        }

        $validatedData = $request->validated();

        DB::transaction(function () use ($katalog, $validatedData, $request) {
            $katalog->update(Arr::except($validatedData, ['images_existing', 'images_new']));

            if ($request->has('images_existing') || $request->hasFile('images_new')) {
                $this->katalogService->syncPictures($katalog, $validatedData['images_existing'] ?? []);

                if ($request->hasFile('images_new')) {
                    $this->katalogService->uploadAndSavePictures($katalog, $request->file('images_new'));
                }
            }
        });

        return response()->json([
            'statusCode'    => 200,
            'message'       => 'Katalog berhasil diupdate',
            'data'          => new KatalogDetailResource($katalog->fresh('pictures')),
        ], 200);
    }

    /**
     * Menghapus katalog secara permanen.
     * Mengakses modul Storage lokal/publik untuk memastikan file fisik gambar 
     * benar-benar dihapus sehingga tidak membebani kapasitas disk peladen.
     *
     * @param Katalog $katalog
     * @return JsonResponse
     */
    public function destroy(Katalog $katalog): JsonResponse
    {
        if (!$this->katalogService->isAuthorized($katalog)) {
            return $this->forbiddenResponse();
        }
        
        foreach ($katalog->pictures as $picture) {
            Storage::disk('public')->delete($picture->path);
        }

        $katalog->pictures()->delete();

        $katalog->delete();
        return response()->json([
            'statusCode' => 204,
            'message'   => 'Katalog berhasil dihapus',
        ], 200);
    }

    /**
     * Menyeragamkan format response jika otorisasi gagal (*HTTP 403 Forbidden*).
     *
     * @return JsonResponse
     */
    private function forbiddenResponse(): JsonResponse
    {
        return response()->json([
            'statusCode' => 403,
            'message'    => 'Akses ditolak'
        ], 403);
    }
}