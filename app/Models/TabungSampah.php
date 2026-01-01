<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model TabungSampah - Tabel tabung_sampah
 *
 * Menyimpan data penyetoran/pengumpulan sampah oleh nasabah
 */
class TabungSampah extends Model
{
    use HasFactory;

    protected $table = 'tabung_sampah';
    protected $primaryKey = 'tabung_sampah_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',              // ID nasabah yang menyetor
        'jadwal_penyetoran_id', // ID jadwal penyetoran yang dipilih
        'nama_lengkap',         // Nama lengkap penyetor
        'no_hp',                // Nomor HP penyetor
        'titik_lokasi',         // Lokasi penyetoran (koordinat/alamat)
        'jenis_sampah',         // Jenis sampah: Plastik, Kertas, Logam, dll
        'foto_sampah',          // URL foto sampah yang disetor
        'status',               // Status: pending, approved, rejected
        'berat_kg',             // Berat sampah dalam kilogram (diisi admin)
        'poin_didapat',         // Poin yang diberikan (dihitung otomatis)
    ];

    protected $casts = [
        'berat_kg' => 'decimal:2',
        'poin_didapat' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalPenyetoran::class, 'jadwal_penyetoran_id', 'jadwal_penyetoran_id');
    }

    public function poinTransaksi()
    {
        return $this->hasOne(PoinTransaksi::class, 'tabung_sampah_id', 'tabung_sampah_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
