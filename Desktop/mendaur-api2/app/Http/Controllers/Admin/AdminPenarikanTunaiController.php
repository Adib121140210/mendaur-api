<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenarikanTunai;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPenarikanTunaiController extends Controller
{
    /**
     * Get all withdrawal requests (GET /api/admin/penarikan-tunai)
     */
    public function index(Request $request)
    {
        $query = PenarikanTunai::with('user');

        // Filter by status
        if ($request->has('status')) {
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
                'id' => $withdrawal->id,
                'user' => [
                    'id' => $withdrawal->user->id,
                    'name' => $withdrawal->user->nama,
                    'email' => $withdrawal->user->email
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

        return response()->json([
            'success' => true,
            'data' => $withdrawals
        ]);
    }

    /**
     * Approve withdrawal (POST /api/admin/penarikan-tunai/{id}/approve)
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan_admin' => 'nullable|string|max:500'
        ]);

        $withdrawal = PenarikanTunai::with('user')->findOrFail($id);

        // Check if already processed
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Penarikan sudah diproses sebelumnya'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Update withdrawal status
            $withdrawal->update([
                'status' => 'approved',
                'catatan_admin' => $validated['catatan_admin'] ?? null,
                'processed_by' => auth()->id(),
                'processed_at' => now()
            ]);

            // Send notification to user
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
                    'id' => $withdrawal->id,
                    'status' => $withdrawal->status,
                    'catatan_admin' => $withdrawal->catatan_admin,
                    'processed_at' => $withdrawal->processed_at
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject withdrawal and refund points (POST /api/admin/penarikan-tunai/{id}/reject)
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan_admin' => 'required|string|max:500'
        ]);

        $withdrawal = PenarikanTunai::with('user')->findOrFail($id);

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
            $withdrawal->user->increment('total_poin', $withdrawal->jumlah_poin);

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'rejected',
                'catatan_admin' => $validated['catatan_admin'],
                'processed_by' => auth()->id(),
                'processed_at' => now()
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
                    'id' => $withdrawal->id,
                    'status' => $withdrawal->status,
                    'catatan_admin' => $withdrawal->catatan_admin,
                    'processed_at' => $withdrawal->processed_at,
                    'points_refunded' => $withdrawal->jumlah_poin
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get withdrawal statistics (GET /api/admin/penarikan-tunai/statistics)
     * Optional endpoint for admin dashboard
     */
    public function statistics()
    {
        $stats = [
            'pending' => [
                'count' => PenarikanTunai::where('status', 'pending')->count(),
                'total_points' => PenarikanTunai::where('status', 'pending')->sum('jumlah_poin'),
                'total_rupiah' => PenarikanTunai::where('status', 'pending')->sum('jumlah_rupiah')
            ],
            'approved_today' => [
                'count' => PenarikanTunai::where('status', 'approved')
                    ->whereDate('processed_at', today())
                    ->count(),
                'total_rupiah' => PenarikanTunai::where('status', 'approved')
                    ->whereDate('processed_at', today())
                    ->sum('jumlah_rupiah')
            ],
            'approved_this_month' => [
                'count' => PenarikanTunai::where('status', 'approved')
                    ->whereMonth('processed_at', now()->month)
                    ->whereYear('processed_at', now()->year)
                    ->count(),
                'total_rupiah' => PenarikanTunai::where('status', 'approved')
                    ->whereMonth('processed_at', now()->month)
                    ->whereYear('processed_at', now()->year)
                    ->sum('jumlah_rupiah')
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
