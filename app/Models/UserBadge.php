<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model UserBadge - Tabel user_badges (Pivot Table)
 *
 * Menyimpan relasi antara user dan badge yang sudah didapat
 */
class UserBadge extends Model
{
    use HasFactory;

    protected $table = 'user_badges';
    protected $primaryKey = 'user_badge_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',        // ID pengguna
        'badge_id',       // ID badge yang didapat
        'tanggal_dapat',  // Tanggal badge didapat
        'reward_claimed', // Apakah reward poin sudah diklaim
    ];

    protected $casts = [
        'tanggal_dapat' => 'datetime',
        'reward_claimed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the badge
     */
    public function badge()
    {
        return $this->belongsTo(Badge::class, 'badge_id', 'badge_id');
    }

    /**
     * Backward compatibility: access 'id' as alias for 'user_badge_id'
     */
    public function getIdAttribute()
    {
        return $this->user_badge_id;
    }

    /**
     * Scope: Get badges earned between dates
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_dapat', [$startDate, $endDate]);
    }

    /**
     * Scope: Recently earned badges
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('tanggal_dapat', '>=', now()->subDays($days))
                     ->orderByDesc('tanggal_dapat');
    }
}
