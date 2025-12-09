<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'total_poin' => $this->total_poin,
            'level' => $this->level,
            'foto_profil' => $this->foto_profil,
            'total_setor_sampah' => $this->total_setor_sampah,
        ];
    }
}
