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
            'foto' => $this->foto,
            'harga_poin' => $this->harga_poin,
            'stok' => $this->stok,
            'kategori' => $this->kategori,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
