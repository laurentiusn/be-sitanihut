<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Penyuluh\CreatePenyuluhRequest;
use App\Http\Requests\Api\Penyuluh\UpdatePenyuluhRequest;
use App\Http\Resources\Penyuluh\PenyuluhDetailResource;
use App\Http\Resources\Penyuluh\PenyuluhResource;
use App\Http\Resources\PostResource;
use App\Models\Kph;
use App\Models\Penyuluh;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PenyuluhController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search'        => 'nullable|string|max:100',
            'page'          => 'nullable|integer|min:1',
            'limit'         => 'nullable|integer|min:10|max:50',
            'asal_kph'      => 'nullable|string|exists:kph,nama_kph',
            'sort_by'       => ['nullable', 'string', Rule::in(['nama_penyuluh', 'created_at'])],
            'sort_direction'=> ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        //Nama - NIP - Asal KPH - Jenis Kelamin - Jabatan

        $query = Penyuluh::with('kph:id,nama_kph');

        //Search
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->query('search');
            return $q->where(function ($subQuery) use ($search) {
                $subQuery->where('nama_penyuluh', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('jabatan', 'like', "%{$search}%");
            });
        });

        //Filter KPH
        $query->when($request->filled('asal_kph'), function ($q) use ($request) {
            return $q->whereHas('kph', function ($relationQuery) use ($request) {
                $relationQuery->where('nama_kph', $request->query('asal_kph'));
            });
        });

        //Sort
        $sortBy = $request->query('sort_by', 'nama_penyuluh');
        $sortDirection = $request->query('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        //Pagination
        $limit = $request->query('limit', 10);
        $paginator = $query->paginate($limit);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Data Penyuluh berhasil diambil',
            'data' => [
                'data'       => PenyuluhResource::collection($paginator->items()),
                'totalPages' => $paginator->lastPage(),
                'count'      => $paginator->total(),
            ]
        ], 200);

        // $penyuluhs = Penyuluh::with('kph')->latest()->get();
        
        // Paginate 10
        // $penyuluhs = Penyuluh::with('kph')->latest()->paginate(10);

        // return new PostResource(
        //     'Data Penyuluh berhasil diambil',
        //     200,
        //     $penyuluhs
        // );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePenyuluhRequest $request)
    {
        $validatedData = $request->validated();

        // $kph = Kph::select('id')->where('nama_kph', $validatedData['nama_kph'])->first();

        $penyuluh = Penyuluh::create([
            'nama_penyuluh' => $validatedData['nama_penyuluh'],
            'nip'           => $validatedData['nip'],
            'nomor_wa'      => $validatedData['nomor_wa'],
            'jenis_kelamin' => $validatedData['jenis_kelamin'],
            'jabatan'       => $validatedData['jabatan'],
            'id_kph'        => $validatedData['id_kph'],
        ]);

        return new PostResource(
            'Data Penyuluh berhasil ditambahkan',
            201,
            new PenyuluhDetailResource($penyuluh->load('kph'))
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Penyuluh $penyuluh)
    {
        $penyuluh->load('kph');

        return new PostResource(
            'Data Penyuluh berhasil diambil',
            200,
            new PenyuluhDetailResource($penyuluh)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePenyuluhRequest $request, Penyuluh $penyuluh)
    {
        $validatedData = $request->validated();

        // if (isset($validatedData['nama_kph'])) {
        //     $kph = Kph::select('id')->where('nama_kph', $validatedData['nama_kph'])->first();
        //     $validatedData['id_kph'] = $kph->id;
        //     unset ($validatedData['nama_kph']);
        // }

        $penyuluh->update($validatedData);

        return new PostResource(
            'Data Penyuluh berhasil diperbarui',
            200,
            new PenyuluhDetailResource($penyuluh->fresh()->load('kph'))
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penyuluh $penyuluh)
    {
        $penyuluh->delete();

        return new PostResource(
            'Data Penyuluh berhasil dihapus',
            200,
            null
        );
    }
}
