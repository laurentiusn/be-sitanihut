<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Support\Collection;

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
}