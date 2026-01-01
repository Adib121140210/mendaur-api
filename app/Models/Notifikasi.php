<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model Notifikasi - Tabel notifikasi
 *
 * Menyimpan notifikasi untuk pengguna
 */
class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';
    protected $primaryKey = 'notifikasi_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',       // ID penerima notifikasi
        'judul',         // Judul notifikasi
        'pesan',         // Isi pesan notifikasi
        'tipe',          // Tipe: info, success, warning, error
        'is_read',       // Sudah dibaca atau belum
        'related_id',    // ID entitas terkait (opsional)
        'related_type',  // Tipe entitas terkait (opsional)
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
}
