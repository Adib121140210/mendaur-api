<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'role_id' => $this->role_id,
            'no_hp' => $this->no_hp,
            'nama' => $this->nama,
            'email' => $this->email,
            'alamat' => $this->alamat,
            'foto_profil' => $this->getPhotoUrl(),
            'actual_poin' => $this->actual_poin,
            'display_poin' => $this->display_poin,  // For leaderboard display
            'poin_tercatat' => $this->poin_tercatat,
            'total_setor_sampah' => $this->total_setor_sampah,
            'level' => $this->level,
            'tipe_nasabah' => $this->tipe_nasabah,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get the full URL for the profile photo
     */
    private function getPhotoUrl(): ?string
    {
        if (empty($this->foto_profil)) {
            return null;
        }

        // If it's already a full URL (Cloudinary), return as-is
        if (str_starts_with($this->foto_profil, 'http://') || str_starts_with($this->foto_profil, 'https://')) {
            // Ensure HTTPS
            return str_replace('http://', 'https://', $this->foto_profil);
        }

        // Validate that foto_profil is a valid file path (not emoji or random text)
        // Valid paths should contain alphanumeric chars, underscores, hyphens, slashes, dots
        if (!preg_match('/^[\w\-\.\/]+$/', $this->foto_profil)) {
            // Invalid path format (could be emoji or garbage data), return null
            return null;
        }

        // Otherwise, it's a local storage path - convert to secure full URL
        return secure_asset('storage/' . $this->foto_profil);
    }
}
