<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JenisSampahResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'jenis_sampah_id' => $this->jenis_sampah_id,
            'kategori_sampah_id' => $this->kategori_sampah_id,
            'nama' => $this->nama,
            'harga_per_kg' => $this->harga_per_kg,
            'deskripsi' => $this->deskripsi,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
