<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TabungSampah;
use App\Models\User;
use App\Models\LogAktivitas;
use App\Services\BadgeService;
use App\Services\PointService;
use App\Http\Resources\TabungSampahResource;

class TabungSampahController extends Controller
{
    protected $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }
    // List all tabung sampah
    public function index()
    {
        $data = TabungSampah::with(['user', 'jadwal'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => TabungSampahResource::collection($data)
        ]);
    }

    // Create new tabung sampah
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jadwal_id' => 'required|exists:jadwal_penyetorans,id',
            'nama_lengkap' => 'required|string',
            'no_hp' => 'required|string',
            'titik_lokasi' => 'required|string',
            'jenis_sampah' => 'required|string',
            'foto_sampah' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        // Handle file upload
        if ($request->hasFile('foto_sampah')) {
            $file = $request->file('foto_sampah');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/sampah', $filename, 'public');
            $validated['foto_sampah'] = $path;
        }

        $data = TabungSampah::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Setor sampah berhasil!',
            'data' => new TabungSampahResource($data)
        ], 201);
    }

    // Get specific tabung sampah
    public function show(TabungSampah $tabungSampah)
    {
        return response()->json([
            'status' => 'success',
            'data' => new TabungSampahResource($tabungSampah->load(['user', 'jadwal']))
        ]);
    }

    // Update tabung sampah
    public function update(Request $request, TabungSampah $tabungSampah)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'nullable|string',
            'no_hp' => 'nullable|string',
            'titik_lokasi' => 'nullable|string',
            'jenis_sampah' => 'nullable|string',
            'foto_sampah' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        // Handle file upload
        if ($request->hasFile('foto_sampah')) {
            $file = $request->file('foto_sampah');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/sampah', $filename, 'public');
            $validated['foto_sampah'] = $path;
        }

        $tabungSampah->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diupdate',
            'data' => new TabungSampahResource($tabungSampah)
        ]);
    }

    // Delete tabung sampah
    public function destroy(TabungSampah $tabungSampah)
    {
        $tabungSampah->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data deleted'
        ]);
    }

    public function byUser(Request $request, $id)
    {
        if ((int)$request->user()->user_id !== (int)$id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $data = TabungSampah::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => TabungSampahResource::collection($data)
        ]);
    }

    /**
     * Approve waste deposit and award points + check for badges
     * Uses PointService for centralized point logic
     */
    public function approve(Request $request, $id)
    {
        try {
            $tabungSampah = TabungSampah::findOrFail($id);

            $validated = $request->validate([
                'berat_kg' => 'required|numeric|min:0',
                'poin_didapat' => 'required|integer|min:0',
            ]);

            \Log::info("Approving tabung_sampah", [
                'tabung_sampah_id' => $id,
                'user_id' => $tabungSampah->user_id,
                'berat_kg' => $validated['berat_kg'],
                'poin_didapat' => $validated['poin_didapat'],
            ]);

            // Update status to approved
            $tabungSampah->update([
                'status' => 'approved',
                'berat_kg' => $validated['berat_kg'],
                'poin_didapat' => $validated['poin_didapat'],
            ]);

            // Use PointService to handle all point logic
            $pointCalculation = PointService::calculatePointsForDeposit($tabungSampah);
            PointService::applyDepositPoints($tabungSampah);

            // Update user statistics
            $user = User::findOrFail($tabungSampah->user_id);
            $user->increment('total_setor_sampah');

            \Log::info("User poin updated via PointService", [
                'user_id' => $user->id,
                'total_poin_awarded' => $pointCalculation['total'],
                'breakdown' => $pointCalculation['breakdown'],
                'new_total_poin' => $user->fresh()->total_poin,
            ]);

            // Log the waste deposit activity
            LogAktivitas::log(
                $user->id,
                LogAktivitas::TYPE_SETOR_SAMPAH,
                "Menyetor {$validated['berat_kg']}kg sampah {$tabungSampah->jenis_sampah}",
                $pointCalculation['total']
            );

            // ✨ Check for new badges and award rewards automatically
            $newBadges = $this->badgeService->checkAndAwardBadges($user->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Penyetoran disetujui!',
                'data' => [
                    'tabung_sampah' => new TabungSampahResource($tabungSampah->fresh()),
                    'user' => $user->fresh(),
                    'poin_diberikan' => $pointCalculation['total'],
                    'breakdown' => $pointCalculation['breakdown'],
                    'new_badges' => $newBadges,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning("Validation error on approve", [
                'id' => $id,
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error("Error approving tabung_sampah", [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject waste deposit
     */
    public function reject(Request $request, $id)
    {
        $tabungSampah = TabungSampah::findOrFail($id);

        $tabungSampah->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penyetoran ditolak',
            'data' => new TabungSampahResource($tabungSampah),
        ]);
    }
}
