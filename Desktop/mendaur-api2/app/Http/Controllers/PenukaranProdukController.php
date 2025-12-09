<?php

namespace App\Http\Controllers;

use App\Models\PenukaranProduk;
use App\Services\PointService;
use Illuminate\Http\Request;

class PenukaranProdukController extends Controller
{
    /**
     * Get user's product redemption history
     * GET /api/penukaran-produk
     */
    public function index(Request $request)
    {
        try {
            $query = PenukaranProduk::with('produk')
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($request->has('status') && $request->status !== 'semua') {
                $query->where('status', $request->status);
            }

            $redemptions = $query->get();

            // Transform data to match frontend expectations
            $transformedData = $redemptions->map(function ($redemption) {
                return [
                    'id' => $redemption->id,
                    'produk_id' => $redemption->produk_id,
                    'nama_produk' => $redemption->nama_produk,
                    'jumlah_poin' => $redemption->poin_digunakan,
                    'jumlah' => $redemption->jumlah,
                    'status' => $redemption->status,
                    'metode_ambil' => $redemption->metode_ambil,
                    'catatan_admin' => $redemption->catatan,
                    'created_at' => $redemption->created_at?->toISOString(),
                    'tanggal_penukaran' => $redemption->tanggal_penukaran?->toISOString(),
                    'tanggal_diambil' => $redemption->tanggal_diambil?->toISOString(),
                    'produk' => $redemption->produk ? [
                        'id' => $redemption->produk->id,
                        'nama' => $redemption->produk->nama,
                        'deskripsi' => $redemption->produk->deskripsi,
                        'poin' => $redemption->produk->poin,
                        'stok' => $redemption->produk->stok,
                        'foto' => $redemption->produk->foto,
                    ] : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $transformedData
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching product redemptions:', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data penukaran produk'
            ], 500);
        }
    }

    /**
     * Get single redemption detail
     * GET /api/penukaran-produk/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $redemption = PenukaranProduk::with('produk')
                ->where('user_id', $request->user()->id)
                ->findOrFail($id);

            // Transform data to match frontend expectations
            $transformedData = [
                'id' => $redemption->id,
                'produk_id' => $redemption->produk_id,
                'nama_produk' => $redemption->nama_produk,
                'jumlah_poin' => $redemption->poin_digunakan,
                'jumlah' => $redemption->jumlah,
                'status' => $redemption->status,
                'metode_ambil' => $redemption->metode_ambil,
                'catatan_admin' => $redemption->catatan,
                'created_at' => $redemption->created_at?->toISOString(),
                'tanggal_penukaran' => $redemption->tanggal_penukaran?->toISOString(),
                'tanggal_diambil' => $redemption->tanggal_diambil?->toISOString(),
                'produk' => $redemption->produk ? [
                    'id' => $redemption->produk->id,
                    'nama' => $redemption->produk->nama,
                    'deskripsi' => $redemption->produk->deskripsi,
                    'poin' => $redemption->produk->poin,
                    'stok' => $redemption->produk->stok,
                    'foto' => $redemption->produk->foto,
                ] : null,
            ];

            return response()->json([
                'status' => 'success',
                'data' => $transformedData
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data penukaran tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching redemption detail:', [
                'redemption_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil detail penukaran'
            ], 500);
        }
    }

    /**
     * Create new product redemption
     * POST /api/penukaran-produk
     * Uses PointService for centralized point deduction logic
     */
    public function store(Request $request)
    {
        try {
            // Accept both jumlah_poin (from frontend) and jumlah (from API)
            $input = $request->all();

            // If frontend sends jumlah_poin, convert it to points directly
            if (isset($input['jumlah_poin'])) {
                $totalPoin = (int) $input['jumlah_poin'];
                $jumlah = isset($input['jumlah']) ? $input['jumlah'] : 1;
            } else {
                // Legacy: calculate from jumlah
                $jumlah = $input['jumlah'] ?? 1;
                $totalPoin = null; // will calculate below
            }

            $validated = $request->validate([
                'produk_id' => 'required|exists:produks,id',
                'metode_ambil' => 'required|string',
            ]);

            $user = $request->user();
            $produk = \App\Models\Produk::findOrFail($validated['produk_id']);

            // Calculate total points if not provided
            if ($totalPoin === null) {
                $totalPoin = $produk->poin * $jumlah;
            }

            // Check if product has enough stock
            if ($produk->stok < $jumlah) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Stok produk tidak mencukupi',
                    'data' => [
                        'requested' => $jumlah,
                        'available' => $produk->stok
                    ]
                ], 400);
            }

            // Use PointService to handle point deduction with validation
            try {
                PointService::deductPointsForRedemption($user, $totalPoin);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'data' => [
                        'required_points' => $totalPoin,
                        'current_points' => $user->total_poin,
                        'shortage' => max(0, $totalPoin - $user->total_poin)
                    ]
                ], 400);
            }

            \DB::beginTransaction();

            try {
                // Create redemption record
                $redemption = PenukaranProduk::create([
                    'user_id' => $user->id,
                    'produk_id' => $produk->id,
                    'nama_produk' => $produk->nama,
                    'poin_digunakan' => $totalPoin,
                    'jumlah' => $jumlah,
                    'status' => 'pending',
                    'metode_ambil' => $validated['metode_ambil'],
                    'catatan' => $request->catatan ?? null,
                    'tanggal_penukaran' => now(),
                    'tanggal_diambil' => null,
                ]);

                // Reduce product stock
                $produk->decrement('stok', $jumlah);

                \DB::commit();

                // Reload relationships and refresh user data from database
                $redemption->load('produk');
                $user->refresh();

                // Transform response
                $transformedData = [
                    'id' => $redemption->id,
                    'produk_id' => $redemption->produk_id,
                    'nama_produk' => $redemption->nama_produk,
                    'jumlah_poin' => $redemption->poin_digunakan,
                    'jumlah' => $redemption->jumlah,
                    'status' => $redemption->status,
                    'metode_ambil' => $redemption->metode_ambil,
                    'catatan_admin' => $redemption->catatan,
                    'tanggal_penukaran' => $redemption->tanggal_penukaran?->toISOString(),
                    'tanggal_diambil' => $redemption->tanggal_diambil?->toISOString(),
                    'created_at' => $redemption->created_at?->toISOString(),
                    'produk' => [
                        'id' => $produk->id,
                        'nama' => $produk->nama,
                        'poin' => $produk->poin,
                        'foto' => $produk->foto,
                    ]
                ];

                return response()->json([
                    'status' => 'success',
                    'message' => 'Penukaran produk berhasil dibuat',
                    'data' => $transformedData
                ], 201);

            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating product redemption:', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membuat penukaran produk'
            ], 500);
        }
    }

    /**
     * Update redemption (admin can set tanggal_diambil)
     * PUT /api/penukaran-produk/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $redemption = PenukaranProduk::findOrFail($id);

            $validated = $request->validate([
                'status' => 'sometimes|required|in:pending,approved,cancelled',
                'metode_ambil' => 'sometimes|required|string',
                'catatan' => 'sometimes|nullable|string',
                'tanggal_diambil' => 'sometimes|nullable|date',
            ]);

            $redemption->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Penukaran berhasil diperbarui',
                'data' => $redemption
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data penukaran tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error updating redemption:', [
                'redemption_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui penukaran'
            ], 500);
        }
    }

    /**
     * Cancel redemption and refund points
     * PUT /api/penukaran-produk/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        try {
            $redemption = PenukaranProduk::where('user_id', $request->user()->id)
                ->findOrFail($id);

            // Only allow canceling pending orders
            if ($redemption->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya pesanan pending yang bisa dibatalkan',
                    'current_status' => $redemption->status
                ], 400);
            }

            \DB::beginTransaction();

            try {
                // Refund points to user
                $user = $redemption->user;
                $user->increment('total_poin', $redemption->poin_digunakan);

                // Return stock to product
                $produk = $redemption->produk;
                $produk->increment('stok', $redemption->jumlah);

                // Update redemption status to cancelled
                $redemption->update(['status' => 'cancelled']);

                \DB::commit();

                // Refresh data from database
                $user->refresh();
                $redemption->refresh();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Penukaran berhasil dibatalkan, poin telah dikembalikan',
                    'data' => [
                        'id' => $redemption->id,
                        'status' => $redemption->status,
                        'poin_dikembalikan' => $redemption->poin_digunakan,
                        'user_total_poin' => $user->total_poin,
                        'stok_dikembalikan' => $redemption->jumlah
                    ]
                ], 200);

            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data penukaran tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error cancelling redemption:', [
                'redemption_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membatalkan penukaran'
            ], 500);
        }
    }

    /**
     * Delete redemption and refund points
     * DELETE /api/penukaran-produk/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $redemption = PenukaranProduk::where('user_id', $request->user()->id)
                ->findOrFail($id);

            // Only allow deleting pending or cancelled orders
            if (!in_array($redemption->status, ['pending', 'cancelled'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya pesanan pending atau cancelled yang bisa dihapus',
                    'current_status' => $redemption->status
                ], 400);
            }

            \DB::beginTransaction();

            try {
                // Only refund if not already cancelled
                if ($redemption->status !== 'cancelled') {
                    $user = $redemption->user;
                    $user->increment('total_poin', $redemption->poin_digunakan);

                    $produk = $redemption->produk;
                    $produk->increment('stok', $redemption->jumlah);
                }

                // Delete the redemption record
                $redemption->delete();

                \DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Penukaran berhasil dihapus'
                ], 200);

            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data penukaran tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error deleting redemption:', [
                'redemption_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus penukaran'
            ], 500);
        }
    }
}
