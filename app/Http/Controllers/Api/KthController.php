<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Kth\CreateKthRequest;
use App\Http\Requests\Api\Kth\UpdateKthRequest;
use App\Http\Resources\Kth\KthDetailResource;
use App\Http\Resources\Kth\KthResource;
use App\Http\Resources\PostResource;
use App\Models\Kph;
use App\Models\Kth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KthController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // $this->authorizeResource(Kth::class, 'kth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Kth::class);
        $request->validate([
            'search'        => 'nullable|string|max:100',
            'page'          => 'nullable|integer|min:1',
            'limit'         => 'nullable|integer|min:10|max:50',
            'asal_kph'      => 'nullable|string|exists:kph,nama_kph',
            'sort_by'       => ['nullable', 'string', Rule::in(['nama_kth', 'created_at'])],
            'sort_direction'=> ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $user = $request->user();
        //Nama - Desa - Kabupaten - Asal Kph - Koordinator
        //Take data KTH include id_kph and nama_kph
        $query = Kth::with('kph:id,nama_kph');

        if($user->hasRole('penyuluh')) {
            $query->where('id_kph', $user->id_kph);
        }

        //Transform data format
        // $query = $data->map(function ($item) {
        //     return [
        //         'id'           => $item->id,
        //         'nama_kth'     => $item->nama_kth,
        //         'desa'         => $item->desa,
        //         'kabupaten'    => $item->kabupaten,
        //         'koordinator'  => $item->koordinator,
        //         'id_kph'       => $item->id_kph,
        //         'asal_kph'     => $item->kph->nama_kph ?? null,
        //         'created_at'   => $item->created_at,
        //         'updated_at'   => $item->updated_at
        //     ];
        // });

        //Search
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->query('search');
            return $q->where(function ($subQuery) use ($search) {
                $subQuery->where('nama_kth', 'like', "%{$search}%")
                      ->orWhere('desa', 'like', "%{$search}%")
                      ->orWhere('kabupaten', 'like', "%{$search}%")
                      ->orWhere('koordinator', 'like', "%{$search}%");
            });
        });

        //Filter KPH
        $query->when($request->filled('asal_kph'), function ($q) use ($request) {
            return $q->whereHas('kph', function ($relationQuery) use ($request) {
                $relationQuery->where('nama_kph', $request->query('asal_kph'));
            });
        });

        //Sort
        $sortBy = $request->query('sort_by', 'nama_kth');
        $sortDirection = $request->query('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        //Pagination
        $limit = $request->query('limit', 10);
        $paginator = $query->paginate($limit);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Data KTH berhasil diambil',
            'data' => [
                'data'       => KthResource::collection($paginator->items()),
                'totalPages' => $paginator->lastPage(),
                'count'      => $paginator->total(),
            ]
        ], 200);

        // return new PostResource(
        //     'Data KTH berhasil diambil', 
        //     200, 
        //     KthResource::collection($kths)
        // );
        
    } 

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateKthRequest $request)
    {
        $this->authorize('create', Kth::class);
        $validatedData = $request->validated();

        if($request->user()->hasRole('penyuluh')) {
            $validatedData['id_kph'] = $request->user()->id_kph;
        }

        // $kph = Kph::select('id')->where('nama_kph', $validatedData['nama_kph'])->first();

        $kth = Kth::create([
            'nama_kth'    => $validatedData['nama_kth'],
            'desa'        => $validatedData['desa'],
            'kecamatan'   => $validatedData['kecamatan'],
            'kabupaten'   => $validatedData['kabupaten'],
            'koordinator' => $validatedData['koordinator'],
            'nomor_wa'    => $validatedData['nomor_wa'],
            'id_kph'      => $validatedData['id_kph'],
        ]);

        return new PostResource(
            'Data KTH berhasil ditambah',
            201,
            new KthDetailResource($kth->load('kph'))
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Kth $kth)
    {
        $this->authorize('view', $kth);
        $kth->load('kph');

        return new PostResource(
            'Data KTH berhasil diambil', 
            200, 
            new KthDetailResource($kth)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKthRequest $request, Kth $kth)
    {
        $this->authorize('update', $kth);
        $validatedData = $request->validated();

        // if(isset($validatedData['nama_kph'])) {
        //     $kph = Kph::select('id')->where('nama_kph', $validatedData['nama_kph'])->firstOrFail();
        //     $validatedData['id_kph'] = $kph->id;
        //     unset($validatedData['nama_kph']);
        // }

        if ($request->user()->hasRole('penyuluh')) {
            unset($validatedData['id_kph']);
        }

        $kth->update($validatedData);

        return new PostResource(
            'Data KTH berhasil diupdate',
            200,
            new KthDetailResource($kth->fresh()->load('kph'))
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kth $kth)
    {
        $this->authorize('delete', $kth);
        $kth->delete();

        return new PostResource(
            'Data KTH berhasil dihapus',
            200,
            null
        );
    }
}