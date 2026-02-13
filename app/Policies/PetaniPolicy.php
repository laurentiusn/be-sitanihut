<?php

namespace App\Policies;

use App\Models\Petani;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PetaniPolicy
{
    /**
     * Grant all permissions to the 'sekretariat' role before other checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role->slug === 'sekretariat') {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view the list of petani (the data master page).
     * ONLY Sekretariat (handled by `before`) and Penyuluh can see this page.
     */
    public function viewAny(User $user): bool
    {
        // A Petani is NOT allowed to see the list.
        if ($user->role->slug === 'penyuluh' || $user->role->slug === 'auditor') {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view a specific petani's data master profile.
     */
    public function view(User $user, Petani $petani): bool
    {
        if ($user->role->slug === 'auditor') {
            return true;
        }
        // A Penyuluh can view a Petani if they are in the same KPH.
        if ($user->role->slug === 'penyuluh') {
            return $user->id_kph && $petani->kth && $user->id_kph === $petani->kth->id_kph;
        }
        // A Petani is NOT allowed to view any data master profiles.
        return false;
    }

    /**
     * Determine whether the user can create new petani profiles.
     */
    public function create(User $user): bool
    {
        return $user->role->slug === 'penyuluh';
    }

    /**
     * Determine whether the user can update a petani's data master profile.
     */
    public function update(User $user, Petani $petani): bool
    {
        // A Penyuluh can update a Petani if they are in the same KPH.
        if ($user->role->slug === 'penyuluh') {
            return $user->id_kph && $petani->kth && $user->id_kph === $petani->kth->id_kph;
        }
        // A Petani is NOT allowed to update any data master profiles.
        return false;
    }

    public function delete(User $user, Petani $petani): bool
    {
        // A Penyuluh can delete a Petani if they are in the same KPH.
        if ($user->role->slug === 'penyuluh') {
            return $user->id_kph && $petani->kth && $user->id_kph === $petani->kth->id_kph;
        }

        // A Petani is NOT allowed to delete any data master profiles.
        return false;
    }

}