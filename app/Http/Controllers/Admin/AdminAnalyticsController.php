<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TabungSampah;
use App\Models\User;
use App\Models\PoinTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAnalyticsController extends Controller
{
    /**
     * Get waste analytics
     * GET /api/admin/analytics/waste?period=monthly&category=&month=2025-01
     */
    public function waste(Request $request)
    {
        try {
            $period = $request->query('period', 'monthly');
            $category = $request->query('category', null);
            $month = $request->query('month', null);

            // Check if tabung_sampah table exists
            $hasSampahTable = DB::select("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                [DB::connection()->getDatabaseName(), 'tabung_sampah']);

            $totalWaste = 0;
            $byCategory = [];
            $monthlyTrend = [];

            if (!empty($hasSampahTable)) {
                // Total waste
                $totalWaste = DB::table('tabung_sampah')->sum('berat_kg') ?? 0;

                // Waste by category
                $byCategory = DB::table('tabung_sampah')
                    ->select('jenis_sampah', DB::raw('SUM(berat_kg) as quantity'))
                    ->groupBy('jenis_sampah')
                    ->orderByDesc('quantity')
                    ->get()
                    ->map(function ($item) use ($totalWaste) {
                        $quantity = (float) $item->quantity;
                        return [
                            'wasteCategory' => ucfirst($item->jenis_sampah),
                            'quantity' => $quantity,
                            'percentage' => $totalWaste > 0 ? round(($quantity / $totalWaste * 100), 2) : 0,
                            'trend' => 'stable',
                        ];
                    })
                    ->values()
                    ->toArray();

                // Monthly trend (last 12 months)
                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $monthStart = $date->copy()->startOfMonth();
                    $monthEnd = $date->copy()->endOfMonth();

                    $quantity = DB::table('tabung_sampah')
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->sum('berat_kg') ?? 0;

                    $monthlyTrend[] = [
                        'month' => $date->format('Y-m'),
                        'quantity' => (float) $quantity,
                    ];
                }
            } else {
                // Table doesn't exist - generate empty monthly trend for last 12 months
                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $monthlyTrend[] = [
                        'month' => $date->format('Y-m'),
                        'quantity' => 0,
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'totalWaste' => (float) $totalWaste,
                    'byCategory' => $byCategory,
                    'monthlyTrend' => $monthlyTrend,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch waste analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get waste by user
     * GET /api/admin/analytics/waste-by-user?page=1&limit=10&sort=quantity
     */
    public function wasteByUser(Request $request)
    {
        try {
            $limit = min($request->query('limit', 10), 100);
            $page = $request->query('page', 1);
            $sort = $request->query('sort', 'quantity');

            // Check if tabung_sampah table exists
            $hasSampahTable = DB::select("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                [DB::connection()->getDatabaseName(), 'tabung_sampah']);

            if (empty($hasSampahTable)) {
                // Table doesn't exist - return empty result
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'records' => [],
                        'pagination' => [
                            'currentPage' => 1,
                            'totalPages' => 0,
                            'total' => 0,
                            'limit' => $limit,
                        ]
                    ]
                ], 200);
            }

            $query = DB::table('tabung_sampah')
                ->join('users', 'tabung_sampah.user_id', '=', 'users.user_id')
                ->select(
                    DB::raw('tabung_sampah.user_id as userId'),
                    DB::raw('MAX(users.nama) as userName'),
                    DB::raw('SUM(tabung_sampah.berat_kg) as totalWaste'),
                    DB::raw('COUNT(tabung_sampah.tabung_sampah_id) as wasteCount'),
                    DB::raw('MAX(tabung_sampah.created_at) as lastSubmission')
                )
                ->groupBy('tabung_sampah.user_id');

            // Sort
            if ($sort === 'quantity') {
                $query->orderByDesc('totalWaste');
            } elseif ($sort === 'count') {
                $query->orderByDesc('wasteCount');
            }

            // Get all records first for pagination calculation
            $allRecords = $query->get();
            $total = $allRecords->count();
            $offset = ($page - 1) * $limit;
            $records = $allRecords->slice($offset, $limit)->values();

            $totalPages = ceil($total / $limit);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'records' => $records->map(function ($record) {
                        return [
                            'userId' => (int) $record->userId,
                            'userName' => $record->userName,
                            'totalWaste' => (float) $record->totalWaste,
                            'wasteCount' => (int) $record->wasteCount,
                            'lastSubmission' => $record->lastSubmission,
                        ];
                    })->toArray(),
                    'pagination' => [
                        'currentPage' => $page,
                        'totalPages' => $totalPages,
                        'total' => $total,
                        'limit' => $limit,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch waste by user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get points distribution analytics
     * GET /api/admin/analytics/points?period=monthly
     */
    public function points(Request $request)
    {
        try {
            $period = $request->query('period', 'monthly');

            // Check if poin_transaksis table exists
            $hasPoinTable = DB::select("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                [DB::connection()->getDatabaseName(), 'poin_transaksis']);

            $totalDistributed = 0;
            $bySource = [
                'wasteSubmission' => 0,
                'referral' => 0,
                'purchases' => 0,
                'challenges' => 0,
            ];
            $topUsers = [];
            $monthlyDistribution = [];

            if (!empty($hasPoinTable)) {
                // Total points distributed
                $totalDistributed = DB::table('poin_transaksis')->sum('poin_didapat') ?? 0;

                // Points by source
                $bySource = [
                    'wasteSubmission' => (int) (DB::table('poin_transaksis')->where('sumber', 'setor_sampah')->sum('poin_didapat') ?? 0),
                    'referral' => (int) (DB::table('poin_transaksis')->where('sumber', 'referral')->sum('poin_didapat') ?? 0),
                    'purchases' => (int) (DB::table('poin_transaksis')->where('sumber', 'pembelian')->sum('poin_didapat') ?? 0),
                    'challenges' => (int) (DB::table('poin_transaksis')->where('sumber', 'challenges')->sum('poin_didapat') ?? 0),
                ];

                // Top users - would need poin_transaksis grouped by user
                $topUsersData = DB::table('poin_transaksis')
                    ->join('users', 'poin_transaksis.user_id', '=', 'users.user_id')
                    ->select(
                        DB::raw('poin_transaksis.user_id as userId'),
                        DB::raw('MAX(users.nama) as userName'),
                        DB::raw('SUM(poin_transaksis.poin_didapat) as totalPoints')
                    )
                    ->groupBy('poin_transaksis.user_id')
                    ->orderByDesc('totalPoints')
                    ->limit(10)
                    ->get();

                $topUsers = $topUsersData->map(function ($user, $index) {
                    return [
                        'userId' => (int) $user->userId,
                        'userName' => $user->userName,
                        'points' => (int) $user->totalPoints,
                        'rank' => $index + 1,
                    ];
                })->values()->toArray();

                // Monthly distribution (last 12 months)
                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $monthStart = $date->copy()->startOfMonth();
                    $monthEnd = $date->copy()->endOfMonth();

                    $points = DB::table('poin_transaksis')
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->sum('poin_didapat') ?? 0;

                    $monthlyDistribution[] = [
                        'month' => $date->format('Y-m'),
                        'points' => (int) $points,
                    ];
                }
            } else {
                // Generate empty monthly distribution
                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $monthlyDistribution[] = [
                        'month' => $date->format('Y-m'),
                        'points' => 0,
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'totalDistributed' => (int) $totalDistributed,
                    'bySource' => $bySource,
                    'topUsers' => $topUsers,
                    'monthlyDistribution' => $monthlyDistribution,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch points analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
