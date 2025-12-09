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
            'foto_profil' => $this->foto_profil,
            'total_poin' => $this->total_poin,
            'poin_tercatat' => $this->poin_tercatat,
            'total_setor_sampah' => $this->total_setor_sampah,
            'level' => $this->level,
            'tipe_nasabah' => $this->tipe_nasabah,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
