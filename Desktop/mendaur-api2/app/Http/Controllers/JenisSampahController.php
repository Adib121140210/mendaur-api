<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JenisSampahController extends Controller
{
    /**
     * Create new jenis sampah (Admin only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori_sampah_id' => 'required|exists:kategori_sampah,id',
            'nama_jenis' => 'required|string|max:100',
            'harga_per_kg' => 'required|numeric|min:0',
            'satuan' => 'sometimes|string|max:20',
            'kode' => 'nullable|string|max:20|unique:jenis_sampah,kode',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jenis = JenisSampah::create($validator->validated());
            $jenis->load('kategori');

            return response()->json([
                'success' => true,
                'message' => 'Jenis sampah berhasil ditambahkan',
                'data' => $jenis
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan jenis sampah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update jenis sampah (Admin only)
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kategori_sampah_id' => 'sometimes|exists:kategori_sampah,id',
            'nama_jenis' => 'sometimes|required|string|max:100',
            'harga_per_kg' => 'sometimes|numeric|min:0',
            'satuan' => 'sometimes|string|max:20',
            'kode' => 'nullable|string|max:20|unique:jenis_sampah,kode,' . $id,
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
            $jenis = JenisSampah::findOrFail($id);
            $jenis->update($validator->validated());
            $jenis->load('kategori');

            return response()->json([
                'success' => true,
                'message' => 'Jenis sampah berhasil diupdate',
                'data' => $jenis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate jenis sampah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete jenis sampah (Admin only)
     */
    public function destroy($id)
    {
        try {
            $jenis = JenisSampah::findOrFail($id);
            $jenis->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jenis sampah berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jenis sampah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all jenis sampah with kategori relationship
     */
    public function index(Request $request)
    {
        try {
            $query = JenisSampah::with('kategori');

            // Filter by category if provided
            if ($request->has('kategori_id')) {
                $query->byKategori($request->kategori_id);
            }

            // Filter by active status
            if ($request->input('active', true)) {
                $query->aktif();
            }

            $jenisSampah = $query->orderBy('nama_jenis')->get();

            return response()->json([
                'success' => true,
                'data' => $jenisSampah->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nama_jenis' => $item->nama_jenis,
                        'kategori_sampah_id' => $item->kategori_sampah_id,
                        'kategori_sampah' => [
                            'id' => $item->kategori->id,
                            'nama_kategori' => $item->kategori->nama_kategori,
                            'icon' => $item->kategori->icon,
                            'color' => $item->kategori->color ?? '#10b981',
                        ],
                        'harga_per_kg' => (float)$item->harga_per_kg,
                        'satuan' => $item->satuan,
                        'kode' => $item->kode,
                        'is_active' => $item->is_active,
                        'updated_at' => $item->updated_at,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jenis sampah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single jenis sampah
     */
    public function show($id)
    {
        try {
            $jenis = JenisSampah::with('kategori')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $jenis->id,
                    'nama_jenis' => $jenis->nama_jenis,
                    'kategori_sampah' => [
                        'id' => $jenis->kategori->id,
                        'nama_kategori' => $jenis->kategori->nama_kategori,
                        'icon' => $jenis->kategori->icon,
                        'color' => $jenis->kategori->color ?? '#10b981',
                    ],
                    'harga_per_kg' => (float)$jenis->harga_per_kg,
                    'satuan' => $jenis->satuan,
                    'kode' => $jenis->kode,
                    'is_active' => $jenis->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis sampah tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
