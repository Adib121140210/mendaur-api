<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenukaranProdukResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->penukaran_produk_id,
            'produk' => [
                'id' => $this->produk?->produk_id,
                'nama' => $this->produk?->nama_produk,
                'deskripsi' => $this->produk?->deskripsi,
                'foto' => $this->produk?->gambar,
                'harga_poin' => $this->produk?->harga_poin,
            ],
            'poin_digunakan' => $this->poin_digunakan,
            'jumlah' => $this->jumlah,
            'metode_ambil' => $this->metode_ambil,
            'metode_label' => $this->metode_ambil === 'pickup' ? 'Ambil di Lokasi' : 'Pengiriman',
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'tanggal_penukaran' => $this->created_at?->format('Y-m-d'),
            'tanggal_diambil' => $this->tanggal_diambil?->format('Y-m-d'),
            'catatan_admin' => $this->catatan,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * Get human-readable status label
     */
    private function getStatusLabel(): string
    {
        $labels = [
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'completed' => 'Selesai',
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
