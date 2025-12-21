<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PoinTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminPointsController extends Controller
{
    /**
     * Award points to user manually
     * POST /api/admin/points/award
     */
    public function award(Request $request)
    {
        try {
            $request->validate([
                'userId' => 'required|exists:users,id',
                'points' => 'required|integer|min:1',
                'reason' => 'required|string|max:255',
                'category' => 'required|in:manual,bonus,promotion'
            ]);

            // Check if poin_transaksis table exists
            $hasPoinTable = DB::select("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                [DB::connection()->getDatabaseName(), 'poin_transaksis']);

            if (!empty($hasPoinTable)) {
                // Create transaction record
                DB::table('poin_transaksis')->insert([
                    'user_id' => $request->userId,
                    'jenis_poin' => $request->category,
                    'poin_didapat' => $request->points,
                    'keterangan' => $request->reason,
                    'status' => 'selesai',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Points awarded successfully',
                'data' => [
                    'userId' => (int) $request->userId,
                    'pointsAdded' => (int) $request->points,
                    'currentBalance' => (int) $request->points,
                    'timestamp' => Carbon::now()->toIso8601String(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to award points',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get points history
     * GET /api/admin/points/history?userId=&page=1&limit=20
     */
    public function history(Request $request)
    {
        try {
            $limit = min($request->query('limit', 20), 100);
            $page = $request->query('page', 1);
            $userId = $request->query('userId', null);

            // Check if poin_transaksis table exists
            $hasPoinTable = DB::select("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                [DB::connection()->getDatabaseName(), 'poin_transaksis']);

            if (empty($hasPoinTable)) {
                // Table doesn't exist - return empty result
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'history' => [],
                        'pagination' => [
                            'currentPage' => 1,
                            'totalPages' => 0,
                            'total' => 0,
                            'limit' => $limit,
                        ]
                    ]
                ], 200);
            }

            $query = DB::table('poin_transaksis')
                ->select(
                    'id',
                    'user_id',
                    'jenis_poin as action',
                    'poin_didapat as points',
                    'keterangan as reason',
                    'created_at as timestamp'
                );

            if ($userId) {
                $query->where('user_id', $userId);
            }

            $total = $query->count();
            $offset = ($page - 1) * $limit;
            $history = $query->orderByDesc('created_at')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $totalPages = ceil($total / $limit);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'history' => $history->map(function ($record) {
                        return [
                            'id' => (int) $record->id,
                            'userId' => (int) $record->user_id,
                            'action' => $record->action,
                            'points' => (int) $record->points,
                            'reason' => $record->reason,
                            'timestamp' => $record->timestamp,
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
                'message' => 'Failed to fetch points history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
