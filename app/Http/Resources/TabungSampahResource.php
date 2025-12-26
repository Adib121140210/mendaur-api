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
            'foto_sampah' => $this->foto_sampah,
            'nama_lengkap' => $this->nama_lengkap,
            'no_hp' => $this->no_hp,
            'titik_lokasi' => $this->titik_lokasi,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
