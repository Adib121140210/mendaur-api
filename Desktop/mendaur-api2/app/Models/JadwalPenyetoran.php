<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPenyetoran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_penyetorans';

    protected $fillable = [
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // Relasi ke TabungSampah
    public function tabungSampah()
    {
        return $this->hasMany(TabungSampah::class, 'jadwal_id');
    }
}
