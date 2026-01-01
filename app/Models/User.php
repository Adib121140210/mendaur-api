<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model User - Tabel users
 *
 * Menyimpan data pengguna aplikasi (nasabah, admin, superadmin)
 */
class User extends Authenticatable
{
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * Kolom yang dapat diisi secara mass assignment
     */
    protected $fillable = [
        // === DATA PRIBADI ===
        'nama',                    // Nama lengkap pengguna
        'email',                   // Email untuk login (unique)
        'password',                // Password terenkripsi
        'no_hp',                   // Nomor handphone
        'alamat',                  // Alamat lengkap pengguna

        // === FOTO PROFIL ===
        'foto_profil',             // URL foto profil di Cloudinary
        'foto_profil_public_id',   // Public ID Cloudinary untuk hapus/update foto

        // === SISTEM POIN (DUAL-POINT SYSTEM) ===
        'display_poin',            // Poin untuk LEADERBOARD/ranking (tidak berkurang saat transaksi)
        'actual_poin',             // Poin SALDO untuk transaksi (penukaran produk, withdrawal)

        // === STATISTIK ===
        'total_setor_sampah',      // Total kg sampah yang sudah disetor (approved)

        // === ROLE & STATUS ===
        'level',                   // Level user: 1=nasabah, 2=admin, 3=superadmin
        'role_id',                 // Foreign key ke tabel roles
        'status',                  // Status akun: active, suspended, inactive
        'tipe_nasabah',            // Tipe: 'konvensional' atau 'modern'

        // === DATA BANK (untuk withdrawal - hanya nasabah modern) ===
        'nama_bank',               // Nama bank (BCA, BNI, Mandiri, dll)
        'nomor_rekening',          // Nomor rekening bank
        'atas_nama_rekening',      // Nama pemilik rekening

        // === BADGE ===
        'badge_title_id',          // Badge yang dipilih sebagai title/gelar
    ];

    /**
     * Kolom yang disembunyikan dari JSON response
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting tipe data
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
            'tipe_nasabah' => 'string',
            'display_poin' => 'integer',
            'actual_poin' => 'integer',
            'total_setor_sampah' => 'decimal:2',
        ];
    }

    /**
     * Nilai default untuk kolom
     */
    protected $attributes = [
        'tipe_nasabah' => 'konvensional',
        'display_poin' => 0,
        'actual_poin' => 0,
        'total_setor_sampah' => 0,
        'status' => 'active',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'admin_id', 'user_id');
    }

    public function tabungSampahs()
    {
        return $this->hasMany(TabungSampah::class, 'user_id', 'user_id');
    }

    public function penukaranProduk()
    {
        return $this->hasMany(PenukaranProduk::class, 'user_id', 'user_id');
    }

    public function penarikanTunai()
    {
        return $this->hasMany(PenarikanTunai::class, 'user_id', 'user_id');
    }

    public function poinTransaksis()
    {
        return $this->hasMany(PoinTransaksi::class, 'user_id', 'user_id');
    }

    public function badges()
    {
        return $this->belongsToMany(
            Badge::class,
            'user_badges',
            'user_id',
            'badge_id'
        )->withPivot('tanggal_dapat')->withTimestamps();
    }

    public function userBadges()
    {
        return $this->hasMany(UserBadge::class, 'user_id', 'user_id');
    }

    public function badgeTitle()
    {
        return $this->belongsTo(Badge::class, 'badge_title_id', 'badge_id');
    }

    public function badgeProgress()
    {
        return $this->hasMany(BadgeProgress::class, 'user_id', 'user_id');
    }

    public function notifikasis()
    {
        return $this->hasMany(Notifikasi::class, 'user_id', 'user_id');
    }

    public function logAktivitas()
    {
        return $this->hasMany(LogAktivitas::class, 'user_id', 'user_id');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Cek apakah user adalah admin atau superadmin
     */
    public function isAdminUser(): bool
    {
        return in_array($this->level, [2, 3]) ||
               in_array($this->role?->nama_role, ['admin', 'superadmin']);
    }

    /**
     * Cek apakah user adalah superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->level === 3 || $this->role?->nama_role === 'superadmin';
    }

    /**
     * Cek apakah nasabah tipe modern (bisa withdrawal)
     */
    public function isModernNasabah(): bool
    {
        return $this->tipe_nasabah === 'modern';
    }

    /**
     * Dapatkan poin yang bisa digunakan untuk transaksi
     * Nasabah modern menggunakan actual_poin
     * Nasabah konvensional tidak bisa transaksi poin
     */
    public function getUsablePoin(): int
    {
        if ($this->tipe_nasabah === 'modern') {
            return $this->actual_poin ?? 0;
        }
        return 0;
    }

    /**
     * Accessor untuk backward compatibility
     */
    public function getIdAttribute()
    {
        return $this->user_id;
    }
}
