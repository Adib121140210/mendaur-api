<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model KategoriTransaksi - Tabel kategori_transaksi
 *
 * [LEGACY] Kategori untuk tabel transaksis
 */
class KategoriTransaksi extends Model
{
    use HasFactory;

    protected $table = 'kategori_transaksi';
    protected $primaryKey = 'kategori_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama', // Nama kategori
        'slug', // URL-friendly slug
    ];

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'kategori_id');
    }
}
