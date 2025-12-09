<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabungSampah extends Model
{
    use HasFactory;

    protected $table = 'tabung_sampah';

    protected $fillable = [
        'user_id',
        'jadwal_id',
        'nama_lengkap',
        'no_hp',
        'titik_lokasi',
        'jenis_sampah',
        'foto_sampah',
        'status',
        'berat_kg',
        'poin_didapat',
    ];

    protected $casts = [
        'berat_kg' => 'decimal:2',
        'poin_didapat' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke JadwalPenyetoran
    public function jadwal()
    {
        return $this->belongsTo(JadwalPenyetoran::class, 'jadwal_id');
    }
}
