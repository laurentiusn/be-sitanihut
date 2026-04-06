<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class UserRepository
 * 
 * Kelas repository yang menangani operasi database untuk entitas User.
 *
 * @package App\Repository
 */
class UserRepository
{
    /**
     * Profil User berdasarkan UUID User
     * Relasi: Role, User Detail
     *
     * @param string $id UUID User
     * @return User|null
     */
    public function findProfileById(string $id){
        return User::with(['role', 'userDetail'])->find($id);
    }
    
    /**
     * Nomor Induk (NIK) berdasarkan Role User
     *
     * @param string $roleId ID role pengguna
     * @return Collection Daftar NIK
     */
    public function getNikByRoleId(string $roleId): Collection
    {
        return User::query()
            ->join('user_details', 'users.id', '=', 'user_details.id_user')
            ->where('users.id_role', $roleId)
            ->pluck('user_details.nomor_induk');
    }

    /**
     * Query Builder User berdasarkan ID role
     * Relasi: User Detail, KPH, KTH
     *
     * @param string $roleId ID role pengguna
     * @return Builder
     */
    public function getBaseQuery(string $roleId): Builder
    {
        return User::with(['userDetail', 'kph', 'kth'])->where('id_role', $roleId);
    }

    /**
     * Filter Search, Asal KPH, dan Jenis Kelamin pada Query
     *
     * @param Builder $query Query builder pengguna
     * @param array $filters Array parameter filter (search, asal_kph, jenis_kelamin)
     * @return Builder
     */
    public function applyCommonFilters(Builder $query, array $filters): Builder
    {
        $query->when(!empty($filters['search']), function ($q) use ($filters) {
            $search = $filters['search'];
            $q->whereHas('userDetail', function ($detail) use ($search) {
                $detail->where('nama_user', 'like', "%{$search}%")
                    ->orWhere('jenis_kelamin', 'like', "%{$search}%")
                    ->orWhere('nomor_induk', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        });

        $query->when(!empty($filters['asal_kph']), function ($q) use ($filters) {
            $q->whereHas('kph', function ($relationQuery) use ($filters) {
                $relationQuery->where('nama_kph', $filters['asal_kph']);
            });
        });

        $query->when(!empty($filters['jenis_kelamin']), function ($q) use ($filters) {
            $q->whereHas('userDetail', function ($relationQuery) use ($filters) {
                $relationQuery->where('jenis_kelamin', $filters['jenis_kelamin']);
            });
        });

        return $query;
    }

    /**
     * Sorting pada Query
     *
     * @param Builder $query Query builder pengguna
     * @param string $sortBy Kolom yang menjadi acuan pengurutan
     * @param string $sortDirection Arah pengurutan (asc/desc)
     * @return Builder
     */
    public function applySorting(Builder $query, string $sortBy, string $sortDirection): Builder
    {
        if ($sortBy === 'nama_user') {
            $query->join('user_details', 'users.id', '=', 'user_details.id_user')
                  ->select('users.*')
                  ->orderBy('user_details.nama_user', $sortDirection);
        } else {
            $query->orderBy('users.' . $sortBy, $sortDirection);
        }

        return $query;
    }
}