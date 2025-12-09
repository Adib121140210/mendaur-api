<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TabungSampah;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for a user
     */
    public function getUserStats($userId)
    {
        $user = User::findOrFail($userId);

        // Get user's rank
        $rank = User::where('total_poin', '>', $user->total_poin)->count() + 1;
        $totalUsers = User::count();

        // Calculate progress to next level
        $levelThresholds = [
            'Pemula' => ['min' => 0, 'max' => 100],
            'Bronze' => ['min' => 100, 'max' => 300],
            'Silver' => ['min' => 300, 'max' => 600],
            'Gold' => ['min' => 600, 'max' => 1000],
            'Platinum' => ['min' => 1000, 'max' => PHP_INT_MAX],
        ];

        $currentLevel = $user->level;
        $currentPoin = $user->total_poin;
        $nextLevel = $this->getNextLevel($currentLevel);
        $nextLevelPoin = $levelThresholds[$nextLevel]['min'] ?? $currentPoin;
        $currentLevelPoin = $levelThresholds[$currentLevel]['min'];

        $progress = 0;
        if ($nextLevelPoin > $currentLevelPoin) {
            $progress = (($currentPoin - $currentLevelPoin) / ($nextLevelPoin - $currentLevelPoin)) * 100;
            $progress = min(100, max(0, $progress)); // Clamp between 0 and 100
        }

        // Get recent activity
        $recentDeposits = TabungSampah::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get monthly stats
        $monthlyPoin = TabungSampah::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->sum('poin_didapat');

        $monthlySetor = TabungSampah::where('user_id', $userId)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'foto_profil' => $user->foto_profil,
                    'total_poin' => $user->total_poin,
                    'total_setor_sampah' => $user->total_setor_sampah,
                    'level' => $user->level,
                ],
                'statistics' => [
                    'rank' => $rank,
                    'total_users' => $totalUsers,
                    'monthly_poin' => $monthlyPoin,
                    'monthly_setor' => $monthlySetor,
                    'next_level' => $nextLevel,
                    'progress_to_next_level' => round($progress, 2),
                    'poin_needed' => max(0, $nextLevelPoin - $currentPoin),
                ],
                'recent_deposits' => $recentDeposits,
            ],
        ]);
    }

    /**
     * Get leaderboard (top users by poin, setor, or badge)
     *
     * Query Parameters:
     * - limit: number of users (default 10, max 50)
     * - type: 'poin' | 'setor' | 'badge' (default 'poin')
     */
    public function getLeaderboard(Request $request)
    {
        $limit = min($request->query('limit', 10), 50); // Max 50 users
        $type = $request->query('type', 'poin'); // Default: poin

        // Validate type
        if (!in_array($type, ['poin', 'setor', 'badge'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid type. Must be: poin, setor, or badge',
            ], 400);
        }

        // Build query with badge count
        $query = User::select(
                'users.id',
                'users.nama',
                'users.foto_profil',
                'users.total_poin',
                'users.total_setor_sampah',
                'users.level'
            )
            ->selectRaw('COALESCE(COUNT(user_badges.id), 0) as badge_count')
            ->leftJoin('user_badges', 'users.id', '=', 'user_badges.user_id')
            ->groupBy(
                'users.id',
                'users.nama',
                'users.foto_profil',
                'users.total_poin',
                'users.total_setor_sampah',
                'users.level'
            );

        // Order by type
        switch ($type) {
            case 'poin':
                $query->orderBy('users.total_poin', 'desc');
                break;
            case 'setor':
                $query->orderBy('users.total_setor_sampah', 'desc');
                break;
            case 'badge':
                $query->orderByRaw('COUNT(user_badges.id) DESC');
                break;
        }

        // Get users
        $leaderboard = $query->limit($limit)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'user_id' => $user->id,
                    'nama' => $user->nama,
                    'foto_profil' => $user->foto_profil,
                    'total_poin' => $user->total_poin,
                    'total_setor_sampah' => $user->total_setor_sampah,
                    'level' => $user->level,
                    'badge_count' => (int) $user->badge_count,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $leaderboard,
        ]);
    }

    /**
     * Get global statistics
     */
    public function getGlobalStats()
    {
        $totalUsers = User::count();
        $totalPoin = User::sum('total_poin');
        $totalSetor = TabungSampah::where('status', 'approved')->count();
        $totalBerat = TabungSampah::where('status', 'approved')->sum('berat_kg');

        // Get monthly growth
        $thisMonth = TabungSampah::whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();

        $lastMonth = TabungSampah::whereMonth('created_at', date('m', strtotime('-1 month')))
            ->whereYear('created_at', date('Y', strtotime('-1 month')))
            ->count();

        $growth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_users' => $totalUsers,
                'total_poin_distributed' => $totalPoin,
                'total_deposits' => $totalSetor,
                'total_weight_kg' => round($totalBerat, 2),
                'monthly_growth' => round($growth, 2),
                'this_month_deposits' => $thisMonth,
            ],
        ]);
    }

    /**
     * Get helper function for next level
     */
    private function getNextLevel($currentLevel)
    {
        $levels = ['Pemula', 'Bronze', 'Silver', 'Gold', 'Platinum'];
        $currentIndex = array_search($currentLevel, $levels);

        if ($currentIndex === false || $currentIndex >= count($levels) - 1) {
            return $currentLevel; // Already at max level
        }

        return $levels[$currentIndex + 1];
    }
}
