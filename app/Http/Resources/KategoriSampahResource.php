<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KategoriSampahResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'kategori_sampah_id' => $this->kategori_sampah_id,
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'gambar' => $this->gambar,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
