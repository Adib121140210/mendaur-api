<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model LogAktivitas - Tabel log_aktivitas
 *
 * Mencatat aktivitas user untuk timeline/history
 */
class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';
    protected $primaryKey = 'log_user_activity_id';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'user_id',         // ID pengguna
        'tipe_aktivitas',  // Tipe: setor_sampah, tukar_poin, badge_unlock, dll
        'deskripsi',       // Deskripsi aktivitas
        'poin_perubahan',  // Perubahan poin (+/-)
        'tanggal',         // Waktu aktivitas
    ];

    protected $casts = [
        'poin_perubahan' => 'integer',
        'tanggal' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Activity type constants
     */
    const TYPE_SETOR_SAMPAH = 'setor_sampah';
    const TYPE_TUKAR_POIN = 'tukar_poin';
    const TYPE_BADGE_UNLOCK = 'badge_unlock';
    const TYPE_POIN_BONUS = 'poin_bonus';
    const TYPE_LEVEL_UP = 'level_up';

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a new activity
     */
    public static function log($userId, $type, $description, $pointChange = 0)
    {
        return self::create([
            'user_id' => $userId,
            'tipe_aktivitas' => $type,
            'deskripsi' => $description,
            'poin_perubahan' => $pointChange,
            'tanggal' => now(),
        ]);
    }
}
