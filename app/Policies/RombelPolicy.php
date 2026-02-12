<?php

namespace App\Policies;

use App\Models\LamtimRombel;
use App\Models\User;

class RombelPolicy
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
    public function view(User $user, LamtimRombel $lamtimRombel): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only Admin
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LamtimRombel $lamtimRombel): bool
    {
        // Only Admin
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LamtimRombel $lamtimRombel): bool
    {
        // Only Admin
        return $user->isAdmin();
    }
}
