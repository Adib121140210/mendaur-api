<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks';

    protected $fillable = [
        'nama',
        'deskripsi',
        'harga_poin',
        'stok',
        'kategori',
        'foto',
        'status',
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
