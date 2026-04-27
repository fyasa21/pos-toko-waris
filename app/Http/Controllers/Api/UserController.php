<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/users
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::when($request->role, fn($q) => $q->role($request->role))
            ->when($request->search, fn($q) =>
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%"))
            ->select('user_id', 'username', 'email', 'nama_lengkap', 'role', 'is_active', 'last_login_at', 'created_at')
            ->orderBy('nama_lengkap')
            ->paginate(20);

        return $this->paginatedResponse($users);
    }

    /**
     * POST /api/users
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username'     => 'required|string|max:50|unique:users',
            'email'        => 'required|email|max:100|unique:users',
            'password'     => 'required|string|min:6',
            'nama_lengkap' => 'required|string|max:100',
            'role'         => 'required|in:kasir,pemilik',
        ]);

        $user = User::create([
            'username'     => $validated['username'],
            'email'        => $validated['email'],
            'password_hash'=> Hash::make($validated['password']),
            'nama_lengkap' => $validated['nama_lengkap'],
            'role'         => $validated['role'],
            'is_active'    => true,
        ]);

        ActivityLog::record($request->user()->user_id, 'tambah_user', 'user',
            "User baru dibuat: {$user->username} ({$user->role})");

        return $this->createdResponse(
            $user->only('user_id', 'username', 'email', 'nama_lengkap', 'role', 'is_active'),
            'Pengguna berhasil dibuat.'
        );
    }

    /**
     * GET /api/users/{id}
     */
    public function show(int $id): JsonResponse
    {
        $user = User::select('user_id', 'username', 'email', 'nama_lengkap', 'role', 'is_active', 'last_login_at', 'created_at')
            ->find($id);
        if (!$user) return $this->notFoundResponse('Pengguna tidak ditemukan.');

        return $this->successResponse($user);
    }

    /**
     * PUT /api/users/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) return $this->notFoundResponse('Pengguna tidak ditemukan.');

        $validated = $request->validate([
            'username'     => 'sometimes|string|max:50|unique:users,username,' . $id . ',user_id',
            'email'        => 'sometimes|email|max:100|unique:users,email,' . $id . ',user_id',
            'nama_lengkap' => 'sometimes|string|max:100',
            'role'         => 'sometimes|in:kasir,pemilik',
            'is_active'    => 'sometimes|boolean',
        ]);

        $user->update($validated);
        ActivityLog::record($request->user()->user_id, 'update_user', 'user',
            "Update user: {$user->username}");

        return $this->successResponse(
            $user->only('user_id', 'username', 'email', 'nama_lengkap', 'role', 'is_active'),
            'Pengguna berhasil diperbarui.'
        );
    }

    /**
     * POST /api/users/{id}/reset-password
     */
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) return $this->notFoundResponse('Pengguna tidak ditemukan.');

        $validated = $request->validate([
            'password_baru' => 'required|string|min:6',
        ]);

        $user->update(['password_hash' => Hash::make($validated['password_baru'])]);
        $user->tokens()->delete(); // Hapus semua sesi aktif user tersebut

        ActivityLog::record($request->user()->user_id, 'reset_password', 'user',
            "Password user {$user->username} di-reset oleh pemilik.");

        return $this->successResponse(null, 'Password berhasil direset. User harus login ulang.');
    }

    /**
     * DELETE /api/users/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($id === $request->user()->user_id) {
            return $this->errorResponse('Anda tidak bisa menghapus akun sendiri.');
        }

        $user = User::find($id);
        if (!$user) return $this->notFoundResponse('Pengguna tidak ditemukan.');

        $user->tokens()->delete();
        $user->update(['is_active' => false]);

        ActivityLog::record($request->user()->user_id, 'hapus_user', 'user',
            "User dinonaktifkan: {$user->username}");

        return $this->successResponse(null, 'Pengguna berhasil dinonaktifkan.');
    }
}
