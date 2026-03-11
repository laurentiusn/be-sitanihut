<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Kth\CreateKthRequest;
use App\Http\Requests\Api\Kth\UpdateKthRequest;
use App\Http\Resources\Kth\KthDetailResource;
use App\Http\Resources\Kth\KthResource;
use App\Http\Resources\PostResource;
use App\Models\Kth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\KthService;

class KthController extends Controller
{
    use AuthorizesRequests;

    protected KthService $kthService;

    public function __construct(KthService $kthService)
    {
        $this->kthService = $kthService;
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
        
        // Eager Load
        $query = Kth::with([
            'kph:id,nama_kph',
            'penyuluh:id', 
            'penyuluh.userDetail:id_user,nama_user']);

        if($user->hasRole('penyuluh')) {
            $query->where('id_penyuluh', $user->id);
        }

        //Search
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->query('search');
            return $q->where(function ($subQuery) use ($search) {
                $subQuery->where('nama_kth', 'like', "%{$search}%")
                      ->orWhere('desa', 'like', "%{$search}%")
                      ->orWhere('kabupaten', 'like', "%{$search}%")
                      ->orWhereHas('kph', function ($kphQuery) use ($search) {
                            $kphQuery->where('nama_kph', 'like', "%{$search}%");
                        })
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
    } 

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateKthRequest $request)
    {
        $this->authorize('create', Kth::class);
        $validatedData = $request->validated();
        $user = $request->user();

        $data = $this->kthService->createCheck($validatedData, $user);

        $kth = Kth::create($data);

        $kth->load([
            'kph:id,nama_kph', 
            'penyuluh:id', 
            'penyuluh.userDetail:id_user,nama_user'
        ]);

        return (new PostResource(
            'Data KTH berhasil ditambah',
            201,
            new KthDetailResource($kth)))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Kth $kth)
    {
        $this->authorize('view', $kth);
        $kth->load([
            'kph:id,nama_kph', 
            'penyuluh:id', 
            'penyuluh.userDetail:id_user,nama_user'
        ]);

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
        $user = $request->user();

        $data = $this->kthService->updateCheck($validatedData, $user, $kth);
        $kth->update($data);

        $kth->refresh()->load([
            'kph:id,nama_kph', 
            'penyuluh:id', 
            'penyuluh.userDetail:id_user,nama_user'
        ]);

        return new PostResource(
            'Data KTH berhasil diupdate',
            200,
            new KthDetailResource($kth)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kth $kth)
    {
        $this->authorize('delete', $kth);
        $kth->delete();

        return (new PostResource(
            'Data KTH berhasil dihapus',
            200,
            null
        ))->response()->setStatusCode(200);
    }
}