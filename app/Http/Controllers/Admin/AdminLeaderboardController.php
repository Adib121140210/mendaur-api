<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminLeaderboardController extends Controller
{
    /**
     * Get leaderboard
     * GET /api/admin/leaderboard?period=monthly&limit=100
     */
    public function index(Request $request)
    {
        try {
            $period = $request->query('period', 'monthly');
            $limit = min($request->query('limit', 100), 500);

            $leaderboard = [];

            // Get users ranked by display_poin (leaderboard ranking system)
            // Filter only nasabah (role_id = 1), exclude admin and superadmin
            $users = DB::table('users')
                ->select('user_id as userId', 'nama as userName', 'display_poin as totalPoints')
                ->where('status', 'active')
                ->where('role_id', 1)
                ->orderByDesc('totalPoints')
                ->orderBy('nama') // Secondary sort for same points
                ->limit($limit)
                ->get();

            $leaderboard = $users->map(function ($user, $index) {
                // Determine badge based on rank
                $badge = 'none';
                if ($index === 0 && $user->totalPoints > 0) {
                    $badge = 'gold';
                } elseif ($index === 1 && $user->totalPoints > 0) {
                    $badge = 'silver';
                } elseif ($index === 2 && $user->totalPoints > 0) {
                    $badge = 'bronze';
                }

                return [
                        'rank' => $index + 1,
                        'userId' => (int) $user->userId,
                        'userName' => $user->userName,
                        'points' => (int) $user->totalPoints,
                        'wasteSubmitted' => 0,
                        'avatar' => null,
                        'badge' => $badge,
                    ];
                })->values()->toArray();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'leaderboard' => $leaderboard,
                    'period' => $period,
                    'generatedAt' => Carbon::now()->toIso8601String(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch leaderboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leaderboard settings and current status
     * GET /api/admin/leaderboard/settings
     */
    public function getSettings(Request $request)
    {
        $settings = $this->getLeaderboardSettings();

        return response()->json([
            'status' => 'success',
            'data' => [
                'reset_period' => $settings['reset_period'],
                'auto_reset' => $settings['auto_reset'],
                'next_reset_date' => $this->calculateNextResetDate($settings['reset_period']),
                'last_reset_date' => $settings['last_reset_date'],
                'current_season' => $this->getCurrentSeason($settings['reset_period']),
            ]
        ]);
    }

    /**
     * Update leaderboard settings
     * PUT /api/admin/leaderboard/settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'reset_period' => 'required|in:weekly,monthly,quarterly,yearly',
            'auto_reset' => 'required|boolean',
        ]);

        $this->saveLeaderboardSettings($validated);

        // Log the action
        \App\Models\AuditLog::create([
            'admin_id' => $request->user()->user_id,
            'action_type' => 'update_leaderboard_settings',
            'resource_type' => 'Leaderboard',
            'resource_id' => 0, // Use 0 for system-wide actions
            'old_values' => null,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'success'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan leaderboard berhasil diperbarui',
            'data' => [
                'reset_period' => $validated['reset_period'],
                'auto_reset' => $validated['auto_reset'],
                'next_reset_date' => $this->calculateNextResetDate($validated['reset_period']),
            ]
        ]);
    }

    /**
     * Reset leaderboard manually
     * POST /api/admin/leaderboard/reset
     */
    public function resetLeaderboard(Request $request)
    {
        $request->validate([
            'confirm' => 'required|boolean|accepted',
        ]);

        DB::beginTransaction();
        try {
            // Get current top users before reset for history
            $topUsersBefore = User::where('status', 'active')
                ->orderBy('display_poin', 'desc')
                ->take(10)
                ->get(['user_id', 'nama', 'display_poin', 'level']);

            // Store snapshot for historical data
            $seasonData = [
                'season' => $this->getCurrentSeason($this->getLeaderboardSettings()['reset_period']),
                'reset_date' => now(),
                'top_users' => $topUsersBefore->toArray(),
                'total_participants' => User::where('status', 'active')->where('display_poin', '>', 0)->count(),
            ];

            // Reset leaderboard display points to 0 (keeping actual_poin intact)
            $affectedUsers = User::where('display_poin', '>', 0)->count();
            User::query()->update(['display_poin' => 0]);

            // Update last reset date
            $settings = $this->getLeaderboardSettings();
            $settings['last_reset_date'] = now()->toDateTimeString();
            $this->saveLeaderboardSettings($settings);

            // Log the action
            \App\Models\AuditLog::create([
                'admin_id' => $request->user()->user_id,
                'action_type' => 'reset_leaderboard',
                'resource_type' => 'Leaderboard',
                'resource_id' => 0, // Use 0 for system-wide actions
                'old_values' => ['top_users' => $topUsersBefore->toArray()],
                'new_values' => ['affected_users' => $affectedUsers, 'season_data' => $seasonData],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Leaderboard berhasil direset',
                'data' => [
                    'affected_users' => $affectedUsers,
                    'reset_date' => now()->toDateTimeString(),
                    'previous_top_users' => $topUsersBefore,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Leaderboard reset failed:', [
                'admin_id' => $request->user()->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mereset leaderboard: ' . $e->getMessage()
            ], 500);
        }
    }    /**
     * Get leaderboard history/seasons
     * GET /api/admin/leaderboard/history
     */
    public function getHistory(Request $request)
    {
        $history = \App\Models\AuditLog::where('action_type', 'reset_leaderboard')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $seasons = $history->map(function ($log) {
            $newValues = is_string($log->new_values) ? json_decode($log->new_values, true) : $log->new_values;
            $oldValues = is_string($log->old_values) ? json_decode($log->old_values, true) : $log->old_values;

            return [
                'reset_date' => $log->created_at,
                'admin_id' => $log->admin_id,
                'affected_users' => $newValues['affected_users'] ?? 0,
                'season_data' => $newValues['season_data'] ?? null,
                'previous_top_users' => $oldValues['top_users'] ?? [],
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $seasons
        ]);
    }

    // ==================== Helper Methods ====================

    private function getLeaderboardSettings(): array
    {
        $settingsPath = storage_path('app/leaderboard_settings.json');

        if (file_exists($settingsPath)) {
            return json_decode(file_get_contents($settingsPath), true);
        }

        return [
            'reset_period' => 'monthly',
            'auto_reset' => false,
            'last_reset_date' => null,
        ];
    }

    private function saveLeaderboardSettings(array $settings): void
    {
        $settingsPath = storage_path('app/leaderboard_settings.json');
        $currentSettings = $this->getLeaderboardSettings();
        $mergedSettings = array_merge($currentSettings, $settings);

        file_put_contents($settingsPath, json_encode($mergedSettings, JSON_PRETTY_PRINT));
    }

    private function calculateNextResetDate(string $period): string
    {
        $now = Carbon::now();

        switch ($period) {
            case 'weekly':
                return $now->next(Carbon::MONDAY)->format('Y-m-d');
            case 'monthly':
                return $now->copy()->startOfMonth()->addMonth()->format('Y-m-d');
            case 'quarterly':
                $currentQuarter = $now->quarter;
                return $now->copy()->quarter($currentQuarter + 1)->startOfQuarter()->format('Y-m-d');
            case 'yearly':
                return $now->copy()->startOfYear()->addYear()->format('Y-m-d');
            default:
                return $now->copy()->startOfMonth()->addMonth()->format('Y-m-d');
        }
    }

    private function getCurrentSeason(string $period): string
    {
        $now = Carbon::now();

        switch ($period) {
            case 'weekly':
                return "Week " . $now->weekOfYear . " - " . $now->year;
            case 'monthly':
                return $now->format('F Y');
            case 'quarterly':
                return "Q" . $now->quarter . " " . $now->year;
            case 'yearly':
                return "Year " . $now->year;
            default:
                return $now->format('F Y');
        }
    }
}
