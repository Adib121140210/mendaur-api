<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPenyetoran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_penyetorans';
    protected $primaryKey = 'jadwal_penyetoran_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'hari',           // CHANGED: from 'tanggal' to 'hari'
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'status',         // UPDATED: enum 'Buka', 'Tutup'
        // REMOVED: 'kapasitas'
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
