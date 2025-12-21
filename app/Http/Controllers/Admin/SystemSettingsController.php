<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemSettingsController extends Controller
{
    /**
     * Get all system settings
     */
    public function index(Request $request)
    {
        // Verify superadmin role
        if (!$request->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Superadmin role required',
            ], 403);
        }

        try {
            $settings = Cache::remember('system_settings', 3600, function () {
                return DB::table('system_settings')->get();
            });

            return response()->json([
                'success' => true,
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil system settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific setting
     */
    public function show($key, Request $request)
    {
        // Verify superadmin role
        if (!$request->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Superadmin role required',
            ], 403);
        }

        try {
            $setting = DB::table('system_settings')->where('kunci', $key)->first();

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $setting,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil setting',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update system setting
     */
    public function update($key, Request $request)
    {
        // Verify superadmin role
        if (!$request->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Superadmin role required',
            ], 403);
        }

        $validated = $request->validate([
            'nilai' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $setting = DB::table('system_settings')->where('kunci', $key)->first();

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting tidak ditemukan',
                ], 404);
            }

            DB::table('system_settings')
                ->where('kunci', $key)
                ->update([
                    'nilai' => $validated['nilai'],
                    'deskripsi' => $validated['deskripsi'] ?? $setting->deskripsi,
                    'updated_at' => now(),
                ]);

            // Invalidate cache
            Cache::forget('system_settings');

            return response()->json([
                'success' => true,
                'message' => 'Setting berhasil diupdate',
                'data' => [
                    'kunci' => $key,
                    'nilai' => $validated['nilai'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate setting',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system health/stats
     */
    public function health(Request $request)
    {
        // Verify superadmin role
        if (!$request->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Superadmin role required',
            ], 403);
        }

        try {
            // Get database stats
            $dbStats = [
                'users_count' => DB::table('users')->count(),
                'active_tokens' => DB::table('personal_access_tokens')->whereNull('revoked_at')->count(),
                'pending_deposits' => DB::table('tabung_sampah')->where('status', 'menunggu')->count(),
                'pending_exchanges' => DB::table('penukaran_produk')->where('status', 'menunggu')->count(),
                'pending_withdrawals' => DB::table('penarikan_tunai')->where('status', 'menunggu')->count(),
            ];

            // Get API stats (last 24 hours)
            $apiStats = [
                'total_requests' => DB::table('audit_logs')->where('created_at', '>=', now()->subDay())->count(),
                'errors' => DB::table('system_logs')->where('created_at', '>=', now()->subDay())->where('level', 'error')->count(),
            ];

            // Get disk usage (approximate)
            $diskStats = [
                'free_space_gb' => disk_free_space('/') / (1024 ** 3),
                'total_space_gb' => disk_total_space('/') / (1024 ** 3),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => 'operational',
                    'timestamp' => now(),
                    'database' => $dbStats,
                    'api' => $apiStats,
                    'disk' => $diskStats,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil system health',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache statistics
     */
    public function cacheStats(Request $request)
    {
        // Verify superadmin role
        if (!$request->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Superadmin role required',
            ], 403);
        }

        try {
            $stats = [
                'driver' => config('cache.default'),
                'cached_settings' => Cache::has('system_settings') ? true : false,
                'cache_items' => 0, // This would depend on your cache driver implementation
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil cache statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(Request $request)
    {
        // Verify superadmin role
        if (!$request->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Superadmin role required',
            ], 403);
        }

        try {
            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Cache berhasil dibersihkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get database statistics
     */
    public function databaseStats(Request $request)
    {
        // Verify superadmin role
        if (!$request->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Superadmin role required',
            ], 403);
        }

        try {
            $stats = [
                'users' => DB::table('users')->count(),
                'waste_deposits' => DB::table('tabung_sampah')->count(),
                'product_exchanges' => DB::table('penukaran_produk')->count(),
                'cash_withdrawals' => DB::table('penarikan_tunai')->count(),
                'badges' => DB::table('badges')->count(),
                'articles' => DB::table('artikel')->count(),
                'products' => DB::table('produk')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil database statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
