<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find user by email with role and permissions loaded
        $user = User::with('role.permissions')
            ->where('email', $request->email)
            ->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah',
            ], 401);
        }

        // Create Sanctum authentication token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Build role object with permissions
        $roleData = null;
        $permissions = [];

        if ($user->role) {
            $permissions = $user->role->permissions
                ? $user->role->permissions->pluck('permission')->toArray()
                : [];
            $roleData = [
                'id' => $user->role->id,
                'nama_role' => $user->role->nama_role,
                'level_akses' => $user->role->level_akses,
                'permissions' => $permissions,
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'no_hp' => $user->no_hp,
                    'alamat' => $user->alamat,
                    'foto_profil' => $user->foto_profil,
                    'total_poin' => $user->total_poin,
                    'total_setor_sampah' => $user->total_setor_sampah,
                    'level' => $user->level,
                    'role_id' => $user->role_id,
                    'role' => $roleData,
                    'permissions' => $permissions,
                ],
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'no_hp' => 'required|string',
            'alamat' => 'nullable|string',
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'total_poin' => 0,
            'total_setor_sampah' => 0,
            'level' => 'Pemula',
            'role_id' => 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'level' => $user->level,
                ],
            ],
        ], 201);
    }

    /**
     * Logout user (revoke current access token)
     */
    public function logout(Request $request)
    {
        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil',
        ], 200);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        // This requires authentication middleware
        $user = $request->user();

        // Refresh user data from database to get latest points
        $user->refresh();

        // Load role and permissions
        $user->load('role.permissions');

        // Build role object with permissions
        $roleData = null;
        $permissions = [];

        if ($user->role) {
            $permissions = $user->role->permissions
                ? $user->role->permissions->pluck('permission')->toArray()
                : [];
            $roleData = [
                'id' => $user->role->id,
                'nama_role' => $user->role->nama_role,
                'level_akses' => $user->role->level_akses,
                'permissions' => $permissions,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'no_hp' => $user->no_hp,
                    'alamat' => $user->alamat,
                    'foto_profil' => $user->foto_profil,
                    'total_poin' => $user->total_poin,
                    'total_setor_sampah' => $user->total_setor_sampah,
                    'level' => $user->level,
                    'role_id' => $user->role_id,
                    'role' => $roleData,
                    'permissions' => $permissions,
                ],
            ],
        ], 200);
    }
}
