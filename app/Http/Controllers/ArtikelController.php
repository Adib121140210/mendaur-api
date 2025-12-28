<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Artikel;
use Illuminate\Support\Str;
use App\Http\Resources\ArtikelResource;

class ArtikelController extends Controller
{
    /**
     * Get all articles
     */
    public function index()
    {
        $artikel = Artikel::orderBy('tanggal_publikasi', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ArtikelResource::collection($artikel),
        ], 200);
    }

    /**
     * Get specific article by slug or ID
     */
    public function show($identifier)
    {
        // Support both ID (numeric) and slug lookup
        if (is_numeric($identifier)) {
            $artikel = Artikel::find($identifier);
        } else {
            $artikel = Artikel::where('slug', $identifier)->first();
        }

        if (!$artikel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Artikel tidak ditemukan',
            ], 404);
        }

        // Increment Article views
        $artikel->increment('views');

        return response()->json([
            'status' => 'success',
            'data' => new ArtikelResource($artikel),
        ], 200);
    }

    /**
     * Create new article (Admin/Superadmin only)
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
            'judul' => 'required|string',
            'konten' => 'required|string',
            'foto_cover' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'penulis' => 'required|string',
            'kategori' => 'required|string',
            'tanggal_publikasi' => 'required|date',
        ]);

        // Generate slug
        $validated['slug'] = Str::slug($validated['judul']);

        // Handle file upload
        if ($request->hasFile('foto_cover')) {
            $file = $request->file('foto_cover');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/artikel', $filename, 'public');
            $validated['foto_cover'] = $path;
        }

        $artikel = Artikel::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Artikel berhasil ditambahkan',
            'data' => new ArtikelResource($artikel),
        ], 201);
    }

    /**
     * Update article (Admin/Superadmin only)
     */
    public function update(Request $request, $slug)
    {
        // Verify admin or superadmin role
        if (!$request->user()?->isAdminUser()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized - Admin role required',
            ], 403);
        }

        $artikel = Artikel::where('slug', $slug)->first();

        if (!$artikel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Artikel tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'judul' => 'nullable|string',
            'konten' => 'nullable|string',
            'foto_cover' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'penulis' => 'nullable|string',
            'kategori' => 'nullable|string',
            'tanggal_publikasi' => 'nullable|date',
        ]);

        // Update slug if judul changed
        if (isset($validated['judul'])) {
            $validated['slug'] = Str::slug($validated['judul']);
        }

        // Handle file upload
        if ($request->hasFile('foto_cover')) {
            $file = $request->file('foto_cover');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/artikel', $filename, 'public');
            $validated['foto_cover'] = $path;
        }

        $artikel->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Artikel berhasil diupdate',
            'data' => new ArtikelResource($artikel),
        ], 200);
    }

    /**
     * Delete article (Admin/Superadmin only)
     */
    public function destroy($slug, Request $request)
    {
        // Verify admin or superadmin role
        if (!$request->user()?->isAdminUser()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized - Admin role required',
            ], 403);
        }

        $artikel = Artikel::where('slug', $slug)->first();

        if (!$artikel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Artikel tidak ditemukan',
            ], 404);
        }

        $artikel->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Artikel berhasil dihapus',
        ], 200);
    }
}
