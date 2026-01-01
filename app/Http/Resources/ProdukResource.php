<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdukResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'produk_id' => $this->produk_id,
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'foto' => $this->getPhotoUrl(),
            'foto_url' => $this->getPhotoUrl(), // Alias for compatibility
            'harga_poin' => $this->harga_poin,
            'stok' => $this->stok,
            'kategori' => $this->kategori,
            'status' => $this->status ?? 'tersedia',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get the full URL for the product photo
     * Handles both Cloudinary URLs and local storage paths
     */
    private function getPhotoUrl(): ?string
    {
        if (empty($this->foto)) {
            return null;
        }

        // If it's already a full URL (Cloudinary), return as-is with HTTPS
        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
            return str_replace('http://', 'https://', $this->foto);
        }

        // Validate that foto is a valid file path (not emoji or random text)
        if (!preg_match('/^[\w\-\.\/]+$/', $this->foto)) {
            return null;
        }

        // Otherwise, it's a local storage path - convert to full URL
        // Railway uses HTTPS, so use secure_asset
        return secure_asset('storage/' . $this->foto);
    }
}
