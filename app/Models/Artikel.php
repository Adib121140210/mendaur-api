<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Artikel - Tabel artikels
 *
 * Menyimpan artikel/berita tentang daur ulang dan lingkungan
 */
class Artikel extends Model
{
    use HasFactory;

    protected $table = 'artikels';
    protected $primaryKey = 'artikel_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'judul',                  // Judul artikel
        'slug',                   // URL-friendly slug dari judul
        'konten',                 // Isi artikel dalam HTML/markdown
        'foto_cover',             // URL foto cover artikel (Cloudinary URL atau local path)
        'foto_cover_public_id',   // Cloudinary public_id untuk menghapus foto
        'penulis',                // Nama penulis artikel
        'kategori',               // Kategori: tips, berita, edukasi, dll
        'tanggal_publikasi',      // Tanggal artikel dipublikasikan
        'views',                  // Jumlah view artikel
    ];

    protected $casts = [
        'tanggal_publikasi' => 'date',
        'views' => 'integer',
    ];
}
