<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TabungSampah;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Get user profile by ID
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'no_hp' => 'sometimes|string|max:20',
            'alamat' => 'sometimes|string',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only(['nama', 'email', 'no_hp', 'alamat']));

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * Update user profile photo
     */
    public function updatePhoto(Request $request, $id)
    {
        $request->validate([
            'foto_profil' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        $user = User::findOrFail($id);

        // Delete old photo if exists
        if ($user->foto_profil) {
            Storage::disk('public')->delete($user->foto_profil);
        }

        // Store new photo
        $path = $request->file('foto_profil')->store('uploads/profiles', 'public');
        $user->update(['foto_profil' => $path]);

        return response()->json([
            'status' => 'success',
            'message' => 'Photo updated successfully',
            'data' => [
                'foto_profil' => $path,
                'foto_url' => asset('storage/' . $path),
            ],
        ]);
    }

    /**
     * Get user's tabung sampah history
     */
    public function tabungSampahHistory($id)
    {
        $history = TabungSampah::where('user_id', $id)
            ->with('jadwal')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history,
        ]);
    }

    /**
     * Get user's badges/achievements
     */
    public function badges($id)
    {
        $user = User::findOrFail($id);

        // Get badges through the many-to-many relationship
        $badges = $user->badges()
            ->withPivot('tanggal_dapat')
            ->orderBy('user_badges.tanggal_dapat', 'desc')
            ->get()
            ->map(function($badge) {
                return [
                    'id' => $badge->id,
                    'nama' => $badge->nama,
                    'deskripsi' => $badge->deskripsi,
                    'icon' => $badge->icon,
                    'syarat_poin' => $badge->syarat_poin,
                    'syarat_setor' => $badge->syarat_setor,
                    'tipe' => $badge->tipe,
                    'tanggal_dapat' => $badge->pivot->tanggal_dapat,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $badges,
        ]);
    }

    /**
     * Get user's activity log
     *
     * Query Parameters:
     * - limit: number of activities (default 20, max 100)
     */
    public function aktivitas(Request $request, $id)
    {
        // Verify user exists
        User::findOrFail($id);

        $limit = min($request->query('limit', 20), 100); // Max 100 activities

        $aktivitas = DB::table('log_aktivitas')
            ->where('user_id', $id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($activity) {
                return [
                    'id' => $activity->id,
                    'tipe_aktivitas' => $activity->tipe_aktivitas,
                    'deskripsi' => $activity->deskripsi,
                    'poin_perubahan' => (int) $activity->poin_perubahan,
                    'tanggal' => $activity->tanggal,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $aktivitas,
        ]);
    }
}
