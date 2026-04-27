<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * POST /api/auth/login
     * Mendukung login via username atau email, dengan validasi role.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role'     => 'required|in:kasir,pemilik',
        ]);

        // Cari user berdasarkan username atau email
        $user = User::where(function ($q) use ($request) {
                $q->where('username', $request->username)
                  ->orWhere('email', $request->username);
            })
            ->where('role', $request->role)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            ActivityLog::record(null, 'login_gagal', 'auth', "Login gagal untuk: {$request->username}");
            throw ValidationException::withMessages([
                'username' => ['Username/email atau password salah, atau role tidak sesuai.'],
            ]);
        }

        if (!$user->is_active) {
            return $this->errorResponse('Akun Anda dinonaktifkan. Hubungi pemilik toko.', 403);
        }

        // Hapus token lama untuk mencegah duplikasi sesi
        $user->tokens()->delete();

        $tokenName  = "pos_{$user->role}_{$user->user_id}";
        $abilities  = $user->isPemilik()
            ? ['*']
            : ['transaksi:*', 'stok:read', 'produk:read'];

        $token = $user->createToken($tokenName, $abilities)->plainTextToken;

        $user->update(['last_login_at' => now()]);

        ActivityLog::record($user->user_id, 'login', 'auth', "Login berhasil sebagai {$user->role}");

        return $this->successResponse([
            'token'       => $token,
            'token_type'  => 'Bearer',
            'user'        => [
                'user_id'      => $user->user_id,
                'username'     => $user->username,
                'nama_lengkap' => $user->nama_lengkap,
                'email'        => $user->email,
                'role'         => $user->role,
                'last_login'   => $user->last_login_at,
            ],
        ], 'Login berhasil.');
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();

        ActivityLog::record($user->user_id, 'logout', 'auth', 'Logout berhasil.');

        return $this->successResponse(null, 'Logout berhasil.');
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return $this->successResponse([
            'user_id'      => $user->user_id,
            'username'     => $user->username,
            'nama_lengkap' => $user->nama_lengkap,
            'email'        => $user->email,
            'role'         => $user->role,
            'is_active'    => $user->is_active,
            'last_login'   => $user->last_login_at,
            'created_at'   => $user->created_at,
        ]);
    }

    /**
     * PUT /api/auth/profile
     * Update nama, username, email sendiri.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|string|max:100',
            'username'     => 'sometimes|string|max:50|unique:users,username,' . $user->user_id . ',user_id',
            'email'        => 'sometimes|email|max:100|unique:users,email,' . $user->user_id . ',user_id',
        ]);

        $user->update($validated);

        ActivityLog::record($user->user_id, 'update_profile', 'user', 'Update profil berhasil.');

        return $this->successResponse($user->only('user_id', 'username', 'nama_lengkap', 'email', 'role'), 'Profil berhasil diperbarui.');
    }

    /**
     * PUT /api/auth/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password_lama, $user->password_hash)) {
            throw ValidationException::withMessages([
                'password_lama' => ['Password lama tidak sesuai.'],
            ]);
        }

        $user->update(['password_hash' => Hash::make($request->password_baru)]);
        $user->tokens()->delete(); // Force re-login setelah ganti password

        ActivityLog::record($user->user_id, 'change_password', 'user', 'Password berhasil diubah. Semua sesi dihapus.');

        return $this->successResponse(null, 'Password berhasil diubah. Silakan login kembali.');
    }
}
