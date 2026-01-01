<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Produk - Tabel produks
 *
 * Menyimpan data produk yang bisa ditukar dengan poin
 */
class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks';
    protected $primaryKey = 'produk_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama',        // Nama produk
        'deskripsi',   // Deskripsi produk
        'harga_poin',  // Harga dalam satuan poin
        'stok',        // Jumlah stok tersedia
        'kategori',    // Kategori produk
        'foto',        // URL foto produk
        'status',      // Status: tersedia, habis, nonaktif
    ];

    protected $casts = [
        'harga_poin' => 'integer',
        'stok' => 'integer',
    ];

    /**
     * Relationship to transactions
     */
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'produk_id');
    }
}
