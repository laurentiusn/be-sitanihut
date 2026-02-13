<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Komoditas\CreateKomoditasRequest;
use App\Http\Requests\Api\Komoditas\UpdateKomoditasRequest;
use App\Http\Resources\KomoditasResource;
use App\Models\Komoditas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KomoditasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Komoditas::class);

        $request->validate([
            'search'    => 'nullable|string|max:100',
            'limit'     => 'nullable|integer|min:5|max:100',
        ]);

        $query = Komoditas::query();

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->query('search');
            return $q->where('kode', 'like', "%{$search}%")
                     ->orWhere('nama_komoditas', 'like', "%{$search}%")
                     ->orWhere('jenis_komoditas', 'like', "%{$search}%");
        });

        $limit = $request->query('limit', 10);
        $paginator = $query->orderBy('nama_komoditas', 'asc')->paginate($limit);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Data Komoditas berhasil diambil',
            'data' => [
                'data' => KomoditasResource::collection($paginator->items()),
                'totalPages' => $paginator->lastPage(),
                'count' => $paginator->total(),
            ]
        ], 200);
    }

    public function store(CreateKomoditasRequest $request): JsonResponse
    {
        $this->authorize('create', Komoditas::class);
        $komoditas = Komoditas::create($request->validated());
        return response()->json([
            'statusCode' => 201,
            'message' => 'Komoditas berhasil ditambah',
            'data' => new KomoditasResource($komoditas),
        ], 201);
    }

    public function show(Komoditas $komoditas): JsonResponse
    {
        $this->authorize('view', $komoditas);
        return response()->json([
            'statusCode' => 200,
            'message' => 'Detail Komoditas berhasil diambil',
            'data' => new KomoditasResource($komoditas),
        ], 200);
    }

    public function update(UpdateKomoditasRequest $request, Komoditas $komoditas): JsonResponse
    {
        $this->authorize('update', $komoditas);
        $validatedData = $request->validated();

        $komoditas->update($validatedData);

        return response()->json([
            'statusCode'    => 200,
            'message'       => 'Komoditas berhasil diupdate',
            'data'          => new KomoditasResource($komoditas->fresh()),
        ], 200);
    }

    public function destroy(Komoditas $komoditas): JsonResponse
    {
        $this->authorize('delete', $komoditas);
        $komoditas->delete();
        return response()->json([
            'statusCode' => 204,
            'message'   => 'Komoditas berhasil dihapus',
        ], 200);
    }
}