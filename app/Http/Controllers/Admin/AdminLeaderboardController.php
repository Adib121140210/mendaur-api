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

            // Check if poin_transaksis table exists to get user rankings
            $hasPoinTable = DB::select("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                [DB::connection()->getDatabaseName(), 'poin_transaksis']);

            if (!empty($hasPoinTable)) {
                // Get users ranked by points if poin_transaksis table exists
                $users = DB::table('poin_transaksis')
                    ->join('users', 'poin_transaksis.user_id', '=', 'users.id')
                    ->select('users.id as userId', 'users.login as userName', DB::raw('SUM(poin_transaksis.poin_didapat) as totalPoints'))
                    ->groupBy('users.id', 'users.login')
                    ->orderByDesc('totalPoints')
                    ->limit($limit)
                    ->get();

                $leaderboard = $users->map(function ($user, $index) {
                    // Determine badge based on rank
                    $badge = 'none';
                    if ($index === 0) {
                        $badge = 'gold';
                    } elseif ($index === 1) {
                        $badge = 'silver';
                    } elseif ($index === 2) {
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
            } else {
                // No points table - return simple user list ordered by join date
                $users = DB::table('users')
                    ->orderByDesc('created_at')
                    ->limit($limit)
                    ->get();

                $leaderboard = $users->map(function ($user, $index) {
                    $badge = 'none';
                    if ($index === 0) {
                        $badge = 'gold';
                    } elseif ($index === 1) {
                        $badge = 'silver';
                    } elseif ($index === 2) {
                        $badge = 'bronze';
                    }

                    return [
                        'rank' => $index + 1,
                        'userId' => (int) $user->id,
                        'userName' => $user->login,
                        'points' => 0,
                        'wasteSubmitted' => 0,
                        'avatar' => null,
                        'badge' => $badge,
                    ];
                })->values()->toArray();
            }

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
}
