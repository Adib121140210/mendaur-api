<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    public $timestamps = false; // Using custom 'created_at' field

    protected $fillable = [
        'user_id',
        'tipe_aktivitas',
        'deskripsi',
        'poin_perubahan',
        'tanggal',
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
