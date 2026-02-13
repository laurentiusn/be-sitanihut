<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    // public function before(User $user, string $ability): bool|null
    // {
    //     if ($user->hasRole('sekretariat')) {
    //         return true;
    //     }
    //     return null;
    // }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $currentUser): bool
    {
        return !$currentUser->hasRole('petani');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->id === $targetUser->id) {
            return true;
        }
        if ($currentUser->hasRole('penyuluh')) {
            return $targetUser->hasRole('petani') && $targetUser->id_kph === $currentUser->id_kph;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $currentUser, Role $roleToCreate): bool
    {
        if ($currentUser->hasRole('sekretariat')) {
            return in_array($roleToCreate->slug, ['auditor', 'penyuluh', 'petani', 'penanggung-jawab']);
        }
        if ($currentUser->hasRole('penyuluh')) {
            return $roleToCreate->slug === 'petani';
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->id === $targetUser->id) {
            return true;
        }

        if ($currentUser->hasRole('sekretariat')) {
            return $targetUser->hasRole('penyuluh') || $targetUser->hasRole('petani') || $targetUser->hasRole('auditor') || $targetUser->hasRole('penanggung-jawab');
        }

        if ($currentUser->hasRole('penyuluh')) {
            return $targetUser->hasRole('petani') && $targetUser->id_kph === $currentUser->id_kph;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        if ($currentUser->hasRole('sekretariat')) {
            return $targetUser->hasRole('penyuluh') || $targetUser->hasRole('petani') || $targetUser->hasRole('auditor') || $targetUser->hasRole('penanggung-jawab');
        }

        if ($currentUser->hasRole('penyuluh')) {
            return $targetUser->hasRole('petani') && $targetUser->id_kph === $currentUser->id_kph;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $currentUser, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
