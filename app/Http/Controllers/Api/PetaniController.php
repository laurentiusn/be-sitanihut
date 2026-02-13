<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Petani\CreatePetaniRequest;
use App\Http\Requests\Api\Petani\UpdatePetaniRequest;
use App\Http\Resources\Petani\PetaniDetailResource;
use App\Http\Resources\Petani\PetaniResource;
use App\Http\Resources\PostResource;
use App\Models\Petani;
use App\Models\Kth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class PetaniController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Petani::class);

        // 1. VALIDATION BLOCK (No changes needed here)
        $request->validate([
            'search'        => 'nullable|string|max:100',
            'limit'         => 'nullable|integer|min:10|max:50',
            'asal_kph'      => 'nullable|string|exists:kph,nama_kph',
            'sort_by'       => ['nullable', 'string', Rule::in(['nama_petani', 'created_at'])],
            'sort_direction'=> ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        // 2. INITIAL QUERY
        $user = Auth::user();
        $query = Petani::with('kth.kph');

        // 3. --- AUTHORIZATION & SCOPING LOGIC (THE NEW PART) ---
        // This is applied before any user-provided filters.
        if ($user->role->slug === 'penyuluh') {
            // A Penyuluh can only see farmers within their own KPH.
            if ($user->id_kph) {
                $userKphId = $user->id_kph;
                $query->whereHas('kth.kph', function ($q) use ($userKphId) {
                    $q->where('id', $userKphId);
                });
            } else {
                // If a Penyuluh has no KPH, they see no farmers.
                $query->whereRaw('1 = 0');
            }
        }

        // 4. APPLYING USER FILTERS (No changes needed here)

        //Search
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->query('search');
            return $q->where(function ($subQuery) use ($search) {
                $subQuery->where('nama_petani', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%")
                      ->orWhere('alamat', 'like', "%{$search}%");
            });
        });

        //Filter KPH
        $query->when($request->filled('asal_kph'), function ($q) use ($request) {
            return $q->whereHas('kth.kph', function ($relationQuery) use ($request) {
                $relationQuery->where('nama_kph', $request->query('asal_kph'));
            });
        });

        // 5. SORTING & PAGINATION (No changes needed here)

        //Sort
        $sortBy = $request->query('sort_by', 'nama_petani');
        $sortDirection = $request->query('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        //Pagination
        $limit = $request->query('limit', 10);
        $paginator = $query->paginate($limit)->withQueryString();

        // 6. RESPONSE (No changes needed here)
        return response()->json([
            'statusCode' => 200,
            'message'    => 'Data Petani berhasil diambil',
            'data'       => [
                'data'      => PetaniResource::collection($paginator->items()),
                'totalPages'=> $paginator->lastPage(),
                'count'     => $paginator->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(CreatePetaniRequest $request): JsonResponse
    {
        $this->authorize('create', Petani::class);

        $validatedData = $request->validated();
        $user = Auth::user();

        // --- STEP 1: FIND THE KTH BY NAME ---
        // The frontend now sends 'nama_kth' instead of 'id_kth'.
        // $kth = Kth::where('nama_kth', $validatedData['nama_kth'])->first();

        // Handle case where KTH is not found
        // if (!$kth) {
        //     return response()->json([
        //         'statusCode' => 422,
        //         'message' => 'Validation Error',
        //         'errors' => ['nama_kth' => ['The selected KTH name is invalid.']]
        //     ], 422);
        // }

        if ($user->role->slug === 'penyuluh') {
            // If the found KTH's KPH doesn't match the Penyuluh's KPH...
            $kth = Kth::find($validatedData['id_kth']);
            if (!$kth || $kth->id_kph !== $user->id_kph) {
                // ...reject the request.
                return response()->json([
                    'statusCode' => 403,
                    'message' => 'Forbidden. You can only assign a KTH that is within your own KPH.',
                ], 403);
            }
        }

        // --- STEP 3: PREPARE DATA FOR CREATION ---
        // Remove the temporary 'nama_kth' field
        // $dataToCreate = Arr::except($validatedData, ['nama_kth']);
        // // Add the correct 'id_kth' foreign key
        // $dataToCreate['id_kth'] = $kth->id;

        // The id_kph is not stored on the petani table, but if you were creating
        // a User login for them, you would get it from the KTH like this:
        // $id_kph_for_user = $kth->id_kph;
        
        // STEP 4: CREATE THE PETANI
        $petani = Petani::create([
            'nama_petani'           => $validatedData['nama_petani'],
            'nik'                   => $validatedData['nik'],
            'jenis_kelamin'         => $validatedData['jenis_kelamin'],
            'alamat'                => $validatedData['alamat'],
            'nomor_wa'              => $validatedData['nomor_wa'],
            'pendidikan_terakhir'   => $validatedData['pendidikan_terakhir'],
            'pekerjaan_sampingan'   => $validatedData['pekerjaan_sampingan'],
            'luas_lahan'            => $validatedData['luas_lahan'],
            'id_kth'                => $validatedData['id_kth'],
        ]);

        return response()->json([
            'statusCode'=> 201,
            'message'   => 'Data Petani berhasil ditambah',
            'data'      => new PetaniDetailResource($petani->load('kth.kph')) // Return the new Petani with nested data
        ], 201);
    }
    
    public function show(Petani $petani): JsonResponse
    {
        $this->authorize('view', $petani);

    
        $petani->load(['kth.kph']);

        // STEP 3: RETURN THE JSON RESPONSE
        return response()->json([
            'statusCode' => 200,
            'message'    => 'Detail Data Petani berhasil diambil',
            'data'       => new PetaniDetailResource($petani)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePetaniRequest $request, Petani $petani): JsonResponse
    {
        
        $this->authorize('update', $petani);

        $validatedData = $request->validated();
        $user = Auth::user();

        $dataToUpdate = Arr::except($validatedData, ['id_kph']);
        
        if (isset($dataToUpdate['id_kth']) && $user->role->slug === 'penyuluh') {
            $newKth = Kth::find($dataToUpdate['id_kth']);

            if (!$newKth || $newKth->id_kph !== $user->id_kph) {
                return response()->json([
                    'statusCode' => 403,
                    'message' => 'Forbidden. You can only assign a KTH that is within your own KPH.',
                ], 403);
            }
        }

        $petani->update($dataToUpdate);

        return response()->json([
            'statusCode'=> 200,
            'message'   => 'Data Petani berhasil diupdate',
            'data'      => new PetaniDetailResource($petani->fresh()->load(['kth.kph']))
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Petani $petani)
    {
        $this->authorize('delete', $petani);
        $petani->delete();

        return new PostResource(
            'Data deleted successfully.', 
            200, 
            null
        );
    }
}
