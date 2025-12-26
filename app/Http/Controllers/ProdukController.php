<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Http\Resources\ProdukResource;

class ProdukController extends Controller
{
    /**
     * Get all products
     */
    public function index()
    {
        $produk = Produk::where('status', 'tersedia')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ProdukResource::collection($produk),
        ], 200);
    }

    /**
     * Get specific product
     */
    public function show($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new ProdukResource($produk),
        ], 200);
    }

    /**
     * Create new product (Admin/Superadmin only)
     */
    public function store(Request $request)
    {
        // Verify admin or superadmin role
        if (!$request->user()?->isAdminUser()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized - Admin role required',
            ], 403);
        }

        $validated = $request->validate([
            'nama' => 'required|string',
            'deskripsi' => 'nullable|string',
            'harga_poin' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'kategori' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'status' => 'nullable|in:tersedia,habis,nonaktif',
        ]);

        // Handle file upload
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/produk', $filename, 'public');
            $validated['foto'] = $path;
        }

        $produk = Produk::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan',
            'data' => new ProdukResource($produk),
        ], 201);
    }

    /**
     * Update product (Admin/Superadmin only)
     */
    public function update(Request $request, $id)
    {
        // Verify admin or superadmin role
        if (!$request->user()?->isAdminUser()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized - Admin role required',
            ], 403);
        }

        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'nama' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'harga_poin' => 'nullable|integer|min:0',
            'stok' => 'nullable|integer|min:0',
            'kategori' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'status' => 'nullable|in:tersedia,habis,nonaktif',
        ]);

        // Handle file upload
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/produk', $filename, 'public');
            $validated['foto'] = $path;
        }

        $produk->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil diupdate',
            'data' => new ProdukResource($produk),
        ], 200);
    }

    /**
     * Delete product (Admin/Superadmin only)
     */
    public function destroy($id, Request $request)
    {
        // Verify admin or superadmin role
        if (!$request->user()?->isAdminUser()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized - Admin role required',
            ], 403);
        }

        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        $produk->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus',
        ], 200);
    }
}
