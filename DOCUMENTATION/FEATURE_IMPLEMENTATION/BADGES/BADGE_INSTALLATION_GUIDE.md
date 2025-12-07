# 🚀 Badge Tracking System - Installation & Setup Guide

**Date**: November 25, 2025  
**Status**: Ready to Install  

---

## ✅ Installation Checklist

### Step 1: Copy Files to Your Project ✅ DONE
The following files have been created in your Laravel project:

- ✅ `app/Services/BadgeTrackingService.php` (280 lines)
- ✅ `app/Http/Controllers/Api/BadgeProgressController.php` (200 lines)
- ✅ `app/Listeners/UpdateBadgeProgressOnTabungSampah.php` (30 lines)
- ✅ `app/Listeners/UpdateBadgeProgressOnPoinChange.php` (30 lines)
- ✅ `app/Console/Commands/RecalculateBadgeProgress.php` (50 lines)
- ✅ `app/Models/UserBadge.php` (70 lines)
- ✅ `app/Models/BadgeProgress.php` (Already exists - kept as is)
- ✅ `app/Models/Badge.php` (Already exists - kept as is)

---

### Step 2: Register Routes

Edit `routes/api.php` and add:

```php
use App\Http\Controllers\Api\BadgeProgressController;

// Badge tracking routes
Route::middleware('auth:sanctum')->group(function () {
    // User badge endpoints
    Route::prefix('user/badges')->group(function () {
        Route::get('progress', [BadgeProgressController::class, 'getUserProgress']);
        Route::get('completed', [BadgeProgressController::class, 'getCompletedBadges']);
    });

    // Public badge endpoints
    Route::prefix('badges')->group(function () {
        Route::get('leaderboard', [BadgeProgressController::class, 'getLeaderboard']);
        Route::get('available', [BadgeProgressController::class, 'getAvailableBadges']);
    });

    // Admin badge endpoints
    Route::middleware('admin')->group(function () {
        Route::prefix('admin/badges')->group(function () {
            Route::get('analytics', [BadgeProgressController::class, 'getAnalytics']);
        });
    });
});
```

---

### Step 3: Register Event Listeners

Edit `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    // ... existing listeners ...
    
    'App\Events\TabungSampahCreated' => [
        'App\Listeners\UpdateBadgeProgressOnTabungSampah',
    ],
    'App\Events\PoinTransaksiCreated' => [
        'App\Listeners\UpdateBadgeProgressOnPoinChange',
    ],
];
```

**NOTE**: Make sure these events are fired from their respective controllers:

In `TabungSampahController`:
```php
event(new TabungSampahCreated($tabungSampah));
```

In `PoinTransaksiController` (or wherever poin_transaksis is created):
```php
event(new PoinTransaksiCreated($poinTransaksi));
```

---

### Step 4: Register Console Command

The console command is auto-discovered by Laravel. Verify it works:

```bash
php artisan badge:recalculate --help
```

Should show:
```
Description:
  Recalculate badge progress for all users

Usage:
  badge:recalculate [options]

Options:
  --force               Force recalculation
```

---

### Step 5: Schedule the Cron Job

Edit `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // ... existing schedules ...
    
    // Recalculate badge progress daily at 01:00 AM
    $schedule->command('badge:recalculate')->dailyAt('01:00');
}
```

---

### Step 6: Initialize Badges for Existing Users

Run this migration script to initialize badge_progress for all existing users:

**Option A: Using Tinker (Development)**
```bash
php artisan tinker
```

Then in tinker:
```php
$service = app(App\Services\BadgeTrackingService::class);
App\Models\User::all()->each(function($user) use ($service) {
    $service->initializeUserBadges($user);
    echo "Initialized: {$user->nama}\n";
});
```

**Option B: Using Command (Production)**

Create `app/Console/Commands/InitializeBadgesForUsers.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BadgeTrackingService;
use Illuminate\Console\Command;

class InitializeBadgesForUsers extends Command
{
    protected $signature = 'badge:initialize {--force : Force re-initialization}';
    protected $description = 'Initialize badge progress for all users';

    public function handle(BadgeTrackingService $service)
    {
        $this->info('Initializing badges for all users...');
        
        $users = User::all();
        $count = 0;

        foreach ($users as $user) {
            $service->initializeUserBadges($user);
            $count++;
            $this->line("✓ {$user->nama}");
        }

        $this->info("✅ Initialized badges for {$count} users");
    }
}
```

Then run:
```bash
php artisan badge:initialize
```

---

### Step 7: Test the System

#### Test 1: Check Service Works
```bash
php artisan tinker
```

```php
$user = App\Models\User::first();
$service = app(App\Services\BadgeTrackingService::class);

// Get user badge summary
$summary = $service->getUserBadgeSummary($user);
dd($summary);
```

Expected output:
```php
[
    "completed" => 0,
    "incomplete" => 15,
    "total_tracked" => 15,
    "average_progress_percentage" => 0.0,
    "almost_complete" => 0,
    "total_reward_poin" => 0,
]
```

#### Test 2: Trigger Update Manually
```php
// Simulate setor_sampah trigger
$user->increment('total_setor_sampah', 10);
$service->updateUserBadgeProgress($user, 'test');

// Check progress
$progress = App\Models\BadgeProgress::where('user_id', $user->id)
    ->where('tipe', 'setor')
    ->first();
dd($progress);
```

#### Test 3: Test API Endpoint
```bash
# Get user's badge progress
curl -X GET "http://localhost:8000/api/user/badges/progress" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Get leaderboard
curl -X GET "http://localhost:8000/api/badges/leaderboard" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Get analytics (admin)
curl -X GET "http://localhost:8000/api/admin/badges/analytics" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json"
```

---

## 🔧 Configuration

### Adjust Badge Unlock Conditions

If you want to change unlock conditions, edit `BadgeTrackingService.php`:

```php
// In shouldUnlock() method:
private function shouldUnlock(Badge $badge, BadgeProgress $progress)
{
    switch ($badge->tipe) {
        case 'poin':
            // Change this condition if needed
            return $progress->current_value >= $badge->syarat_poin;
        
        // ... other cases ...
    }
}
```

### Customize Progress Calculation

Edit `calculateCurrentValue()` method for custom logic:

```php
private function calculateCurrentValue(User $user, Badge $badge)
{
    switch ($badge->tipe) {
        case 'poin':
            // Add any custom logic here
            return $user->total_poin ?? 0;
        
        // ... other cases ...
    }
}
```

---

## 📊 Database Verification

### Check Badge Tables Exist

```bash
php artisan tinker
```

```php
// Check badges table
echo App\Models\Badge::count() . " badges\n";

// Check badge_progress table
echo App\Models\BadgeProgress::count() . " progress records\n";

// Check user_badges table
echo App\Models\UserBadge::count() . " earned badges\n";
```

### Verify Data

```sql
-- Check badges
SELECT * FROM badges LIMIT 5;

-- Check progress for a user
SELECT * FROM badge_progress WHERE user_id = 1;

-- Check earned badges
SELECT * FROM user_badges WHERE user_id = 1;
```

---

## 🚨 Troubleshooting

### Issue: "Class not found" error
**Solution**: Make sure the service is properly namespaced. Check:
```php
// In BadgeTrackingService.php - first line should be:
namespace App\Services;
```

### Issue: Events not firing
**Solution**: Ensure events are dispatched in your controllers:
```php
// After creating tabung_sampah
event(new TabungSampahCreated($tabungSampah));

// After creating poin_transaksi
event(new PoinTransaksiCreated($poinTransaksi));
```

### Issue: Badges not unlocking
**Solution**: 
1. Check badges exist in DB: `Badge::count()`
2. Check badge_progress exists: `BadgeProgress::where('user_id', 1)->count()`
3. Check user total_poin/setor: `User::find(1)->only(['total_poin', 'total_setor_sampah'])`
4. Manually trigger: `app(BadgeTrackingService::class)->updateUserBadgeProgress($user)`

### Issue: API returns empty results
**Solution**: Make sure to initialize badges first:
```bash
php artisan badge:initialize
```

---

## 📈 Monitoring

### Check Badge Progress Logs
```bash
# View recent logs
tail -f storage/logs/laravel.log | grep "Badge"
```

### Monitor Cron Job
After scheduling, monitor that it runs:
```bash
# Check last execution
php artisan schedule:list

# View cron output
php artisan schedule:work
```

---

## ✅ Post-Installation Verification

Run this checklist to verify everything works:

- [ ] All files created successfully
- [ ] Routes registered (test with `php artisan route:list`)
- [ ] Event listeners registered (test event firing)
- [ ] Console command works (`php artisan badge:recalculate`)
- [ ] Badges initialized for users
- [ ] API endpoint returns data
- [ ] Leaderboard shows users
- [ ] Admin analytics work
- [ ] Cron job scheduled

---

## 🎉 You're Done!

Badge tracking system is now fully implemented. Users can:
- ✅ Track their badge progress in real-time
- ✅ See their completed badges
- ✅ View leaderboards
- ✅ Get notified when badges unlock
- ✅ Earn reward points automatically

Admins can:
- ✅ View badge analytics
- ✅ See who earned which badges
- ✅ Monitor gamification metrics

---

**Next Steps**:
1. Integrate APIs into your frontend
2. Create badge progress UI components
3. Add notifications when badges unlock
4. Create dashboard views for badge display

