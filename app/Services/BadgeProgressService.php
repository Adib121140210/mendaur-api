<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use App\Models\BadgeProgress;
use Illuminate\Support\Facades\DB;

class BadgeProgressService
{
    /**
     * Initialize or update progress for all badges for a user
     */
    public function syncUserBadgeProgress(User $user): void
    {
        $badges = Badge::all();

        foreach ($badges as $badge) {
            $this->updateBadgeProgress($user, $badge);
        }
    }

    /**
     * Update progress for a specific badge
     */
    public function updateBadgeProgress(User $user, Badge $badge): BadgeProgress
    {
        $progress = BadgeProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'badge_id' => $badge->id,
            ],
            [
                'current_value' => 0,
                'target_value' => $this->getTargetValue($badge),
                'progress_percentage' => 0,
            ]
        );

        $progress->updateProgress($user);

        return $progress;
    }

    /**
     * Get all badge progress for a user with filter
     */
    public function getUserBadgeProgress(User $user, string $filter = 'all'): array
    {
        // Ensure all badges have progress records
        $this->syncUserBadgeProgress($user);

        $query = BadgeProgress::with('badge')
            ->where('user_id', $user->id);

        // Apply filter
        switch ($filter) {
            case 'unlocked':
                $query->where('is_unlocked', true);
                break;
            case 'locked':
                $query->where('is_unlocked', false);
                break;
            case 'all':
            default:
                // No filter
                break;
        }

        $progressRecords = $query->orderByDesc('progress_percentage')
            ->orderBy('badge_id')
            ->get();

        // Get counts
        $allCount = BadgeProgress::where('user_id', $user->id)->count();
        $unlockedCount = BadgeProgress::where('user_id', $user->id)
            ->where('is_unlocked', true)
            ->count();
        $lockedCount = $allCount - $unlockedCount;

        // Generate message
        $message = match($filter) {
            'unlocked' => "Menampilkan {$unlockedCount} badge yang sudah didapat",
            'locked' => "Menampilkan {$lockedCount} badge yang belum didapat",
            default => "Menampilkan {$allCount} badge",
        };

        return [
            'data' => $progressRecords,
            'counts' => [
                'all' => $allCount,
                'unlocked' => $unlockedCount,
                'locked' => $lockedCount,
            ],
            'message' => $message,
        ];
    }

    /**
     * Get target value for a badge
     */
    private function getTargetValue(Badge $badge): int
    {
        return match($badge->tipe) {
            'poin' => $badge->syarat_poin ?? 0,
            'setor' => $badge->syarat_setor ?? 0,
            'kombinasi' => max($badge->syarat_poin ?? 0, $badge->syarat_setor ?? 0),
            'ranking' => $badge->syarat_poin ?? 0,
            default => 0,
        };
    }
}
