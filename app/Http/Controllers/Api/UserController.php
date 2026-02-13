<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\CreateUserRequest;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserDetailResource;
use App\Http\Resources\UserResource;
use App\Models\Kph;
use App\Models\Kth;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        // $currentUser = $request->user();
        // if (!$currentUser->hasRole('sekretariat') && !$currentUser->hasRole('penyuluh')) {
        //     return response()->json([
        //         'message' => 'Unauthorized',
        //         'status' => 401
        //     ], 401);
        // }
        $request->validate([
            'search'         => 'nullable|string|max:100',
            'page'           => 'nullable|integer|min:1',
            'limit'          => 'nullable|integer|min:10|max:50',
            'role_slug'      => 'nullable|string|exists:role,slug',
            'sort_by'        => ['nullable', 'string', Rule::in(['email', 'created_at','role'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $query = User::with(['role', 'kph', 'kth', 'userDetail'])->select('users.*');
        $currentUser = $request->user();

        if($currentUser->hasRole('penyuluh')){
            $query->where('id_kph', $currentUser->id_kph)
                ->whereHas('role', function ($relationQuery) {
                    $relationQuery->where('slug', 'petani');
                });
        }

        //Nama - Jenis Kelamin - Role - Kph
        //Search
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->query('search');
            return $q->where(function ($query) use ($search) {
        
                // The first condition inside the group is whereHas()
                $query->whereHas('userDetail', function ($subQuery) use ($search) {
                    $subQuery->where('nama_user', 'like', "%{$search}%")
                    ->orWhere('jenis_kelamin', 'like', "%{$search}%");
                })

                // The second condition is orWhereHas()
                ->orWhereHas('kph', function ($relationQuery) use ($search) {
                    $relationQuery->where('nama_kph', 'like', "%{$search}%");
                });
            });
        });

        //Filter by Role
        $query->when($request->filled('role_slug'), function ($q) use ($request) {
            return $q->whereHas('role', function ($relationQuery) use ($request) {
                $relationQuery->where('slug', $request->query('role_slug'));
            });
        });

        // if ($request->has('search')) {
        //     $searchTerm = $request->input('search');

        //     $query->where(function ($q) use ($searchTerm) {
        //         $q->orWhereHas('userDetail', function ($q_detail) use ($searchTerm) {
        //             $q_detail->where('nama_user', 'like', "%{$searchTerm}%")
        //                      ->orWhere('nomor_induk', 'like', "%{$searchTerm}%");
        //         })
        //         ->orWhereHas('role', function ($q_role) use ($searchTerm) {
        //             $q_role->where('name', 'like', "%{$searchTerm}%");
        //         })
        //         ->orWhereHas('kph', function ($q_kph) use ($searchTerm) {
        //             $q_kph->where('nama_kph', 'like', "%{$searchTerm}%");
        //         })
        //         ->orWhereHas('kth', function ($q_kth) use ($searchTerm) {
        //             $q_kth->where('nama_kth', 'like', "%{$searchTerm}%");
        //         });
        //     });
        // }

        //Sort
        $sortBy = $request->query('sort_by', 'role');
        $sortDirection = $request->query('sort_direction', 'asc');
        if($sortBy === 'role'){
            $query->join('role', 'users.id_role', '=', 'role.id')
                  ->orderBy('role.nama_role', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        // $users = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        //Pagination
        $limit = $request->query('limit', 10);
        $paginator = $query->paginate($limit);

        return response()->json([
            'statusCode' => 200,
            'message'    => 'Data User berhasil diambil',
            'data'       => [
                'data'      => UserResource::collection($paginator->items()),
                'totalPages'=> $paginator->lastPage(),
                'count'     => $paginator->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request)
    {
        // dd($request->all());
        $validatedData = $request->validated();
        $currentUser = $request->user();

        $roleToCreate = Role::findOrFail($validatedData['id_role']);

        $this->authorize('create', [User::class, $roleToCreate]);

        if ($currentUser->hasRole('penyuluh')) {
            $petaniKth = Kth::find($validatedData['id_kth']);
            if (!$petaniKth || $petaniKth->id_kph !== $currentUser->id_kph) {
                return new PostResource('Anda hanya dapat membuat petani di KPH ini', 403, null);
            }
            $validatedData['id_kph'] = $currentUser->id_kph;
        }

        $user = DB::transaction(function () use ($validatedData, $request) {
            $userData = Arr::only($validatedData, ['email', 'id_role', 'id_kph', 'id_kth']);
            $userData['password'] = bcrypt($validatedData['password']);

            $userDetailData = Arr::only($validatedData, [
                'nama_user', 'nomor_induk', 'jenis_kelamin', 'alamat', 'nomor_wa',
                'pendidikan_terakhir', 'pekerjaan_sampingan', 'luas_lahan', 'jabatan'
            ]);

            $user = User::create($userData);

            $userDetail = $user->userDetail()->create($userDetailData);

            // --- CHANGED: Apply the consistent naming strategy ---
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $originalFilename = $file->getClientOriginalName();
                $sanitizedFilename = preg_replace('/[^A-Za-z0-9\._-]/', '', $originalFilename);

                // Use the new user's ID to construct the filename
                $newFilename = $user->id . '_' . Str::random(8) . '_' . $sanitizedFilename;
                
                // Use storeAs() to save with the new name
                $path = $file->storeAs('profile_pictures', $newFilename, 'public');

                $userDetail->update(['profile_picture' => $path]);
            }
            
            return $user;
        });

        return new PostResource(
            'Data User berhasil ditambah',
            201,
            new UserDetailResource($user->load(['role', 'kph', 'kth', 'userDetail']))
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // $this->authorize('view', $user);
        $currentUser = request()->user();
        
        $canView = false;
        if ($currentUser->id === $user->id) { // Semua orang bisa lihat profil sendiri
            $canView = true;
        } elseif ($currentUser->hasRole('sekretariat') || $currentUser->hasRole('auditor')) { // Sekretariat & Auditor bisa lihat siapa saja
            $canView = true;
        } elseif ($currentUser->hasRole('penyuluh') && $user->hasRole('petani') && $currentUser->id_kph === $user->id_kph) { // Penyuluh hanya bisa lihat petani di KPH-nya
            $canView = true;
        }

        if (!$canView) {
            return new PostResource('Unauthorized', 401, null);
        }

        $user->load(['role', 'kph', 'kth', 'userDetail']);

        return new PostResource(
            'Data User berhasil diambil',
            200,
            new UserDetailResource($user)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $validatedData = $request->validated();

        // $user = DB::transaction(function () use ($user, $validatedData, $request) {
        //     $userData = Arr::only($validatedData, ['email', 'id_role', 'id_kph', 'id_kth']);
        //     if (!empty($validatedData['password'])) {
        //         $userData['password'] = bcrypt($validatedData['password']);
        //     }
        //     $user->update($userData);

        //     $userDetailData = Arr::only($validatedData, [
        //         'nama_user', 'nomor_induk', 'jenis_kelamin', 'alamat', 'nomor_wa',
        //         'pendidikan_terakhir', 'pekerjaan_sampingan', 'luas_lahan', 'jabatan'
        //     ]);

        //     // --- CHANGED: Apply the consistent naming strategy ---
        //     if ($request->hasFile('profile_picture')) {
        //         if ($user->userDetail && $user->userDetail->profile_picture) {
        //             Storage::disk('public')->delete($user->userDetail->profile_picture);
        //         }

        //         $file = $request->file('profile_picture');
        //         $originalFilename = $file->getClientOriginalName();
        //         $sanitizedFilename = preg_replace('/[^A-Za-z0-9\._-]/', '', $originalFilename);
                
        //         $newFilename = $user->id . '_' . Str::random(8) . '_' . $sanitizedFilename;

        //         $path = $file->storeAs('profile_pictures', $newFilename, 'public');
                
        //         $userDetailData['profile_picture'] = $path;
        //     }
            
        //     if ($user->userDetail && !empty($userDetailData)) {
        //         $user->userDetail->update($userDetailData);
        //     }
            
        //     return $user;
        // });  
        if($request->hasFile('profile_picture')) {
            if($user->userDetail && $user->userDetail->profile_picture) {
                // Hapus file lama jika ada
                Storage::disk('public')->delete($user->userDetail->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $validatedData['profile_picture'] = $path;
        }

        if (!empty($validatedData['password'])) {
            $bcrypt = bcrypt($validatedData['password']);
            $validatedData['password'] = $bcrypt;
        } else {
            unset($validatedData['password']);
        }

        $userData = Arr::only($validatedData, ['email', 'password', 'id_role', 'id_kph', 'id_kth']);
        $userDetailData = Arr::except($validatedData, array_keys($userData));

        $user->update($userData);
        if ($user->userDetail) {
            $user->userDetail->update($userDetailData);
        }
        
        return new PostResource(
            'Data User berhasil diupdate',
            200,
            new UserDetailResource($user->fresh()->load(['role', 'kph', 'kth', 'userDetail']))
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // $this->authorize('delete', $user);
        $currentUser = request()->user();

        $canDelete = false;
        if ($currentUser->hasRole('sekretariat') && $currentUser->id !== $user->id) {
            $canDelete = true;
        } elseif ($currentUser->hasRole('penyuluh') && $user->hasRole('petani') && $currentUser->id_kph === $user->id_kph) {
            $canDelete = true;
        }

        if (!$canDelete) {
            return new PostResource('Unauthorized', 401, null);
        }

        $user->delete();

        return new PostResource(
            'Data User berhasil dihapus',
            200,
            null
        );
    }
}