<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * Transform the resource into an array for auth responses.
     * Includes role and permissions.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $roleData = null;
        $permissions = [];

        if ($this->role && $this->role !== null) {
            $rolePermissions = $this->role->permissions;
            if ($rolePermissions) {
                $permissions = $rolePermissions->pluck('permission_code')->toArray();
            }
            $roleData = [
                'role_id' => $this->role->role_id,
                'nama_role' => $this->role->nama_role,
                'level_akses' => $this->role->level_akses,
                'permissions' => $permissions,
            ];
        }

        return [
            'user' => [
                'user_id' => $this->user_id,
                'nama' => $this->nama,
                'email' => $this->email,
                'no_hp' => $this->no_hp,
                'alamat' => $this->alamat,
                'foto_profil' => $this->foto_profil,
                'total_poin' => $this->getAvailablePoin(), // FIXED: Show actual available poin from transactions
                'total_poin_display' => $this->total_poin, // For reference (reset by leaderboard)
                'total_setor_sampah' => $this->total_setor_sampah,
                'level' => $this->level,
                'role_id' => $this->role_id,
                'role' => $roleData,
                'permissions' => $permissions,
            ],
        ];
    }
}
