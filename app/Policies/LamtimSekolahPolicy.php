<?php

namespace App\Policies;

use App\Models\LamtimSekolah;
use App\Models\User;

class LamtimSekolahPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LamtimSekolah $lamtimSekolah): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only Admin can create
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LamtimSekolah $lamtimSekolah): bool
    {
        // Only Admin can update
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LamtimSekolah $lamtimSekolah): bool
    {
        // Only Admin can delete
        return $user->isAdmin();
    }
}
