<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request (Web & API)
     */
    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login(
                $request->email,
                $request->password,
                $request->boolean('remember')
            );

            // If API request, return JSON
            // Untuk Sanctum stateful authentication, kita menggunakan session
            // Session sudah otomatis dikelola oleh Laravel dan dikirim via cookie
            if ($request->expectsJson() || $request->is('api/*')) {
                // Regenerate session untuk keamanan
                if ($request->hasSession()) {
                    $request->session()->regenerate();
                }

                // Return response dengan user data

                // Session cookie akan otomatis dikirim oleh Laravel
                $responseData = [
                    'user' => $result['user'],
                ];

                return ResponseHelper::success($responseData, 'Login berhasil');
            }

            // Regenerate session for web requests only
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            // Web redirect
            return redirect()->intended('/dashboard');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // If API request, return JSON error
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::error('Email atau password salah', 401, $e->errors());
            }

            throw $e;
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Login error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            // If API request, return JSON error
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::error('Terjadi kesalahan saat login: ' . $e->getMessage(), 500);
            }

            throw $e;
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ResponseHelper::unauthorized('User not authenticated');
            }

            return ResponseHelper::success([
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'isActive' => $user->isActive,
                'avatar' => $user->avatar,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting current user: ' . $e->getMessage());
            return ResponseHelper::error('Terjadi kesalahan saat mengambil data user', 500);
        }
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request);

        // If API request, return JSON
        // Session akan otomatis dihapus oleh Auth::logout()
        if ($request->expectsJson() || $request->is('api/*')) {
            return ResponseHelper::success(null, 'Logout berhasil');
        }

        // Web redirect
        return redirect('/login');
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ResponseHelper::error('User tidak ditemukan', 404);
            }

            // Handle _method for FormData
            if ($request->has('_method') && $request->input('_method') === 'PUT') {
                $request->merge(['_method' => null]);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8|confirmed',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'remove_avatar' => 'nullable|boolean',
            ]);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                // Store new avatar
                $avatarPath = $request->file('avatar')->store('users/avatars', 'public');
                $user->avatar = $avatarPath;
            } elseif ($request->input('remove_avatar')) {
                // Remove avatar if requested
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $user->avatar = null;
            }

            // Update user data
            $user->name = $validated['name'];
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }

            // Update password if provided
            if (!empty($validated['password'])) {
                $user->password = bcrypt($validated['password']);
            }

            $user->save();

            // Return updated user data (without password)
            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar,
            ];

            return ResponseHelper::success([
                'user' => $userData
            ], 'Profil berhasil diperbarui');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHelper::error('Validasi gagal', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Update profile error: ' . $e->getMessage());
            return ResponseHelper::error('Terjadi kesalahan saat memperbarui profil: ' . $e->getMessage(), 500);
        }
    }
}
