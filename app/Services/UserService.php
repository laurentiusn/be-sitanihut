<?php

namespace App\Services;

use App\Repository\UserRepository;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class UserService
 * 
 * Service: pengambilan data pengguna dengan filter dan paginasi, 
 * pengecekan otorisasi melihat detail pengguna.
 *
 * @package App\Services
 */
class UserService
{
    /**
     * @var UserRepository Instance UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepository $userRepository Dependency injection untuk UserRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Mengambil daftar pengguna berdasarkan ID role dengan menerapkan paginasi,
     * filter pencarian, filter spesifik otorisasi (opsional), dan pengurutan (sorting).
     *
     * @param string $roleId ID role pengguna yang ingin dicari
     * @param Request $request Objek request yang berisi parameter pencarian/filter
     * @param callable|null $customAuthFilters Fungsi callback opsional untuk menerapkan filter tambahan berdasarkan otorisasi
     * @return LengthAwarePaginator Objek paginasi hasil pencarian pengguna
     */
    public function getPaginatedUsers(string $roleId, Request $request, ?callable $customAuthFilters = null): LengthAwarePaginator
    {
        $query = $this->userRepository->getBaseQuery($roleId);

        if ($customAuthFilters) {
            $customAuthFilters($query, $request->user(), $request);
        }

        $query = $this->userRepository->applyCommonFilters($query, $request->only(['search', 'asal_kph', 'jenis_kelamin']));
        
        $query = $this->userRepository->applySorting(
            $query,
            $request->query('sort_by', 'nama_user'),
            $request->query('sort_direction', 'desc')
        );

        $limit = $request->query('limit', 10);
        return $query->paginate($limit);
    }

    /**
     * Hak Akses Melihat Detail target User tertentu
     * berdasarkan aturan role yang ingin melihat
     *
     * @param User $targetUser Objek user yang ingin dilihat detailnya
     * @param User $currentUser Objek user yang sedang mengakses/login
     * @param string $targetRoleSlug Slug dari role target (misal: 'penyuluh', 'petani')
     * @return bool True jika diizinkan, False jika ditolak
     */
    public function authorizeDetailView(User $targetUser, User $currentUser, string $targetRoleSlug): bool
    {
        if ($currentUser->hasRole('sekretariat') || $currentUser->hasRole('auditor')) {
            return true;
        }

        switch ($targetRoleSlug) {
            case 'petani':
                if ($currentUser->hasRole('penanggung-jawab') && $currentUser->id_kph === $targetUser->id_kph) {
                    return true;
                }
                if ($currentUser->hasRole('penyuluh') && $targetUser->kth && $targetUser->kth->id_penyuluh === $currentUser->id) {
                    return true;
                }
                return false;

            default:
                return false;
        }
    }
}