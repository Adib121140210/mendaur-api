<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TabungSampahResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tabung_sampah_id' => $this->tabung_sampah_id,
            'user_id' => $this->user_id,
            'jadwal_penyetoran_id' => $this->jadwal_penyetoran_id,
            'jenis_sampah' => $this->jenis_sampah,
            'berat_kg' => $this->berat_kg,
            'status' => $this->status,
            'poin_didapat' => $this->poin_didapat,
            'foto_sampah' => $this->getPhotoUrl(),
            'foto_sampah_url' => $this->getPhotoUrl(), // Alias for compatibility
            'nama_lengkap' => $this->nama_lengkap,
            'no_hp' => $this->no_hp,
            'titik_lokasi' => $this->titik_lokasi,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get the full URL for the waste photo
     * Handles both Cloudinary URLs and local storage paths
     */
    private function getPhotoUrl(): ?string
    {
        if (empty($this->foto_sampah)) {
            return null;
        }

        // If it's already a full URL (Cloudinary), return as-is with HTTPS
        if (str_starts_with($this->foto_sampah, 'http://') || str_starts_with($this->foto_sampah, 'https://')) {
            return str_replace('http://', 'https://', $this->foto_sampah);
        }

        // Validate that foto_sampah is a valid file path
        if (!preg_match('/^[\w\-\.\/]+$/', $this->foto_sampah)) {
            return null;
        }

        // Otherwise, it's a local storage path - convert to full URL
        return secure_asset('storage/' . $this->foto_sampah);
    }
}
