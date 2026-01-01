<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model PenarikanTunai - Tabel penarikan_tunai
 *
 * Menyimpan data permintaan penarikan tunai/withdrawal (hanya nasabah modern)
 */
class PenarikanTunai extends Model
{
    use HasFactory;

    protected $table = 'penarikan_tunai';
    protected $primaryKey = 'penarikan_tunai_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',        // ID nasabah yang request
        'jumlah_poin',    // Jumlah poin yang ditarik
        'jumlah_rupiah',  // Nilai rupiah (konversi dari poin)
        'nomor_rekening', // Nomor rekening tujuan
        'nama_bank',      // Nama bank tujuan
        'nama_penerima',  // Nama penerima di rekening
        'status',         // Status: pending, approved, rejected
        'catatan_admin',  // Catatan dari admin (alasan reject, dll)
        'processed_by',   // ID admin yang memproses
        'processed_at',   // Waktu diproses
    ];

    protected $casts = [
        'jumlah_poin' => 'integer',
        'jumlah_rupiah' => 'decimal:2',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by', 'user_id');
    }

    /**
     * Query Scopes
     */
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
