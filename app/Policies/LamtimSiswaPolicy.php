<?php

namespace App\Policies;

use App\Models\LamtimSiswa;
use App\Models\User;

class LamtimSiswaPolicy
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
    public function view(User $user, LamtimSiswa $lamtimSiswa): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin & Operator
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LamtimSiswa $lamtimSiswa): bool
    {
        // Admin & Operator
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LamtimSiswa $lamtimSiswa): bool
    {
        // Only Admin
        return $user->isAdmin();
    }
}
