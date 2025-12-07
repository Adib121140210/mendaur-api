# 🔧 Badge Tracking Implementation - Developer Guide

**Date**: November 25, 2025  
**Target**: Complete badge tracking system for auto-unlock & progress monitoring  
**Implementation Level**: Backend + APIs  

---

## 📦 What We're Building

### Current State
- ✅ `badge_progress` table exists (tracks progress)
- ✅ `user_badges` table exists (records earned badges)
- ⚠️ No auto-tracking logic yet
- ⚠️ No API endpoints yet
- ⚠️ No dashboard integration yet

### End State (After Implementation)
- ✅ Auto-tracking when user deposits or gets points
- ✅ Auto-unlock when conditions are met
- ✅ Complete APIs for badge progress
- ✅ Leaderboard system
- ✅ Admin analytics
- ✅ Real-time notifications

---

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    USER ACTIONS                            │
│  (Setor, Poin Transaksi, Achievement, etc)                │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────┐
        │  BadgeTrackingService    │  ← LISTENER/TRIGGER
        │  - Track progress        │
        │  - Check unlock condition│
        │  - Create records        │
        └──────┬───────────────────┘
               │
        ┌──────┴──────────┬────────────────┐
        ▼                 ▼                ▼
   ┌─────────────┐ ┌──────────────┐ ┌──────────────┐
   │badge_progress│ │user_badges   │ │poin_transaksis
   │(tracking)   │ │(completed)   │ │(audit)
   └─────────────┘ └──────────────┘ └──────────────┘
        │
        └─────────────────┬──────────────────────┐
                          ▼                      ▼
                    ┌──────────────┐      ┌─────────────┐
                    │API Endpoints │      │Dashboard    │
                    │& Controllers │      │& Leaderboard
                    └──────────────┘      └─────────────┘
```

---

## 📝 Step 1: Create Service Class

### File: `app/Services/BadgeTrackingService.php`

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use App\Models\BadgeProgress;
use App\Models\UserBadge;
use App\Models\PoinTransaksis;
use Illuminate\Support\Facades\Log;

class BadgeTrackingService
{
    /**
     * Update badge progress for user
     * Call this after: setor_sampah created, poin changed, etc
     */
    public function updateUserBadgeProgress(User $user, $triggerType = 'generic')
    {
        try {
            // Get all badges
            $badges = Badge::all();
            
            foreach ($badges as $badge) {
                $this->updateBadgeProgress($user, $badge, $triggerType);
            }
            
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
        // Get or create progress record
        $progress = BadgeProgress::firstOrCreate(
            ['user_id' => $user->id, 'badge_id' => $badge->id],
            ['target_value' => $this->getTargetValue($badge)]
        );

        // Calculate current value based on badge type
        $currentValue = $this->calculateCurrentValue($user, $badge);
        
        // Update progress
        $progress->current_value = $currentValue;
        $progress->progress_percentage = min(100, 
            ($progress->target_value > 0) 
                ? ($currentValue / $progress->target_value) * 100
                : 0
        );

        // Check if should unlock
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
                // Return minimum of both percentages
                $poinProgress = ($user->total_poin ?? 0) / $badge->syarat_poin;
                $setorProgress = ($user->total_setor_sampah ?? 0) / $badge->syarat_setor;
                return min($poinProgress, $setorProgress) * 100;
            
            case 'special':
                // Custom logic for special badges
                return $this->calculateSpecialBadgeProgress($badge, $user);
            
            case 'ranking':
                // Calculate from leaderboard
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
                return 100; // Progress is percentage
            case 'special':
                return 100; // Progress is percentage
            case 'ranking':
                return 100; // Progress is percentage
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
                // Both conditions must be 100%
                return $progress->progress_percentage >= 100;
            
            case 'special':
                return $progress->current_value >= 100;
            
            case 'ranking':
                return $progress->current_value >= 100;
            
            default:
                return false;
        }
    }

    /**
     * Unlock badge for user
     */
    private function unlockBadge(User $user, Badge $badge, BadgeProgress $progress)
    {
        $progress->is_unlocked = true;
        $progress->unlocked_at = now();
        $progress->save();

        // Create user_badges record (if not exists)
        $userBadge = UserBadge::firstOrCreate(
            ['user_id' => $user->id, 'badge_id' => $badge->id],
            [
                'tanggal_dapat' => now(),
                'reward_claimed' => true
            ]
        );

        // Add reward poin to user
        if ($badge->reward_poin > 0) {
            $user->increment('total_poin', $badge->reward_poin);

            // Create audit trail in poin_transaksis
            PoinTransaksis::create([
                'user_id' => $user->id,
                'poin_didapat' => $badge->reward_poin,
                'sumber' => 'badge',
                'keterangan' => "Badge unlocked: {$badge->nama}",
                'referensi_id' => $badge->id,
                'referensi_tipe' => 'badge'
            ]);
        }

        // TODO: Send notification to user
        // NotificationService::badgeUnlocked($user, $badge);

        Log::info("Badge unlocked for user {$user->id}: {$badge->nama}");
    }

    /**
     * Calculate special badge progress (custom logic)
     */
    private function calculateSpecialBadgeProgress(Badge $badge, User $user)
    {
        // Example: Special badge for event participation
        // Logic depends on your specific events
        return 0; // To be customized
    }

    /**
     * Calculate ranking badge progress
     */
    private function calculateRankingProgress(Badge $badge, User $user)
    {
        // Get user's rank
        $userRank = User::query()
            ->selectRaw('COUNT(*) + 1 as rank')
            ->where('total_poin', '>', $user->total_poin)
            ->count() + 1;

        // Top 10 = 100% progress
        if ($userRank <= 10) {
            return 100 - ($userRank * 10); // 90-100%
        }
        
        // Others get partial progress
        return max(0, 50 - ($userRank / 100)); // 0-50%
    }

    /**
     * Initialize badge progress for new user
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
                    'progress_percentage' => 0,
                    'is_unlocked' => false
                ]
            );
        }
    }

    /**
     * Recalculate all users' badge progress (for cron job)
     */
    public function recalculateAllUserProgress()
    {
        $users = User::all();
        
        foreach ($users as $user) {
            $this->updateUserBadgeProgress($user, 'cron');
        }
        
        Log::info('Badge progress recalculated for all users');
    }

    /**
     * Get user badge summary for dashboard
     */
    public function getUserBadgeSummary(User $user)
    {
        return [
            'completed' => BadgeProgress::where('user_id', $user->id)
                ->where('is_unlocked', true)
                ->count(),
            
            'incomplete' => BadgeProgress::where('user_id', $user->id)
                ->where('is_unlocked', false)
                ->count(),
            
            'total_tracked' => BadgeProgress::where('user_id', $user->id)->count(),
            
            'average_progress' => BadgeProgress::where('user_id', $user->id)
                ->avg('progress_percentage'),
            
            'almost_complete' => BadgeProgress::where('user_id', $user->id)
                ->where('progress_percentage', '>=', 75)
                ->where('is_unlocked', false)
                ->count(),
            
            'total_reward' => UserBadge::where('user_id', $user->id)
                ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
                ->sum('badges.reward_poin')
        ];
    }
}
```

---

## 🎯 Step 2: Create Event Listeners

### File: `app/Listeners/UpdateBadgeProgressListener.php`

```php
<?php

namespace App\Listeners;

use App\Events\TabungSampahCreated;
use App\Events\PoinTransaksiCreated;
use App\Services\BadgeTrackingService;

class UpdateBadgeProgressListener
{
    protected $badgeService;

    public function __construct(BadgeTrackingService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * Handle tabung_sampah created event
     */
    public function handleTabungSampah(TabungSampahCreated $event)
    {
        $this->badgeService->updateUserBadgeProgress(
            $event->tabungSampah->user,
            'setor_sampah'
        );
    }

    /**
     * Handle poin_transaksi created event
     */
    public function handlePoinTransaksi(PoinTransaksiCreated $event)
    {
        $this->badgeService->updateUserBadgeProgress(
            $event->poinTransaksi->user,
            'poin_change'
        );
    }
}
```

### Register in: `app/Providers/EventServiceProvider.php`

```php
protected $listen = [
    TabungSampahCreated::class => [
        UpdateBadgeProgressListener::class,
    ],
    PoinTransaksiCreated::class => [
        UpdateBadgeProgressListener::class,
    ],
];
```

---

## 🔌 Step 3: Create API Controllers

### File: `app/Http/Controllers/Api/BadgeProgressController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\BadgeProgress;
use App\Models\UserBadge;
use App\Models\Badge;
use App\Services\BadgeTrackingService;
use Illuminate\Http\Request;

class BadgeProgressController extends Controller
{
    protected $badgeService;

    public function __construct(BadgeTrackingService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * GET /api/user/badges/progress
     * Get user's badge progress
     */
    public function getUserProgress(Request $request)
    {
        $user = $request->user();

        $completedBadges = UserBadge::where('user_id', $user->id)
            ->with('badge')
            ->get()
            ->map(fn($ub) => [
                'id' => $ub->id,
                'badge_id' => $ub->badge_id,
                'nama' => $ub->badge->nama,
                'tipe' => $ub->badge->tipe,
                'icon' => $ub->badge->icon,
                'reward_poin' => $ub->badge->reward_poin,
                'earned_date' => $ub->tanggal_dapat,
            ]);

        $inProgressBadges = BadgeProgress::where('user_id', $user->id)
            ->where('is_unlocked', false)
            ->with('badge')
            ->orderByDesc('progress_percentage')
            ->get()
            ->map(fn($bp) => [
                'id' => $bp->id,
                'badge_id' => $bp->badge_id,
                'nama' => $bp->badge->nama,
                'tipe' => $bp->badge->tipe,
                'icon' => $bp->badge->icon,
                'progress_percentage' => $bp->progress_percentage,
                'current_value' => $bp->current_value,
                'target_value' => $bp->target_value,
                'remaining' => $bp->target_value - $bp->current_value,
                'status' => $this->getProgressStatus($bp->progress_percentage),
            ]);

        $summary = $this->badgeService->getUserBadgeSummary($user);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'total_poin' => $user->total_poin,
                    'total_setor' => $user->total_setor_sampah,
                ],
                'summary' => $summary,
                'completed_badges' => $completedBadges,
                'in_progress_badges' => $inProgressBadges,
            ]
        ]);
    }

    /**
     * GET /api/user/badges/completed
     * Get only completed badges for user
     */
    public function getCompletedBadges(Request $request)
    {
        $user = $request->user();
        $badges = UserBadge::where('user_id', $user->id)
            ->with('badge')
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $badges->count(),
            'data' => $badges->map(fn($ub) => [
                'id' => $ub->id,
                'nama' => $ub->badge->nama,
                'tipe' => $ub->badge->tipe,
                'reward_poin' => $ub->badge->reward_poin,
                'earned_date' => $ub->tanggal_dapat,
            ])
        ]);
    }

    /**
     * GET /api/badges/leaderboard
     * Get top achievers leaderboard
     */
    public function getLeaderboard(Request $request)
    {
        $limit = $request->get('limit', 10);

        $leaderboard = User::query()
            ->withCount('userBadges')
            ->with(['userBadges' => function ($query) {
                $query->join('badges', 'user_badges.badge_id', '=', 'badges.id')
                    ->select('user_badges.user_id')
                    ->selectRaw('SUM(badges.reward_poin) as total_reward');
            }])
            ->orderByDesc('user_badges_count')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'rank' => $loop?->iteration ?? 1,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'foto_profil' => $user->foto_profil,
                ],
                'badges_earned' => $user->user_badges_count,
                'total_poin' => $user->total_poin,
                'reward_poin' => $user->userBadges->sum('reward_poin'),
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $leaderboard
        ]);
    }

    /**
     * GET /api/admin/badges/analytics
     * Admin analytics for badge system
     */
    public function getAnalytics()
    {
        $totalBadges = Badge::count();
        $totalEarned = UserBadge::count();
        
        $mostEarned = Badge::withCount('userBadges')
            ->orderByDesc('user_badges_count')
            ->limit(5)
            ->get()
            ->map(fn($b) => [
                'nama' => $b->nama,
                'earned_count' => $b->user_badges_count,
            ]);

        $rarest = Badge::withCount('userBadges')
            ->orderBy('user_badges_count')
            ->limit(5)
            ->get()
            ->map(fn($b) => [
                'nama' => $b->nama,
                'earned_count' => $b->user_badges_count,
            ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_badges' => $totalBadges,
                'total_earned' => $totalEarned,
                'total_users_tracking' => BadgeProgress::distinct('user_id')->count('user_id'),
                'most_earned_badges' => $mostEarned,
                'rarest_badges' => $rarest,
            ]
        ]);
    }

    /**
     * Helper to get progress status message
     */
    private function getProgressStatus($percentage)
    {
        if ($percentage >= 75) return 'ALMOST THERE';
        if ($percentage >= 50) return 'HALFWAY';
        if ($percentage >= 25) return 'ON TRACK';
        return 'JUST STARTED';
    }
}
```

---

## 🛣️ Step 4: Register Routes

### File: `routes/api.php`

```php
Route::middleware('auth:sanctum')->group(function () {
    // Badge endpoints
    Route::get('/user/badges/progress', [BadgeProgressController::class, 'getUserProgress']);
    Route::get('/user/badges/completed', [BadgeProgressController::class, 'getCompletedBadges']);
    Route::get('/badges/leaderboard', [BadgeProgressController::class, 'getLeaderboard']);
    
    // Admin
    Route::middleware('admin')->group(function () {
        Route::get('/admin/badges/analytics', [BadgeProgressController::class, 'getAnalytics']);
    });
});
```

---

## ⏰ Step 5: Create Cron Job

### File: `app/Console/Commands/RecalculateBadgeProgress.php`

```php
<?php

namespace App\Console\Commands;

use App\Services\BadgeTrackingService;
use Illuminate\Console\Command;

class RecalculateBadgeProgress extends Command
{
    protected $signature = 'badge:recalculate';
    protected $description = 'Recalculate badge progress for all users';

    public function handle(BadgeTrackingService $service)
    {
        $this->info('Recalculating badge progress...');
        $service->recalculateAllUserProgress();
        $this->info('Done!');
    }
}
```

### Register in: `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('badge:recalculate')->daily()->at('01:00');
}
```

---

## 🔗 Step 6: Update Models

### File: `app/Models/BadgeProgress.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeProgress extends Model
{
    protected $table = 'badge_progress';
    protected $fillable = [
        'user_id', 'badge_id', 'current_value', 'target_value',
        'progress_percentage', 'is_unlocked', 'unlocked_at'
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
        'unlocked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}
```

### Update: `app/Models/User.php`

```php
public function badgeProgress()
{
    return $this->hasMany(BadgeProgress::class);
}

public function userBadges()
{
    return $this->hasMany(UserBadge::class);
}
```

---

## ✅ Testing Checklist

```php
// Test 1: Initialize badges for new user
$user = User::create([...]);
app(BadgeTrackingService::class)->initializeUserBadges($user);
// Result: 15 badge_progress records created

// Test 2: Update progress when poin changes
$user->increment('total_poin', 500);
app(BadgeTrackingService::class)->updateUserBadgeProgress($user);
// Result: badge_progress.current_value updated for 'poin' type badges

// Test 3: Auto-unlock when condition met
$user->update(['total_poin' => 1000]);
app(BadgeTrackingService::class)->updateUserBadgeProgress($user);
// Result: 'Eco Hero' badge is_unlocked = true, user_badges record created

// Test 4: API endpoint returns correct data
GET /api/user/badges/progress
// Result: JSON with progress, completed, incomplete badges

// Test 5: Leaderboard shows top users
GET /api/badges/leaderboard
// Result: Top 10 users by badges earned
```

---

## 🚀 Deployment Steps

1. ✅ Create `BadgeTrackingService` class
2. ✅ Create event listeners
3. ✅ Create API controllers
4. ✅ Register routes
5. ✅ Update models with relationships
6. ✅ Create console command
7. ✅ Initialize badges for existing users:
   ```php
   User::all()->each(function($user) {
       app(BadgeTrackingService::class)->initializeUserBadges($user);
   });
   ```
8. ✅ Test all endpoints
9. ✅ Deploy and monitor

---

## 📊 Expected Results

After implementation, you should have:

1. ✅ Real-time badge progress tracking
2. ✅ Automatic badge unlock on condition met
3. ✅ Complete APIs for frontend integration
4. ✅ Leaderboard system
5. ✅ Admin analytics dashboard
6. ✅ Email/push notifications on unlock
7. ✅ Audit trail in poin_transaksis

---

**Implementation Time**: 4-6 hours  
**Complexity**: Medium-High  
**Status**: Ready for development

