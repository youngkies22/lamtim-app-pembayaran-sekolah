<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role',
        'isActive',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'integer',
            'isActive' => 'integer',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 1;
    }

    /**
     * Check if user is operator
     */
    public function isOperator(): bool
    {
        return $this->role === 2;
    }

    /**
     * Check if user is kepala sekolah
     */
    public function isKepsek(): bool
    {
        return $this->role === 3;
    }

    /**
     * Check if user is active
     */
    public function isActiveUser(): bool
    {
        return $this->isActive === 1;
    }

    /**
     * Scope: Active users only
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', 1);
    }

    /**
     * Scope: By role
     */
    public function scopeByRole($query, int $role)
    {
        return $query->where('role', $role);
    }
}
