<?php

namespace App\Http\Controllers\Api;

use App\Helpers\RoleHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\Penyuluh\UserPenyuluhResource;
use App\Http\Resources\Penyuluh\UserPenyuluhDetailResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class UserPenyuluhController
 * 
 * Controller untuk mengelola daftar Penyuluh di lapangan.
 * Dapat menyeleksi query berdasarkan asal instansi (*Current User*) secara dinamis.
 *
 * @package App\Http\Controllers\Api
 */
class UserPenyuluhController extends Controller
{
    protected UserService $userService;

    /**
     * Konstruktor UserPenyuluhController.
     *
     * @param UserService $userService Abstraksi kueri *Directory* User.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Memuat daftar direktori Penyuluh dengan Paginasi.
     * Memiliki filter otomatis khusus di mana akses baca oleh seorang *Penanggung Jawab*
     * hanya akan menghasilkan direktori penyuluh yang beroperasi di wilayah kerjanya.
     *
     * @param Request $request Object Query string filter
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $rolePenyuluhId = RoleHelper::id('penyuluh');
        if (!$rolePenyuluhId) {
            abort(500, 'Role penyuluh tidak ditemukan');
        }
        
        $request->validate([
            'search'         => 'nullable|string|max:100',
            'limit'          => 'nullable|integer|min:10|max:50',
            'asal_kph'       => 'nullable|string',
            'jenis_kelamin'  => ['nullable', Rule::in(['pria', 'wanita'])],
            'sort_by'        => ['nullable', Rule::in(['nama_user'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $aturanKhususPenyuluh = function (Builder $query, User $currentUser, Request $req) {
            if ($currentUser->hasRole('penanggung-jawab')) {
                $req->query->remove('asal_kph');
                $query->where('id_kph', $currentUser->id_kph);
            }

            $query->when($req->filled('asal_kth'), function ($q) use ($req) {
                $q->whereHas('kth', function ($relationQuery) use ($req) {
                    $relationQuery->where('nama_kth', 'like', '%' . $req->query('asal_kth') . '%');
                });
            });
        };

        $paginator = $this->userService->getPaginatedUsers(
            $rolePenyuluhId,
            $request,
            $aturanKhususPenyuluh
        );

        return response()->json([
            'statusCode' => 200,
            'message'    => 'Data Penyuluh berhasil diambil',
            'data'       => [
                'data'      => UserPenyuluhResource::collection($paginator->items()),
                'totalPages'=> $paginator->lastPage(),
                'count'     => $paginator->total(),
            ]
        ], 200);
    }

    /**
     * Membaca rincian profil biodata Penyuluh bersangkutan.
     * Dilengkapi fungsi *guard/authorization* internal *UserService* agar wilayah kerja
     * pengguna tidak dapat dimata-matai oleh KPH silang.
     *
     * @param User $user Target Penyuluh
     * @return PostResource
     */
    public function show(User $user)
    {
        if (!$this->userService->authorizeDetailView($user, request()->user(), 'penyuluh')) {
            return (new PostResource('Unauthorized', 401, null))->response()->setStatusCode(401);
        }

        $user->load(['role', 'kph', 'kth', 'userDetail']);

        return new PostResource(
            'Data User berhasil diambil',
            200,
            new UserPenyuluhDetailResource($user)
        );
    }
}