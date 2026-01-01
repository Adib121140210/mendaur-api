<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model JadwalPenyetoran - Tabel jadwal_penyetorans
 *
 * Menyimpan jadwal operasional bank sampah untuk penyetoran
 */
class JadwalPenyetoran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_penyetorans';
    protected $primaryKey = 'jadwal_penyetoran_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'hari',           // Hari operasional: Senin, Selasa, ..., Minggu
        'waktu_mulai',    // Jam buka (format HH:mm)
        'waktu_selesai',  // Jam tutup (format HH:mm)
        'lokasi',         // Lokasi bank sampah
        'status',         // Status: Buka, Tutup
    ];

    protected $casts = [
        // REMOVED: 'tanggal' => 'date',
        // REMOVED: 'kapasitas' => 'integer',
    ];

    /**
     * Valid hari values
     */
    public const HARI_OPTIONS = [
        'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'
    ];

    /**
     * Valid status values
     */
    public const STATUS_OPTIONS = ['Buka', 'Tutup'];

    // Relasi ke TabungSampah
    public function tabungSampah()
    {
        return $this->hasMany(TabungSampah::class, 'jadwal_penyetoran_id');
    }
}
