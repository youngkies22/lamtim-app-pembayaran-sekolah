<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    /**
     * Authenticate user and return token
     * Supports login with email OR username
     *
     * @param string $identifier (email or username)
     * @param string $password
     * @param bool $remember
     * @return array ['user' => User, 'token' => string]
     * @throws ValidationException
     */
    public function login(string $identifier, string $password, bool $remember = false): array
    {
        // Determine if identifier is email or username
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        // Find user first
        $user = User::where($field, $identifier)->first();
        
        if (!$user) {
            Log::warning('Failed login attempt - user not found', ['identifier' => $identifier]);
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        // Check if user is active
        if (isset($user->isActive) && $user->isActive !== 1) {
            Log::warning('Failed login attempt - user inactive', ['identifier' => $identifier]);
            throw ValidationException::withMessages([
                'email' => 'Your account is inactive. Please contact administrator.',
            ]);
        }

        // Verify password manually
        if (!Hash::check($password, $user->password)) {
            Log::warning('Failed login attempt - wrong password', ['identifier' => $identifier]);
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        // Login the user (membuat session untuk Sanctum stateful authentication)
        Auth::login($user, $remember);
        
        // Untuk Sanctum stateful authentication, kita tidak perlu membuat token
        // Session akan digunakan untuk autentikasi
        // Token hanya diperlukan jika menggunakan token-based authentication

        // Return user data without sensitive information
        return [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ];
    }

    /**
     * Logout user and revoke token
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function logout($request): void
    {
        $user = $request->user();

        if ($user) {
            Log::info('User logged out', [
                'user_id' => $user->id,
                'username' => $user->username,
            ]);
        }

        // Logout user (akan menghapus session)
        Auth::logout();
        
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    /**
     * Get authenticated user
     *
     * @return User|null
     */
    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Revoke all tokens for user
     *
     * @param User $user
     * @return void
     */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
        
        Log::info('All tokens revoked for user', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Revoke specific token
     *
     * @param string $tokenId
     * @return bool
     */
    public function revokeToken(string $tokenId): bool
    {
        $token = PersonalAccessToken::findToken($tokenId);
        
        if ($token) {
            $token->delete();
            Log::info('Token revoked', ['token_id' => $tokenId]);
            return true;
        }

        return false;
    }
}
