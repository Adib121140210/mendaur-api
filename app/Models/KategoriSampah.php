<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model KategoriSampah - Tabel kategori_sampah
 *
 * Menyimpan kategori utama sampah (Plastik, Kertas, Logam, dll)
 */
class KategoriSampah extends Model
{
    use HasFactory;

    protected $table = 'kategori_sampah';
    protected $primaryKey = 'kategori_sampah_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama_kategori', // Nama kategori: Plastik, Kertas, Logam, Kaca, Organik
        'deskripsi',     // Deskripsi kategori
        'icon',          // URL icon kategori
        'warna',         // Warna hex untuk UI (contoh: #FF5722)
        'is_active',     // Status aktif/nonaktif
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all jenis sampah for this category
     */
    public function jenisSampah()
    {
        return $this->hasMany(JenisSampah::class, 'kategori_sampah_id');
    }

    /**
     * Get active jenis sampah only
     */
    public function activeJenisSampah()
    {
        return $this->hasMany(JenisSampah::class, 'kategori_sampah_id')
                    ->where('is_active', true);
    }
}
