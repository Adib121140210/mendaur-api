<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'no_hp',
        'alamat',
        'foto_profil',
        'foto_profil_public_id', // Cloudinary public_id for deletion
        'display_poin',      // For leaderboard ranking (can be reset)
        'actual_poin',       // For transactions/withdrawals (calculated from poin_transaksis)
        'total_setor_sampah',
        'level',
        'role_id',
        'status',
        'tipe_nasabah',
        'poin_tercatat',
        'nama_bank',
        'nomor_rekening',
        'atas_nama_rekening',
        'badge_title_id', // Selected badge to display as user title
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
            'tipe_nasabah' => 'string',
        ];
    }

    /**
     * Default attribute values
     * NOTE: nama_bank NOT set here - only Modern users should have banking info
     */
    protected $attributes = [
        'tipe_nasabah' => 'konvensional',
        'display_poin' => 0,     // For leaderboard display
        'actual_poin' => 0,      // Will be calculated from poin_transaksis
        'poin_tercatat' => 0,
        'total_setor_sampah' => 0,
    ];

    /**
     * Relationships
     */
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
        return $this->hasMany(TabungSampah::class);
    }

    public function penukaranProduk()
    {
        return $this->hasMany(PenukaranProduk::class);
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function badges()
    {
        return $this->belongsToMany(
            \App\Models\Badge::class,
            'user_badges',
            'user_id',
            'badge_id'
        )->withPivot('tanggal_dapat')->withTimestamps();
    }

    public function userBadges()
    {
        return $this->hasMany(\App\Models\UserBadge::class, 'user_id', 'user_id');
    }

    /**
     * Get the selected badge title for the user
     */
    public function badgeTitle()
    {
        return $this->belongsTo(\App\Models\Badge::class, 'badge_title_id', 'badge_id');
    }

    public function notifikasis()
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function logAktivitas()
    {
        return $this->hasMany(\App\Models\LogAktivitas::class);
    }

    public function badgeProgress()
    {
        return $this->hasMany(\App\Models\BadgeProgress::class);
    }

    public function getBadgeProgressFor(Badge $badge)
    {
        return $this->badgeProgress()
            ->where('badge_id', $badge->id)
            ->first();
    }

    public function penarikanTunai()
    {
        return $this->hasMany(\App\Models\PenarikanTunai::class);
    }

    public function poinTransaksis()
    {
        return $this->hasMany(\App\Models\PoinTransaksi::class, 'user_id', 'user_id');
    }

    /**
     * Accessor for legacy 'id' usage
     * Maps to the primary key 'user_id'
     */
    public function getIdAttribute()
    {
        return $this->user_id;
    }

    /**
     * Check if user is admin
     * Assuming 'level' field: 'admin' for admin users, 'user' for regular users
     */
    public function isAdmin()
    {
        return $this->level === 'admin';
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->nama_role === $roleName;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(...$roleNames): bool
    {
        if (!$this->role) {
            return false;
        }
        return in_array($this->role->nama_role, $roleNames);
    }

    /**
     * Check if user has a specific permission (via role)
     */
    public function hasPermission(string $permissionCode): bool
    {
        if (!$this->role) {
            return false;
        }

        // Get all inherited permissions for user's role
        $permissions = $this->role->getInheritedPermissions();
        return $permissions->contains('permission_code', $permissionCode);
    }

    /**
     * Check if user has all given permissions
     */
    public function hasAllPermissions(...$permissionCodes): bool
    {
        foreach ($permissionCodes as $code) {
            if (!$this->hasPermission($code)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(...$permissionCodes): bool
    {
        foreach ($permissionCodes as $code) {
            if ($this->hasPermission($code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all inherited permissions for this user's role
     */
    public function getAllPermissions()
    {
        if (!$this->role) {
            return collect();
        }
        return $this->role->getInheritedPermissions();
    }

    /**
     * Check if this user is konvensional nasabah
     */
    public function isNasabahKonvensional(): bool
    {
        return $this->tipe_nasabah === 'konvensional';
    }

    /**
     * Check if this user is modern nasabah
     */
    public function isNasabahModern(): bool
    {
        return $this->tipe_nasabah === 'modern';
    }

    /**
     * Get displayed poin based on nasabah type
     * - Konvensional: shows actual_poin (calculated from transactions)
     * - Modern: shows 0 (poin not directly usable for features)
     */
    public function getDisplayedPoin(): int
    {
        if ($this->isNasabahModern()) {
            return 0; // Modern nasabah cannot use poin for features
        }
        return (int)$this->actual_poin;
    }

    /**
     * Get actual usable poin balance
     * - Konvensional: actual_poin (calculated from transactions)
     * - Modern: always 0 (cannot use for features)
     */
    public function getActualPoinBalance(): int
    {
        if ($this->isNasabahModern()) {
            return 0;
        }
        return (int)$this->actual_poin;
    }

    /**
     * Get recorded poin (for audit trail and badges)
     * Both nasabah types use this for badges/leaderboard
     */
    public function getRecordedPoin(): int
    {
        return (int)$this->poin_tercatat;
    }

    /**
     * Check if user can use a specific poin feature
     */
    public function canUsePoinFeature(string $featureName): bool
    {
        // Modern nasabah cannot use poin features
        if ($this->isNasabahModern()) {
            return false;
        }

        // Konvensional nasabah can use if they have poin and permission
        if ($this->actual_poin <= 0) {
            return false;
        }

        // Check if user has permission for the feature
        $permissionMap = [
            'penarikan_tunai' => 'request_withdrawal',
            'penukaran_produk' => 'redeem_poin',
            'tabung_sampah' => 'deposit_sampah',
        ];

        if (!isset($permissionMap[$featureName])) {
            return true; // Unknown feature, allow by default
        }

        return $this->hasPermission($permissionMap[$featureName]);
    }

    /**
     * Increment recorded poin (tercatat) for audit trail
     */
    public function addPoinTercatat(int $amount, string $reason = ''): void
    {
        $this->increment('poin_tercatat', $amount);
    }

    /**
     * Add usable poin only for konvensional nasabah
     * This updates both display_poin (for leaderboard) and actual_poin (for transactions)
     */
    public function addUsablePoin(int $amount, string $reason = ''): void
    {
        if ($this->isNasabahKonvensional()) {
            // Update both display_poin and actual_poin
            $this->increment('display_poin', $amount);
            $this->increment('actual_poin', $amount);
        }
    }

    /**
     * Check if user is nasabah (level 1)
     */
    public function isNasabah(): bool
    {
        return $this->role && $this->role->level_akses === 1;
    }

    /**
     * Check if user is admin (level 2) or superadmin (level 3)
     */
    public function isAdminUser(): bool
    {
        return $this->role && ($this->role->level_akses === 2 || $this->role->level_akses === 3);
    }

    /**
     * Check if user is superadmin (level 3)
     */
    public function isSuperAdmin(): bool
    {
        return $this->role && $this->role->level_akses === 3;
    }

    /**
     * Check if user has admin privileges (admin or superadmin)
     */
    public function isStaff(): bool
    {
        return $this->isAdminUser() || $this->isSuperAdmin();
    }

    /**
     * Get actual available poin from poin_transaksis table
     * This is the real poin balance, regardless of display_poin field
     */
    public function getAvailablePoin(): int
    {
        return $this->poinTransaksis()->sum('poin_didapat') ?? 0;
    }

    /**
     * Update actual_poin field based on poin_transaksis data
     * This should be called when needed to sync the cache
     */
    public function updateActualPoin(): void
    {
        $this->actual_poin = $this->getAvailablePoin();
        $this->save();
    }

    /**
     * Get usable poin for konvensional nasabah
     * Modern nasabah always returns 0 (cannot use poin for withdrawal/exchange)
     */
    public function getUsablePoin(): int
    {
        if ($this->isNasabahModern()) {
            return 0; // Modern nasabah cannot use poin
        }

        return $this->getAvailablePoin();
    }

    /**
     * Check if user has enough poin for transaction
     */
    public function hasEnoughPoin(int $requiredPoin): bool
    {
        return $this->getUsablePoin() >= $requiredPoin;
    }
}

