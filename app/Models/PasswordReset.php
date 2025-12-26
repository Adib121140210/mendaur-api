<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PasswordReset extends Model
{
    protected $table = 'password_resets';
    protected $fillable = [
        'email',
        'token',
        'otp',
        'reset_token',
        'expires_at',
        'verified_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Automatically hash the OTP when storing
     */
    public function setOtpAttribute($value)
    {
        $this->attributes['otp'] = $value; // Store plain OTP for verification
    }

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
