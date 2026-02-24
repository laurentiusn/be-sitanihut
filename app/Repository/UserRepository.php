<?php

namespace App\Repository;

use App\Models\User;

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
}