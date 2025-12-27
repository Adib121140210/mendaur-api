<?php

namespace App\Http\Controllers;

use App\Models\PenarikanTunai;
use App\Models\User;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PenarikanTunaiResource;

class PenarikanTunaiController extends Controller
{
    /**
     * Submit withdrawal request (POST /api/penarikan-tunai)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jumlah_poin' => 'required|integer|min:2000',
            'nomor_rekening' => 'required|string|max:50',
            'nama_bank' => 'required|string|max:100',
            'nama_penerima' => 'required|string|max:255'
        ]);

        // Validate multiple of 100
        if ($validated['jumlah_poin'] % 100 !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah poin harus kelipatan 100',
                'errors' => [
                    'jumlah_poin' => ['Jumlah poin harus kelipatan 100']
                ]
            ], 422);
        }

        // Get authenticated user from token
        $user = $request->user();

        // FIXED: Use actual available poin from transactions, not actual_poin field
        $availablePoin = $user->getUsablePoin();

        if ($availablePoin < $validated['jumlah_poin']) {
            return response()->json([
                'success' => false,
                'message' => 'Poin tidak mencukupi',
                'errors' => [
                    'jumlah_poin' => ["Saldo poin Anda hanya {$availablePoin}"]
                ]
            ], 400);
        }

        // Calculate Rupiah (100 points = Rp 1,000)
        $jumlah_rupiah = ($validated['jumlah_poin'] / 100) * 1000;

        DB::beginTransaction();
        try {
            // CRITICAL: Deduct points immediately to prevent double spending
            $user->decrement('actual_poin', $validated['jumlah_poin']);

            // Create withdrawal record
            $withdrawal = PenarikanTunai::create([
                'user_id' => $user->user_id,
                'jumlah_poin' => $validated['jumlah_poin'],
                'jumlah_rupiah' => $jumlah_rupiah,
                'nomor_rekening' => $validated['nomor_rekening'],
                'nama_bank' => $validated['nama_bank'],
                'nama_penerima' => $validated['nama_penerima'],
                'status' => 'pending'
            ]);

            // Send notification to user
            Notifikasi::create([
                'user_id' => $user->user_id,
                'judul' => 'Permintaan Penarikan Tunai Diajukan',
                'pesan' => "Permintaan penarikan Rp " . number_format($jumlah_rupiah, 0, ',', '.') . " berhasil diajukan dan sedang diproses",
                'tipe' => 'penarikan_tunai',
                'is_read' => false
            ]);

            // TODO: Send notification to admin (all admin users)
            // User::where('level', 'admin')->each(function($admin) use ($user, $jumlah_rupiah) {
            //     Notifikasi::create([
            //         'user_id' => $admin->id,
            //         'judul' => 'Permintaan Penarikan Tunai Baru',
            //         'pesan' => "Ada permintaan penarikan tunai sebesar Rp " . number_format($jumlah_rupiah, 0, ',', '.') . " dari {$user->nama}",
            //         'tipe' => 'admin_notif',
            //         'is_read' => false
            //     ]);
            // });

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permintaan penarikan tunai berhasil diajukan',
                'data' => new PenarikanTunaiResource($withdrawal)
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses penarikan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user withdrawal history (GET /api/penarikan-tunai)
     */
    public function index(Request $request)
    {
        $query = PenarikanTunai::where('user_id', $request->user()->user_id);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => PenarikanTunaiResource::collection($withdrawals->items()),
            'pagination' => [
                'current_page' => $withdrawals->currentPage(),
                'per_page' => $withdrawals->perPage(),
                'total' => $withdrawals->total(),
                'last_page' => $withdrawals->lastPage(),
            ]
        ]);
    }

    /**
     * Get single withdrawal detail (GET /api/penarikan-tunai/{id})
     */
    public function show($id)
    {
        $withdrawal = PenarikanTunai::with(['user', 'processedBy'])
            ->findOrFail($id);

        // Check authorization
        if (auth()->user()->user_id !== $withdrawal->user_id && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new PenarikanTunaiResource($withdrawal)
        ]);
    }

    /**
     * Get withdrawal summary (GET /api/penarikan-tunai/summary)
     * Optional endpoint for statistics
     */
    public function summary(Request $request)
    {
        $userId = $request->user()->user_id;

        $summary = [
            'total_withdrawn_points' => PenarikanTunai::where('user_id', $userId)
                ->where('status', 'approved')
                ->sum('jumlah_poin'),
            'total_withdrawn_rupiah' => PenarikanTunai::where('user_id', $userId)
                ->where('status', 'approved')
                ->sum('jumlah_rupiah'),
            'pending_count' => PenarikanTunai::where('user_id', $userId)
                ->where('status', 'pending')
                ->count(),
            'approved_count' => PenarikanTunai::where('user_id', $userId)
                ->where('status', 'approved')
                ->count(),
            'rejected_count' => PenarikanTunai::where('user_id', $userId)
                ->where('status', 'rejected')
                ->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Get withdrawals by user ID
     * GET /api/penarikan-tunai/user/{userId}
     */
    public function byUser(Request $request, $userId)
    {
        // IDOR Protection: User can only view their own data
        if ((int)$request->user()->user_id !== (int)$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = PenarikanTunai::where('user_id', $userId);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'status' => 'success',
            'data' => PenarikanTunaiResource::collection($withdrawals)
        ]);
    }
}
