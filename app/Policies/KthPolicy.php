<?php

namespace App\Policies;

use App\Models\Kth;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KthPolicy
{

    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('sekretariat')) {
            return true;
        }
        return null;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return !$user->hasRole('petani');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Kth $kth): bool
    {
        if ($user->hasRole('auditor')) {
            return true;
        }
        if ($user->hasRole('penyuluh')){
            return $user->id_kph === $kth->id_kph;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('penyuluh');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Kth $kth): bool
    {
        if ($user->hasRole('penyuluh')) {
            return $user->id_kph === $kth->id_kph;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Kth $kth): bool
    {
        if ($user->hasRole('penyuluh')) {
            return $user->id_kph === $kth->id_kph;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Kth $kth): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Kth $kth): bool
    {
        return false;
    }
}
