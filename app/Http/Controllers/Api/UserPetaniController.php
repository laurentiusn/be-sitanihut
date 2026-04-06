<?php

namespace App\Http\Controllers\Api;

use App\Helpers\RoleHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserDetailResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class UserPetaniController
 * 
 * Controller khusus untuk menangani *read-only view* pengguna dengan *Role* Petani.
 * Menyediakan kapabilitas *filtering* yang menyesuaikan jangkauan otorisasi si pengakses
 * (misal: Penyuluh hanya bisa membaca daftar Petani miliknya).
 *
 * @package App\Http\Controllers\Api
 */
class UserPetaniController extends Controller
{
    protected UserService $userService;
    /**
     * UserPetaniController Constructor.
     * 
     * @param UserService $userService Service pengelola direktori pengguna.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Menampilkan daftar Petani terpaginasi.
     * Menanamkan Aturan Khusus (*Callback Builder*) di mana Penanggung Jawab KPH dibatasi, 
     * dan Penyuluh KTH dibatasi relasinya.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $rolePetaniId = RoleHelper::id('petani');

        if (!$rolePetaniId) {
            abort(500, 'Role petani tidak ditemukan');
        }

        $request->validate([
            'search'         => 'nullable|string|max:100',
            'limit'          => 'nullable|integer|min:10|max:50',
            'asal_kph'       => 'nullable|string',
            'asal_kth'       => 'nullable|string',
            'jenis_kelamin'  => ['nullable', Rule::in(['pria', 'wanita'])],
            'sort_by'        => ['nullable', Rule::in(['nama_user'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $aturanKhususPetani = function (Builder $query, User $currentUser, Request $req) {
            if ($currentUser->hasRole('penanggung-jawab')) {
                $req->query->remove('asal_kph');
                $query->where('id_kph', $currentUser->id_kph);
            } elseif ($currentUser->hasRole('penyuluh')) {
                $req->query->remove('asal_kph');
                $query->whereHas('kth', function ($q) use ($currentUser) {
                    $q->where('id_penyuluh', $currentUser->id);
                });
            }

            $query->when($req->filled('asal_kth'), function ($q) use ($req) {
                $q->whereHas('kth', function ($relationQuery) use ($req) {
                    $relationQuery->where('nama_kth', 'like', '%' . $req->query('asal_kth') . '%');
                });
            });
        };

        $paginator = $this->userService->getPaginatedUsers(
            $rolePetaniId, 
            $request,
            $aturanKhususPetani
        );

        return response()->json([
            'statusCode' => 200,
            'message'    => 'Data Petani berhasil diambil',
            'data'       => [
                'data'       => UserResource::collection($paginator->items()),
                'totalPages' => $paginator->lastPage(),
                'count'      => $paginator->total(),
            ]
        ], 200);
    }

    /**
     * Menampilkan profil terperinci seorang Petani.
     * Mengharuskan pengguna melewati gerbang verifikasi otorisasi silang KPH/KTH (*authorizeDetailView*).
     *
     * @param User $user Model user (Implicit Model Binding)
     * @return PostResource|JsonResponse Objek Response terformat atau Error 401
     */
    public function show(User $user)
    {
        if (!$this->userService->authorizeDetailView($user, request()->user(), 'petani')) {
            return (new PostResource('Unauthorized', 401, null))->response()->setStatusCode(401);
        }

        $user->load(['role', 'kph', 'kth', 'userDetail']);

        return new PostResource(
            'Data User berhasil diambil',
            200,
            new UserDetailResource($user)
        );
    }
}