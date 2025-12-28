<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenarikanTunai;
use App\Models\Notifikasi;
use App\Models\AuditLog;
use App\Models\PoinTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPenarikanTunaiController extends Controller
{
    /**
     * Get all withdrawal requests (GET /api/admin/penarikan-tunai)
     */
    public function index(Request $request)
    {
        // RBAC: Admin+ only - Check if user is admin or superadmin
        if (!$request->user()->isAdminUser()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can view withdrawal requests'
            ], 403);
        }

        $query = PenarikanTunai::with('user');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        // Transform data for response
        $withdrawals->getCollection()->transform(function ($withdrawal) {
            return [
                'id' => $withdrawal->penarikan_tunai_id,
                'user' => [
                    'id' => $withdrawal->user?->user_id ?? null,
                    'name' => $withdrawal->user?->nama ?? null,
                    'email' => $withdrawal->user?->email ?? null
                ],
                'jumlah_poin' => $withdrawal->jumlah_poin,
                'jumlah_rupiah' => $withdrawal->jumlah_rupiah,
                'nomor_rekening' => $withdrawal->nomor_rekening,
                'nama_bank' => $withdrawal->nama_bank,
                'nama_penerima' => $withdrawal->nama_penerima,
                'status' => $withdrawal->status,
                'catatan_admin' => $withdrawal->catatan_admin,
                'processed_by' => $withdrawal->processed_by,
                'processed_at' => $withdrawal->processed_at,
                'created_at' => $withdrawal->created_at
            ];
        });

        // Add summary statistics
        $summary = [
            'total' => PenarikanTunai::count(),
            'pending' => PenarikanTunai::where('status', 'pending')->count(),
            'approved' => PenarikanTunai::where('status', 'approved')->count(),
            'rejected' => PenarikanTunai::where('status', 'rejected')->count(),
            'total_poin' => (int) (PenarikanTunai::sum('jumlah_poin') ?? 0),
            'total_nominal' => (int) (PenarikanTunai::sum('jumlah_rupiah') ?? 0),
        ];

        return response()->json([
            'success' => true,
            'data' => $withdrawals,
            'summary' => $summary
        ]);
    }

    /**
     * Get single withdrawal request detail (GET /api/admin/penarikan-tunai/{withdrawalId})
     */
    public function show(Request $request, $withdrawalId)
    {
        // RBAC: Admin+ only - Check if user is admin or superadmin
        if (!$request->user()->isAdminUser()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can view withdrawal details'
            ], 403);
        }

        try {
            // FIX: Use correct primary key
            $withdrawal = PenarikanTunai::where('penarikan_tunai_id', $withdrawalId)
                ->with('user')
                ->firstOrFail();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $withdrawal->penarikan_tunai_id,  // FIX: Use correct primary key
                    'user' => [
                        'id' => $withdrawal->user?->user_id,
                        'name' => $withdrawal->user?->nama,
                        'email' => $withdrawal->user?->email
                    ],
                    'jumlah_poin' => $withdrawal->jumlah_poin,
                    'jumlah_rupiah' => $withdrawal->jumlah_rupiah,
                    'nomor_rekening' => $withdrawal->nomor_rekening,
                    'nama_bank' => $withdrawal->nama_bank,
                    'nama_penerima' => $withdrawal->nama_penerima,
                    'status' => $withdrawal->status,
                    'catatan_admin' => $withdrawal->catatan_admin,
                    'processed_by' => $withdrawal->processed_by,
                    'processed_at' => $withdrawal->processed_at,
                    'created_at' => $withdrawal->created_at
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Withdrawal record not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error retrieving withdrawal detail:', [
                'withdrawal_id' => $withdrawalId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve withdrawal detail'
            ], 500);
        }
    }

    /**
     * Approve withdrawal (PATCH /api/admin/penarikan-tunai/{withdrawalId}/approve)
     */
    public function approve(Request $request, $withdrawalId)
    {
        try {
            if (!$request->user()->isAdminUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Admin role required'
                ], 403);
            }

            $catatanAdmin = $request->input('catatan_admin');
            if (!is_string($catatanAdmin)) {
                $catatanAdmin = null;
            }
            if ($catatanAdmin !== null && strlen($catatanAdmin) > 500) {
                return response()->json([
                    'success' => false,
                    'message' => 'Catatan admin maksimal 500 karakter'
                ], 422);
            }

            $withdrawal = PenarikanTunai::where('penarikan_tunai_id', $withdrawalId)
                ->with('user')
                ->firstOrFail();

            if ($withdrawal->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Penarikan sudah diproses sebelumnya'
                ], 400);
            }

            DB::beginTransaction();
            try {
                $withdrawal->update([
                    'status' => 'approved',
                    'catatan_admin' => $catatanAdmin,
                    'processed_by' => auth()->user()->user_id,
                    'processed_at' => now()
                ]);

                AuditLog::create([
                    'admin_id' => auth()->user()->user_id,
                    'action_type' => 'approve_withdrawal',
                    'resource_type' => 'PenarikanTunai',
                    'resource_id' => $withdrawalId,
                    'old_values' => ['status' => 'pending'],
                    'new_values' => ['status' => 'approved'],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'status' => 'success'
                ]);

                Notifikasi::create([
                    'user_id' => $withdrawal->user_id,
                    'judul' => 'Penarikan Tunai Disetujui',
                    'pesan' => "Penarikan Rp " . number_format($withdrawal->jumlah_rupiah, 0, ',', '.') . " telah disetujui dan sedang diproses. Dana akan ditransfer ke rekening Anda dalam 1-3 hari kerja.",
                    'tipe' => 'penarikan_tunai',
                    'is_read' => false
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Penarikan tunai berhasil disetujui',
                    'data' => [
                        'id' => $withdrawal->penarikan_tunai_id,
                        'status' => $withdrawal->status,
                        'catatan_admin' => $withdrawal->catatan_admin,
                        'processed_at' => $withdrawal->processed_at
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal record not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Withdrawal approval failed', ['id' => $withdrawalId, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject withdrawal and refund points (PATCH /api/admin/penarikan-tunai/{withdrawalId}/reject)
     */
    public function reject(Request $request, $withdrawalId)
    {
        try {
            // RBAC: Admin+ only - Check if user is admin or superadmin
            if (!$request->user()->isAdminUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Admin role required'
                ], 403);
            }

            $validated = $request->validate([
                'catatan_admin' => 'required|string|max:500'
            ]);

            // FIX: Use correct primary key
            $withdrawal = PenarikanTunai::where('penarikan_tunai_id', $withdrawalId)
                ->with('user')
                ->firstOrFail();

            // Check if already processed
            if ($withdrawal->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Penarikan sudah diproses sebelumnya'
                ], 400);
            }

            DB::beginTransaction();
            try {
                // CRITICAL: Refund points to user
                $withdrawal->user->increment('actual_poin', $withdrawal->jumlah_poin);
                $withdrawal->user->increment('display_poin', $withdrawal->jumlah_poin);

                // CRITICAL: Record point refund transaction
                PoinTransaksi::create([
                    'user_id' => $withdrawal->user_id,
                    'poin_didapat' => $withdrawal->jumlah_poin,
                    'sumber' => 'pengembalian_penarikan',
                    'keterangan' => 'Pengembalian poin dari penarikan tunai yang ditolak',
                    'referensi_id' => $withdrawalId,
                    'referensi_tipe' => 'PenarikanTunai'
                ]);

                // Update withdrawal status
                $withdrawal->update([
                    'status' => 'rejected',
                    'catatan_admin' => $validated['catatan_admin'],
                    'processed_by' => auth()->user()->user_id,
                    'processed_at' => now()
                ]);

                // Log the action
                AuditLog::create([
                    'admin_id' => auth()->user()->user_id,
                    'action_type' => 'reject_withdrawal',
                    'resource_type' => 'PenarikanTunai',
                    'resource_id' => $withdrawalId,
                    'old_values' => ['status' => 'pending'],
                    'new_values' => ['status' => 'rejected'],
                    'reason' => $validated['catatan_admin'],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'status' => 'success'
                ]);

                // Send notification to user
                Notifikasi::create([
                    'user_id' => $withdrawal->user_id,
                    'judul' => 'Penarikan Tunai Ditolak',
                    'pesan' => "Penarikan Rp " . number_format($withdrawal->jumlah_rupiah, 0, ',', '.') . " ditolak. Alasan: {$validated['catatan_admin']}. Poin sebesar {$withdrawal->jumlah_poin} telah dikembalikan ke saldo Anda.",
                    'tipe' => 'penarikan_tunai',
                    'is_read' => false
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Penarikan tunai ditolak dan poin dikembalikan',
                    'data' => [
                        'id' => $withdrawal->penarikan_tunai_id,  // FIX: Use correct primary key
                        'status' => $withdrawal->status,
                        'catatan_admin' => $withdrawal->catatan_admin,
                        'processed_at' => $withdrawal->processed_at,
                        'points_refunded' => $withdrawal->jumlah_poin
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal record not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete/Cancel withdrawal (DELETE /api/admin/penarikan-tunai/{withdrawalId})
     */
    public function destroy(Request $request, $withdrawalId)
    {
        try {
            // RBAC: Admin+ only - Check if user is admin or superadmin
            if (!$request->user()->isAdminUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Admin role required'
                ], 403);
            }

            // FIX: Use correct primary key
            $withdrawal = PenarikanTunai::where('penarikan_tunai_id', $withdrawalId)->firstOrFail();

            // Can only delete pending withdrawals
            if ($withdrawal->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya penarikan dengan status pending yang dapat dihapus'
                ], 400);
            }

            DB::beginTransaction();
            try {
                $withdrawal->delete();

                // Log the action
                AuditLog::create([
                    'admin_id' => auth()->user()->user_id,
                    'action_type' => 'delete_withdrawal',
                    'resource_type' => 'PenarikanTunai',
                    'resource_id' => $withdrawalId,
                    'old_values' => $withdrawal->toArray(),
                    'new_values' => [],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'status' => 'success'
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Penarikan berhasil dihapus'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal record not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get withdrawal statistics (GET /api/admin/penarikan-tunai/stats/overview)
     */
    public function stats(Request $request)
    {
        try {
            // RBAC: Admin+ only - Check if user is admin or superadmin
            if (!$request->user()->isAdminUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Admin role required'
                ], 403);
            }

            $stats = [
                'total' => PenarikanTunai::count(),
                'pending' => [
                    'count' => PenarikanTunai::where('status', 'pending')->count(),
                    'total_points' => (int) (PenarikanTunai::where('status', 'pending')->sum('jumlah_poin') ?? 0),
                    'total_rupiah' => (int) (PenarikanTunai::where('status', 'pending')->sum('jumlah_rupiah') ?? 0)
                ],
                'approved' => [
                    'count' => PenarikanTunai::where('status', 'approved')->count(),
                    'total_points' => (int) (PenarikanTunai::where('status', 'approved')->sum('jumlah_poin') ?? 0),
                    'total_rupiah' => (int) (PenarikanTunai::where('status', 'approved')->sum('jumlah_rupiah') ?? 0)
                ],
                'rejected' => [
                    'count' => PenarikanTunai::where('status', 'rejected')->count(),
                    'total_points' => (int) (PenarikanTunai::where('status', 'rejected')->sum('jumlah_poin') ?? 0),
                    'total_rupiah' => (int) (PenarikanTunai::where('status', 'rejected')->sum('jumlah_rupiah') ?? 0)
                ],
                'total_poin' => (int) (PenarikanTunai::sum('jumlah_poin') ?? 0),
                'total_nominal' => (int) (PenarikanTunai::sum('jumlah_rupiah') ?? 0),
                'approved_today' => [
                    'count' => PenarikanTunai::where('status', 'approved')
                        ->whereDate('processed_at', today())
                        ->count(),
                    'total_rupiah' => (int) (PenarikanTunai::where('status', 'approved')
                        ->whereDate('processed_at', today())
                        ->sum('jumlah_rupiah') ?? 0)
                ],
                'approved_this_month' => [
                    'count' => PenarikanTunai::where('status', 'approved')
                        ->whereMonth('processed_at', now()->month)
                        ->whereYear('processed_at', now()->year)
                        ->count(),
                    'total_rupiah' => (int) (PenarikanTunai::where('status', 'approved')
                        ->whereMonth('processed_at', now()->month)
                        ->whereYear('processed_at', now()->year)
                        ->sum('jumlah_rupiah') ?? 0)
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
