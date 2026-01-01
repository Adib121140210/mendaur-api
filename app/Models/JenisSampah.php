<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model JenisSampah - Tabel jenis_sampah
 *
 * Menyimpan daftar jenis sampah beserta harga per kg
 */
class JenisSampah extends Model
{
    use HasFactory;

    protected $table = 'jenis_sampah';
    protected $primaryKey = 'jenis_sampah_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'kategori_sampah_id', // FK ke kategori sampah (Plastik, Kertas, dll)
        'nama_jenis',         // Nama spesifik: Botol PET, Kardus, dll
        'harga_per_kg',       // Harga beli per kg (untuk display/referensi)
        'satuan',             // Satuan: kg, pcs, dll
        'kode',               // Kode unik jenis sampah
        'is_active',          // Status aktif/nonaktif
    ];

    protected $casts = [
        'harga_per_kg' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category for this jenis sampah
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriSampah::class, 'kategori_sampah_id');
    }

    /**
     * Get full name with category
     */
    public function getFullNameAttribute()
    {
        return $this->kategori->nama_kategori . ' - ' . $this->nama_jenis;
    }

    /**
     * Scope to get only active types
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByKategori($query, $kategoriId)
    {
        return $query->where('kategori_sampah_id', $kategoriId);
    }

    /**
     * Format price for display
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->harga_per_kg, 0, ',', '.');
    }
}
