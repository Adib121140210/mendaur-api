<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriSampah extends Model
{
    use HasFactory;

    protected $table = 'kategori_sampah';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'icon',
        'warna',
        'is_active',
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
