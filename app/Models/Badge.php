<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Badge - Tabel badges
 *
 * Menyimpan data badge/achievement yang bisa didapat pengguna
 */
class Badge extends Model
{
    protected $table = 'badges';
    protected $primaryKey = 'badge_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama',          // Nama badge (contoh: "Pemula Hijau", "Master Daur Ulang")
        'deskripsi',     // Deskripsi cara mendapatkan badge
        'icon',          // URL icon badge
        'syarat_poin',   // Minimal poin untuk unlock badge (untuk tipe 'poin')
        'syarat_setor',  // Minimal kg sampah untuk unlock badge (untuk tipe 'setor')
        'reward_poin',   // Bonus poin yang didapat saat unlock badge
        'tipe',          // Tipe badge: 'poin', 'setor', 'kombinasi', 'ranking'
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_badges',
            'badge_id',
            'user_id'
        )->withPivot('tanggal_dapat')->withTimestamps();
    }

    public function userBadges()
    {
        return $this->hasMany(UserBadge::class, 'badge_id', 'badge_id');
    }

    public function badgeProgress()
    {
        return $this->hasMany(BadgeProgress::class, 'badge_id', 'badge_id');
    }

    /**
     * Accessor untuk backward compatibility
     */
    public function getIdAttribute()
    {
        return $this->badge_id;
    }
}
