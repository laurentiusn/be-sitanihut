<?php

namespace App\Http\Controllers\Api;

use App\Helpers\RoleHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\PJ\UserPJDetailResource;
use App\Http\Resources\PJ\UserPJResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\UserService;

/**
 * Class UserPJController
 * 
 * Controller antarmuka baca (*read-only*) untuk menampilkan senarai
 * pengguna dengan jabatan/Role Penanggung Jawab (PJ) tingkat KPH.
 *
 * @package App\Http\Controllers\Api
 */
class UserPJController extends Controller
{
    protected UserService $userService;

    /**
     * UserPJController Constructor.
     * 
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Mendapatkan daftar Penanggung Jawab KPH dengan dukungan 
     * *pagination*, pencarian nama, filter asal KPH, dan pengurutan dinamis.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $rolePJId = RoleHelper::id('penanggung-jawab');
        if (!$rolePJId) {
            abort(500, 'Role PJ tidak ditemukan');
        }
        
        $request->validate([
            'search'         => 'nullable|string|max:100',
            'limit'          => 'nullable|integer|min:10|max:50',
            'asal_kph'       => 'nullable|string',
            'jenis_kelamin'  => ['nullable', Rule::in(['pria', 'wanita'])],
            'sort_by'        => ['nullable', Rule::in(['nama_user'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $paginator = $this->userService->getPaginatedUsers(
            $rolePJId,
            $request,
            null
        );

        return response()->json([
            'statusCode' => 200,
            'message'    => 'Data Penanggung Jawab berhasil diambil',
            'data'       => [
                'data'      => UserPJResource::collection($paginator->items()),
                'totalPages'=> $paginator->lastPage(),
                'count'     => $paginator->total(),
            ]
        ], 200);
    }

    /**
     * Menampilkan profil detail seorang Penanggung Jawab.
     * Melalui filter *UserService* untuk memastikan si pencari berhak melihat profil ini.
     *
     * @param User $user Parameter user
     * @return PostResource
     */
    public function show(User $user)
    {
        if (!$this->userService->authorizeDetailView($user, request()->user(), 'penanggung-jawab')) {
            return new PostResource('Unauthorized', 401, null);
        }

        $user->load(['role', 'kph', 'kth', 'userDetail']);

        return new PostResource(
            'Data User berhasil diambil',
            200,
            new UserPJDetailResource($user)
        );
    }
}