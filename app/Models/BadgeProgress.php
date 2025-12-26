<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeProgress extends Model
{
    protected $table = 'badge_progress';
    protected $primaryKey = 'badge_progress_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'badge_id',
        'current_value',
        'target_value',
        'progress_percentage',
        'is_unlocked',
        'unlocked_at',
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'is_unlocked' => 'boolean',
        'unlocked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class, 'badge_id', 'badge_id');
    }

    /**
     * Update progress based on current user stats
     */
    public function updateProgress(User $user): void
    {
        $badge = $this->badge;

        // Determine current value based on badge type
        $currentValue = match($badge->tipe) {
            'poin' => $user->total_poin,
            'setor' => $user->total_setor_sampah,
            'kombinasi' => min($user->total_poin, $user->total_setor_sampah),
            'ranking' => $this->calculateRankingProgress($user),
            default => 0,
        };

        // Determine target value
        $targetValue = match($badge->tipe) {
            'poin' => $badge->syarat_poin ?? 0,
            'setor' => $badge->syarat_setor ?? 0,
            'kombinasi' => max($badge->syarat_poin ?? 0, $badge->syarat_setor ?? 0),
            'ranking' => $badge->syarat_poin ?? 0, // Use syarat_poin for rank requirement
            default => 0,
        };

        // Calculate percentage
        $percentage = $targetValue > 0 ? min(($currentValue / $targetValue) * 100, 100) : 0;

        // Check if unlocked
        $isUnlocked = $percentage >= 100;

        $this->update([
            'current_value' => $currentValue,
            'target_value' => $targetValue,
            'progress_percentage' => round($percentage, 2),
            'is_unlocked' => $isUnlocked,
            'unlocked_at' => $isUnlocked && !$this->unlocked_at ? now() : $this->unlocked_at,
        ]);
    }

    /**
     * Calculate ranking progress for ranking badges
     * Returns the user's current rank
     */
    private function calculateRankingProgress(User $user): int
    {
        // Get user's rank (1-indexed)
        $rank = User::where('total_poin', '>', $user->total_poin)->count() + 1;
        return $rank;
    }
}
