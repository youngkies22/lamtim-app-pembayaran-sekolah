<?php

namespace App\Policies;

use App\Models\LamtimJurusan;
use App\Models\User;

class JurusanPolicy
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
    public function view(User $user, LamtimJurusan $lamtimJurusan): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Blocked for everyone (Only Edit allowed)
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LamtimJurusan $lamtimJurusan): bool
    {
        // Only Admin can update
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LamtimJurusan $lamtimJurusan): bool
    {
        // Blocked for everyone (Only Edit allowed)
        return false;
    }
}
