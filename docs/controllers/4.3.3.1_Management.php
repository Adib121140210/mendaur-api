<?php
/**
 * ============================================================================
 * 4.3.3.1 MANAGEMENT CONTROLLERS
 * ============================================================================
 * Dokumentasi Controller untuk Laporan Tugas Akhir/Skripsi
 * Aplikasi: Bank Sampah Digital (Mendaur)
 * ============================================================================
 */

// =============================================================================
// 1. USER MANAGEMENT CONTROLLER
// =============================================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Menampilkan daftar semua user dengan filter dan pagination
     * GET /api/admin/users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter berdasarkan role (user/admin/superadmin)
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter berdasarkan status (active/inactive)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Pencarian berdasarkan nama atau email
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * Menampilkan detail user
     * GET /api/admin/users/{userId}
     */
    public function show($userId)
    {
        $user = User::with(['tabungSampah', 'penukaranProduk', 'badges'])
            ->findOrFail($userId);

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    /**
     * Membuat user baru
     * POST /api/admin/users
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:user,admin,superadmin',
            'no_hp' => 'nullable|string',
            'alamat' => 'nullable|string',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil dibuat',
            'data' => $user
        ], 201);
    }

    /**
     * Mengupdate data user
     * PUT /api/admin/users/{userId}
     */
    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId . ',user_id',
            'no_hp' => 'nullable|string',
            'alamat' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil diupdate',
            'data' => $user
        ]);
    }

    /**
     * Mengubah status user (active/inactive)
     * PATCH /api/admin/users/{userId}/status
     */
    public function updateStatus(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'status' => 'required|in:active,inactive'
        ]);

        $user->update(['status' => $validated['status']]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status user berhasil diubah'
        ]);
    }

    /**
     * Menghapus user
     * DELETE /api/admin/users/{userId}
     */
    public function destroy($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil dihapus'
        ]);
    }
}

// =============================================================================
// 2. LEADERBOARD MANAGEMENT CONTROLLER
// =============================================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminLeaderboardController extends Controller
{
    /**
     * Menampilkan leaderboard dengan ranking
     * GET /api/admin/leaderboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'all'); // all, monthly, weekly

        $query = User::where('role', 'user')
            ->where('status', 'active')
            ->orderBy('display_poin', 'desc');

        // Filter berdasarkan periode
        if ($period === 'monthly') {
            $query->whereMonth('updated_at', now()->month);
        } elseif ($period === 'weekly') {
            $query->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        $leaderboard = $query->take(100)->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'user_id' => $user->user_id,
                    'nama' => $user->nama,
                    'display_poin' => $user->display_poin,
                    'total_sampah_kg' => $user->total_sampah_kg,
                    'badge_count' => $user->badges()->count(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $leaderboard
        ]);
    }

    /**
     * Menampilkan overview statistik leaderboard
     * GET /api/admin/leaderboard/overview
     */
    public function overview()
    {
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'active_users' => User::where('role', 'user')->where('status', 'active')->count(),
            'total_poin_distributed' => User::sum('display_poin'),
            'total_sampah_kg' => DB::table('tabung_sampah')
                ->where('status', 'approved')
                ->sum('berat_kg'),
            'top_user' => User::where('role', 'user')
                ->orderBy('display_poin', 'desc')
                ->first(['user_id', 'nama', 'display_poin']),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Reset leaderboard (periode baru)
     * POST /api/admin/leaderboard/reset
     */
    public function resetLeaderboard(Request $request)
    {
        // Simpan snapshot leaderboard sebelum reset
        $topUsers = User::where('role', 'user')
            ->orderBy('display_poin', 'desc')
            ->take(10)
            ->get();

        DB::table('leaderboard_history')->insert([
            'period' => now()->format('Y-m'),
            'snapshot' => json_encode($topUsers),
            'created_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Snapshot leaderboard berhasil disimpan'
        ]);
    }
}

// =============================================================================
// 3. NOTIFICATION MANAGEMENT CONTROLLER
// =============================================================================

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Menampilkan semua notifikasi user yang login
     * GET /api/notifications
     */
    public function index(Request $request)
    {
        $notifications = Notifikasi::where('user_id', $request->user()->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    /**
     * Menampilkan notifikasi yang belum dibaca
     * GET /api/notifications/unread
     */
    public function unread(Request $request)
    {
        $notifications = Notifikasi::where('user_id', $request->user()->user_id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    /**
     * Menghitung jumlah notifikasi belum dibaca
     * GET /api/notifications/unread-count
     */
    public function unreadCount(Request $request)
    {
        $count = Notifikasi::where('user_id', $request->user()->user_id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => ['unread_count' => $count]
        ]);
    }

    /**
     * Menandai notifikasi sebagai sudah dibaca
     * PATCH /api/notifications/{id}/read
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $notification = Notifikasi::where('notifikasi_id', $notificationId)
            ->where('user_id', $request->user()->user_id)
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi ditandai sudah dibaca'
        ]);
    }

    /**
     * Menandai semua notifikasi sebagai sudah dibaca
     * PATCH /api/notifications/mark-all-read
     */
    public function markAllAsRead(Request $request)
    {
        Notifikasi::where('user_id', $request->user()->user_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Semua notifikasi ditandai sudah dibaca'
        ]);
    }

    /**
     * Membuat notifikasi baru (Admin only)
     * POST /api/admin/notifications
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'judul' => 'required|string|max:255',
            'pesan' => 'required|string',
            'tipe' => 'nullable|in:info,success,warning',
            'related_id' => 'nullable|integer',
            'related_type' => 'nullable|string',
        ]);

        $notification = Notifikasi::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dibuat',
            'data' => $notification
        ], 201);
    }

    /**
     * Menghapus notifikasi
     * DELETE /api/notifications/{id}
     */
    public function destroy(Request $request, $notificationId)
    {
        $notification = Notifikasi::where('notifikasi_id', $notificationId)
            ->where('user_id', $request->user()->user_id)
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi berhasil dihapus'
        ]);
    }
}
