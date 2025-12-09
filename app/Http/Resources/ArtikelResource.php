<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtikelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'artikel_id' => $this->artikel_id,
            'judul' => $this->judul,
            'konten' => $this->konten,
            'gambar' => $this->gambar,
            'kategori' => $this->kategori,
            'dilihat' => $this->dilihat,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
