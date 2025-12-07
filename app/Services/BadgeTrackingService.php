<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use App\Models\BadgeProgress;
use App\Models\UserBadge;
use App\Models\PoinTransaksi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BadgeTrackingService
{
    /**
     * Update badge progress for user - DUAL-NASABAH AWARE
     */
    public function updateUserBadgeProgress(User $user, $triggerType = 'generic')
    {
        try {
            $badges = Badge::all();
            foreach ($badges as $badge) {
                $this->updateBadgeProgress($user, $badge, $triggerType);
            }
            Log::info("Badge progress updated for user {$user->id} (trigger: {$triggerType})");
            return true;
        } catch (\Exception $e) {
            Log::error('Badge tracking error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update progress for specific badge
     */
    public function updateBadgeProgress(User $user, Badge $badge, $triggerType = 'generic')
    {
        $progress = BadgeProgress::firstOrCreate(
            ['user_id' => $user->id, 'badge_id' => $badge->id],
            [
                'current_value' => 0,
                'target_value' => $this->getTargetValue($badge),
                'progress_percentage' => 0.00,
                'is_unlocked' => false
            ]
        );

        $currentValue = $this->calculateCurrentValue($user, $badge);
        $progress->current_value = $currentValue;
        $progress->progress_percentage = min(100,
            ($progress->target_value > 0)
                ? ($currentValue / $progress->target_value) * 100
                : 0
        );

        if (!$progress->is_unlocked && $this->shouldUnlock($badge, $progress)) {
            $this->unlockBadge($user, $badge, $progress);
        }

        $progress->save();
        return $progress;
    }

    /**
     * Calculate current value based on badge type
     */
    private function calculateCurrentValue(User $user, Badge $badge)
    {
        switch ($badge->tipe) {
            case 'poin':
                return $user->total_poin ?? 0;
            case 'setor':
                return $user->total_setor_sampah ?? 0;
            case 'kombinasi':
                $poinPercent = ($user->total_poin ?? 0) / max(1, $badge->syarat_poin);
                $setorPercent = ($user->total_setor_sampah ?? 0) / max(1, $badge->syarat_setor);
                return min($poinPercent, $setorPercent) * 100;
            case 'special':
                return $this->calculateSpecialBadgeProgress($badge, $user);
            case 'ranking':
                return $this->calculateRankingProgress($badge, $user);
            default:
                return 0;
        }
    }

    /**
     * Get target value for badge
     */
    private function getTargetValue(Badge $badge)
    {
        switch ($badge->tipe) {
            case 'poin':
                return $badge->syarat_poin ?? 0;
            case 'setor':
                return $badge->syarat_setor ?? 0;
            case 'kombinasi':
            case 'special':
            case 'ranking':
                return 100;
            default:
                return 0;
        }
    }

    /**
     * Check if should unlock badge
     */
    private function shouldUnlock(Badge $badge, BadgeProgress $progress)
    {
        switch ($badge->tipe) {
            case 'poin':
                return $progress->current_value >= $badge->syarat_poin;
            case 'setor':
                return $progress->current_value >= $badge->syarat_setor;
            case 'kombinasi':
            case 'special':
            case 'ranking':
                return $progress->progress_percentage >= 100;
            default:
                return false;
        }
    }

    /**
     * Unlock badge - DUAL-NASABAH AWARE
     * Konvensional: reward → total_poin (usable)
     * Modern: reward → poin_tercatat (non-usable)
     */
    private function unlockBadge(User $user, Badge $badge, BadgeProgress $progress)
    {
        DB::transaction(function() use ($user, $badge, $progress) {
            $progress->is_unlocked = true;
            $progress->unlocked_at = now();
            $progress->save();

            UserBadge::firstOrCreate(
                ['user_id' => $user->id, 'badge_id' => $badge->id],
                [
                    'tanggal_dapat' => now(),
                    'reward_claimed' => true
                ]
            );

            $poinType = 'none';
            if ($badge->reward_poin > 0) {
                if ($user->isNasabahKonvensional()) {
                    $user->increment('total_poin', $badge->reward_poin);
                    $poinType = 'usable';
                } else {
                    $user->increment('poin_tercatat', $badge->reward_poin);
                    $poinType = 'recorded';
                }

                PoinTransaksi::create([
                    'user_id' => $user->id,
                    'poin_didapat' => $badge->reward_poin,
                    'sumber' => 'badge',
                    'keterangan' => "Badge unlocked: {$badge->nama} ({$poinType})",
                    'referensi_id' => $badge->id,
                    'referensi_tipe' => 'badge',
                    'is_usable' => $user->isNasabahKonvensional(),
                    'reason_not_usable' => $user->isNasabahModern() ? 'modern_nasabah_type' : null
                ]);
            }

            Log::info("Badge unlocked for user {$user->id}: {$badge->nama} (reward: {$badge->reward_poin}, type: {$poinType})");
        });
    }

    /**
     * Initialize badges for new user
     */
    public function initializeUserBadges(User $user)
    {
        $badges = Badge::all();
        foreach ($badges as $badge) {
            BadgeProgress::firstOrCreate(
                ['user_id' => $user->id, 'badge_id' => $badge->id],
                [
                    'current_value' => 0,
                    'target_value' => $this->getTargetValue($badge),
                    'progress_percentage' => 0.00,
                    'is_unlocked' => false
                ]
            );
        }
        Log::info("Badge progress initialized for user {$user->id}");
    }

    /**
     * Recalculate all users' badge progress
     */
    public function recalculateAllUserProgress()
    {
        $users = User::all();
        $count = 0;
        foreach ($users as $user) {
            $this->updateUserBadgeProgress($user, 'cron');
            $count++;
        }
        Log::info("Badge progress recalculated for {$count} users");
        return $count;
    }

    /**
     * Get user badge summary
     */
    public function getUserBadgeSummary(User $user)
    {
        $badgeProgress = BadgeProgress::where('user_id', $user->id)->get();
        $completed = $badgeProgress->where('is_unlocked', true)->count();
        $incomplete = $badgeProgress->where('is_unlocked', false)->count();
        $avgProgress = $badgeProgress->avg('progress_percentage') ?? 0;
        $almostComplete = $badgeProgress
            ->where('progress_percentage', '>=', 75)
            ->where('is_unlocked', false)
            ->count();
        $totalReward = UserBadge::where('user_id', $user->id)
            ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
            ->sum('badges.reward_poin') ?? 0;

        return [
            'completed' => $completed,
            'incomplete' => $incomplete,
            'total_tracked' => $badgeProgress->count(),
            'average_progress_percentage' => round($avgProgress, 2),
            'almost_complete' => $almostComplete,
            'total_reward_poin' => $totalReward,
        ];
    }

    /**
     * Get user's badge progress details
     */
    public function getUserBadgeDetails(User $user)
    {
        $completedBadges = UserBadge::where('user_id', $user->id)
            ->with('badge')
            ->get()
            ->map(fn($ub) => [
                'id' => $ub->id,
                'badge_id' => $ub->badge_id,
                'nama' => $ub->badge->nama,
                'reward_poin' => $ub->badge->reward_poin,
                'earned_date' => $ub->tanggal_dapat?->format('Y-m-d H:i:s'),
            ]);

        $inProgressBadges = BadgeProgress::where('user_id', $user->id)
            ->where('is_unlocked', false)
            ->with('badge')
            ->orderByDesc('progress_percentage')
            ->get()
            ->map(fn($bp) => [
                'id' => $bp->id,
                'nama' => $bp->badge->nama,
                'progress_percentage' => round($bp->progress_percentage, 2),
                'remaining' => max(0, $bp->target_value - $bp->current_value),
            ]);

        return [
            'completed_badges' => $completedBadges,
            'in_progress_badges' => $inProgressBadges,
        ];
    }

    private function calculateSpecialBadgeProgress(Badge $badge, User $user)
    {
        return 0;
    }

    private function calculateRankingProgress(Badge $badge, User $user)
    {
        $userRank = User::query()
            ->where('total_poin', '>', $user->total_poin)
            ->count() + 1;

        if ($userRank <= 10) {
            return 100 - (($userRank - 1) * 10);
        }

        return max(0, 50 - ($userRank / 100));
    }
}
