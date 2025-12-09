<?php

namespace App\Http\Controllers;

use App\Models\KategoriSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KategoriSampahController extends Controller
{
    /**
     * Get all kategori sampah with their jenis (hierarchical structure)
     */
    public function index()
    {
        try {
            $kategori = KategoriSampah::with(['activeJenisSampah' => function($query) {
                $query->orderBy('nama_jenis');
            }])
            ->where('is_active', true)
            ->orderBy('nama_kategori')
            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Kategori sampah berhasil diambil',
                'data' => $kategori
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil kategori sampah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific kategori with its jenis sampah
     */
    public function show($id)
    {
        try {
            $kategori = KategoriSampah::with('activeJenisSampah')
                ->where('is_active', true)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail kategori sampah berhasil diambil',
                'data' => $kategori
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori sampah tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get all jenis sampah for specific category
     */
    public function getJenisByKategori($id)
    {
        try {
            $kategori = KategoriSampah::findOrFail($id);
            $jenisSampah = $kategori->activeJenisSampah()
                ->orderBy('nama_jenis')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Jenis sampah berhasil diambil',
                'data' => [
                    'kategori' => $kategori,
                    'jenis_sampah' => $jenisSampah
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jenis sampah',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get flat list of all jenis sampah (for dropdown/select)
     */
    public function getAllJenisSampah()
    {
        try {
            $jenisSampah = \App\Models\JenisSampah::with('kategori')
                ->orderBy('nama')
                ->get()
                ->map(function($jenis) {
                    return [
                        'id' => $jenis->id,
                        'nama_jenis' => $jenis->nama_jenis,
                        'kategori' => $jenis->kategori->nama_kategori,
                        'full_name' => $jenis->kategori->nama_kategori . ' - ' . $jenis->nama_jenis,
                        'harga_per_kg' => $jenis->harga_per_kg,
                        'satuan' => $jenis->satuan,
                        'kode' => $jenis->kode,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Semua jenis sampah berhasil diambil',
                'data' => $jenisSampah
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jenis sampah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new kategori sampah (Admin only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'warna' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $kategori = KategoriSampah::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Kategori sampah berhasil ditambahkan',
                'data' => $kategori
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kategori sampah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update kategori sampah (Admin only)
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'sometimes|required|string|max:100',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'warna' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $kategori = KategoriSampah::findOrFail($id);
            $kategori->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Kategori sampah berhasil diupdate',
                'data' => $kategori
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate kategori sampah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete kategori sampah (Admin only)
     */
    public function destroy($id)
    {
        try {
            $kategori = KategoriSampah::findOrFail($id);
            $kategori->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kategori sampah berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori sampah',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
