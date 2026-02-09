<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        try {
            $query = User::query();

            // Search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Role filter
            if ($request->has('role') && $request->role) {
                $query->where('role', $request->role);
            }

            // Status filter
            if ($request->has('isActive') && $request->isActive !== null) {
                $query->where('isActive', $request->isActive);
            }

            $users = $query->orderBy('created_at', 'desc')->get();

            // Format response
            $formattedUsers = $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_label' => $this->getRoleLabel($user->role),
                    'isActive' => $user->isActive,
                    'isActive_badge' => $user->isActive 
                        ? '<span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-300 rounded-full">Aktif</span>'
                        : '<span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-300 rounded-full">Nonaktif</span>',
                    'avatar' => $user->avatar,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            return ResponseHelper::success($formattedUsers);
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal memuat data pengguna: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'username' => 'required|string|max:100|unique:users,username',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'role' => 'required|integer|in:1,2,3',
                'isActive' => 'nullable|boolean',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            ]);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('users/avatars', 'public');
                $validated['avatar'] = $avatarPath;
            }

            // Hash password
            $validated['password'] = Hash::make($validated['password']);
            $validated['isActive'] = $validated['isActive'] ?? 1;

            $user = User::create($validated);

            return ResponseHelper::success([
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => $this->getRoleLabel($user->role),
                'isActive' => $user->isActive,
                'avatar' => $user->avatar,
            ], 'Pengguna berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHelper::error('Validasi gagal', 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal menambahkan pengguna: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        try {
            $user = User::findOrFail($id);
            
            return ResponseHelper::success([
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => $this->getRoleLabel($user->role),
                'isActive' => $user->isActive,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('Pengguna tidak ditemukan', 404);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'username' => ['required', 'string', 'max:100', Rule::unique('users')->ignore($id)],
                'name' => 'required|string|max:255',
                'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($id)],
                'password' => 'nullable|string|min:8',
                'role' => 'required|integer|in:1,2,3',
                'isActive' => 'nullable|boolean',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'remove_avatar' => 'nullable|boolean',
            ]);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $avatarPath = $request->file('avatar')->store('users/avatars', 'public');
                $validated['avatar'] = $avatarPath;
            } elseif ($request->input('remove_avatar')) {
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $validated['avatar'] = null;
            }

            // Hash password if provided
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            return ResponseHelper::success([
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => $this->getRoleLabel($user->role),
                'isActive' => $user->isActive,
                'avatar' => $user->avatar,
            ], 'Pengguna berhasil diperbarui');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHelper::error('Validasi gagal', 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal memperbarui pengguna: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                return ResponseHelper::error('Anda tidak dapat menghapus akun sendiri', 403);
            }

            // Delete avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();

            return ResponseHelper::success(null, 'Pengguna berhasil dihapus');
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal menghapus pengguna: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Toggle active status of user.
     */
    public function toggleActive(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deactivating yourself
            if ($user->id === auth()->id()) {
                return ResponseHelper::error('Anda tidak dapat menonaktifkan akun sendiri', 403);
            }

            $user->isActive = $user->isActive ? 0 : 1;
            $user->save();

            return ResponseHelper::success([
                'id' => $user->id,
                'isActive' => $user->isActive,
                'isActive_badge' => $user->isActive 
                    ? '<span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-300 rounded-full">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-300 rounded-full">Nonaktif</span>',
            ], $user->isActive ? 'Pengguna berhasil diaktifkan' : 'Pengguna berhasil dinonaktifkan');
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal mengubah status pengguna: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get role label.
     */
    private function getRoleLabel(int $role): string
    {
        return match($role) {
            1 => 'Admin',
            2 => 'Operator',
            3 => 'Kepala Sekolah',
            default => 'Unknown',
        };
    }
}
