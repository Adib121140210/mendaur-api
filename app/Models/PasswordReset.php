<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Model PasswordReset - Tabel password_resets
 *
 * Menyimpan token reset password dan OTP
 */
class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $fillable = [
        'email',        // Email user yang request reset
        'token',        // Token reset password
        'otp',          // OTP (6 digit) - plaintext untuk backward compat
        'otp_hash',     // OTP yang di-hash untuk keamanan
        'reset_token',  // Token untuk halaman reset password
        'expires_at',   // Waktu expired
        'verified_at',  // Waktu OTP diverifikasi
        'created_at',   // Waktu dibuat
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * NO LONGER HASH OTP IN MODEL - Handled by Service Layer
     * Keep OTP plaintext temporarily for backward compatibility
     * Will be removed in Phase 3 after stable
     */

    /**
     * Check if OTP is expired
     */
    public function isExpired()
    {
        return Carbon::now()->gt($this->expires_at);
    }

    /**
     * Check if OTP is verified
     */
    public function isVerified()
    {
        return !is_null($this->verified_at);
    }

    /**
     * Scope for active (non-expired) records
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope for verified records
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }
}
