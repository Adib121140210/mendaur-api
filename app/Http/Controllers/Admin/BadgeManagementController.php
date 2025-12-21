<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BadgeManagementController extends Controller
{
    /**
     * List all badges
     */
    public function index(Request $request)
    {
        // Verify admin or superadmin role
        if (!$request->user()?->isAdminUser()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Admin role required',
            ], 403);
        }

        try {
            $badges = Badge::with('users')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $badges,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data badges',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show specific badge
     */
    public function show($badgeId, Request $request)
    {
        // Verify admin or superadmin role
        if (!$request->user()?->isAdminUser()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Admin role required',
            ], 403);
        }

        try {
            $badge = Badge::with(['users', 'criteria'])
                ->findOrFail($badgeId);

            return response()->json([
                'success' => true,
                'data' => $badge,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Badge tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new badge
     */
    public function store(Request $request)
    {
        // Verify superadmin role
        if (!$request->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Superadmin role required',
            ], 403);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:badges,nama',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,jpg,png|max:1024',
            'tipe' => 'required|in:automatic,manual',
            'kondisi_pencapaian' => 'nullable|string',
            'poin_reward' => 'nullable|integer|min:0',
        ]);

        try {
            // Handle icon upload
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads/badges', $filename, 'public');
                $validated['icon'] = $path;
            }

            $badge = Badge::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Badge berhasil dibuat',
                'data' => $badge,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat badge',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update badge
     */
    public function update($badgeId, Request $request)
    {
        // Verify superadmin role
        if (!$request->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Superadmin role required',
            ], 403);
        }

        try {
            $badge = Badge::findOrFail($badgeId);

            $validated = $request->validate([
                'nama' => ['nullable', 'string', 'max:100', Rule::unique('badges')->ignore($badgeId)],
                'deskripsi' => 'nullable|string',
                'icon' => 'nullable|image|mimes:jpeg,jpg,png|max:1024',
                'tipe' => 'nullable|in:automatic,manual',
                'kondisi_pencapaian' => 'nullable|string',
                'poin_reward' => 'nullable|integer|min:0',
            ]);

            // Handle icon upload
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads/badges', $filename, 'public');
                $validated['icon'] = $path;
            }

            $badge->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Badge berhasil diupdate',
                'data' => $badge,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate badge',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete badge
     */
    public function destroy($badgeId, Request $request)
    {
        // Verify superadmin role
        if (!$request->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Superadmin role required',
            ], 403);
        }

        try {
            $badge = Badge::findOrFail($badgeId);
            $badge->delete();

            return response()->json([
                'success' => true,
                'message' => 'Badge berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus badge',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign badge to user (manual assignment)
     */
    public function assignToUser($badgeId, Request $request)
    {
        // Verify admin or superadmin role
        if (!$request->user()?->isAdminUser()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Admin role required',
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $badge = Badge::findOrFail($badgeId);
            $user = User::findOrFail($validated['user_id']);

            // Check if user already has this badge
            if ($user->badges()->where('badge_id', $badgeId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User sudah memiliki badge ini',
                ], 409);
            }

            $user->badges()->attach($badgeId);

            return response()->json([
                'success' => true,
                'message' => 'Badge berhasil di-assign ke user',
                'data' => [
                    'badge' => $badge,
                    'user' => $user,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal assign badge',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke badge from user
     */
    public function revokeFromUser($badgeId, Request $request)
    {
        // Verify admin or superadmin role
        if (!$request->user()?->isAdminUser()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Admin role required',
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $badge = Badge::findOrFail($badgeId);
            $user = User::findOrFail($validated['user_id']);

            $user->badges()->detach($badgeId);

            return response()->json([
                'success' => true,
                'message' => 'Badge berhasil di-revoke dari user',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal revoke badge',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users with specific badge
     */
    public function getUsersWithBadge($badgeId, Request $request)
    {
        // Verify admin or superadmin role
        if (!$request->user()?->isAdminUser()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Admin role required',
            ], 403);
        }

        try {
            $badge = Badge::findOrFail($badgeId);

            $users = $badge->users()
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil users dengan badge',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
