<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PoinTransaksi;
use App\Services\PointService;
use App\Http\Resources\PoinTransaksiResource;
use Illuminate\Http\Request;

class PointController extends Controller
{
    /**
     * Get user's point summary + recent history
     * GET /api/user/{id}/poin
     * 
     * @param int $id User ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserPoints(Request $request, $id)
    {
        // IDOR Protection
        if ((int)$request->user()->id !== (int)$id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: Cannot access other user\'s data'
            ], 403);
        }
        
        try {
            $user = User::findOrFail($id);
            
            // Get recent transactions
            $recentTransactions = PoinTransaksi::where('user_id', $id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'total_poin' => $user->total_poin,
                        'level' => $user->level ?? 'Bronze',
                    ],
                    'recent_transactions' => PoinTransaksiResource::collection($recentTransactions),
                    'statistics' => PointService::getStatistics($id),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in getUserPoints:', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get point transaction history with pagination
     * GET /api/poin/history?page=1&per_page=20&sumber=setor_sampah
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory(Request $request)
    {
        try {
            $userId = $request->user()?->id;
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $perPage = $request->input('per_page', 20);
            $sumber = $request->input('sumber');

            $query = PoinTransaksi::where('user_id', $userId);

            if ($sumber) {
                $query->where('sumber', $sumber);
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => PoinTransaksiResource::collection($transactions->items()),
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in getHistory:', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's redemption history
     * GET /api/user/{id}/redeem-history
     * 
     * @param int $id User ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRedeemHistory(Request $request, $id)
    {
        // IDOR Protection
        if ((int)$request->user()->id !== (int)$id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: Cannot access other user\'s data'
            ], 403);
        }
        
        try {
            $user = User::findOrFail($id);
            
            $redemptions = PoinTransaksi::where('user_id', $id)
                ->where('sumber', 'redemption')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => PoinTransaksiResource::collection($redemptions),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in getRedeemHistory:', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get point statistics for a user
     * GET /api/user/{id}/poin/statistics
     * 
     * @param int $id User ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(Request $request, $id)
    {
        // IDOR Protection
        if ((int)$request->user()->id !== (int)$id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: Cannot access other user\'s data'
            ], 403);
        }
        
        try {
            $user = User::findOrFail($id);
            
            $stats = PointService::getStatistics($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'nama' => $user->nama,
                    ],
                    'statistics' => $stats,
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in getStatistics:', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get point breakdown by source
     * GET /api/poin/breakdown/{userId}
     * 
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBreakdown($userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            $breakdown = [
                'current_balance' => $user->total_poin,
                'earned_from' => [
                    'deposits' => (int) PoinTransaksi::where('user_id', $userId)
                        ->where('sumber', 'setor_sampah')
                        ->sum('poin_didapat'),
                    'bonuses' => (int) PoinTransaksi::where('user_id', $userId)
                        ->where('sumber', 'bonus')
                        ->sum('poin_didapat'),
                    'badges' => (int) PoinTransaksi::where('user_id', $userId)
                        ->where('sumber', 'badge')
                        ->sum('poin_didapat'),
                    'events' => (int) PoinTransaksi::where('user_id', $userId)
                        ->where('sumber', 'event')
                        ->sum('poin_didapat'),
                    'manual' => (int) PoinTransaksi::where('user_id', $userId)
                        ->where('sumber', 'manual')
                        ->sum('poin_didapat'),
                ],
                'spent_on' => [
                    'redemptions' => (int) abs(
                        PoinTransaksi::where('user_id', $userId)
                            ->where('sumber', 'redemption')
                            ->sum('poin_didapat')
                    ),
                ],
            ];

            return response()->json([
                'status' => 'success',
                'data' => $breakdown,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in getBreakdown:', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Award bonus points to a user (Admin only)
     * POST /api/poin/bonus
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function awardBonus(Request $request)
    {
        try {
            // TODO: Add admin middleware check

            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'points' => 'required|integer|min:1',
                'reason' => 'required|string|max:255',
            ]);

            $transaction = PointService::awardBonusPoints(
                $validated['user_id'],
                $validated['points'],
                'manual',
                $validated['reason']
            );

            $user = User::find($validated['user_id']);

            return response()->json([
                'status' => 'success',
                'message' => 'Bonus poin berhasil diberikan',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id,
                    'points_awarded' => $validated['points'],
                    'new_balance' => $user->fresh()->total_poin,
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in awardBonus:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
