<?php

namespace App\Policies;

use App\Models\Komoditas;
use App\Models\User;

class KomoditasPolicy
{
    
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role->slug === 'sekretariat' /* || $user->role->slug === 'auditor' */) {
            return true;
        }
        return null;
    }

    
    public function viewAny(User $user): bool
    {
        return true;
    }

    
    public function view(User $user, Komoditas $komoditas): bool
    {
        return true;
    }

    
    public function create(User $user): bool
    {
        return false;
    }

    
    public function update(User $user, Komoditas $komoditas): bool
    {
        return false;
    }

   
    public function delete(User $user, Komoditas $komoditas): bool
    {
        return false;
    }
}