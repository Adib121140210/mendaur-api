# 🏆 Badge Tracking System - Complete Implementation Guide

**Date**: November 25, 2025  
**Status**: ✅ READY FOR IMPLEMENTATION  
**Purpose**: Track completed/incomplete badges for each user with progress monitoring

---

## 📋 Overview

Badge tracking system memungkinkan:
1. ✅ Real-time progress monitoring untuk setiap badge
2. ✅ Automatic unlock ketika kondisi terpenuhi
3. ✅ Complete audit trail dari setiap achievement
4. ✅ User dashboard menampilkan progress & unlocked badges
5. ✅ Admin dashboard untuk analytics & user achievements

---

## 🏗️ Current Database Structure

### Badge_Progress Table (Existing)
```php
Schema::create('badge_progress', function (Blueprint $table) {
    $table->id();  // PK
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
    
    // Tracking fields
    $table->integer('current_value')->default(0);      // Current progress
    $table->integer('target_value')->default(0);       // Target to unlock
    $table->decimal('progress_percentage', 5, 2)->default(0.00);  // 0-100%
    $table->boolean('is_unlocked')->default(false);    // Completed?
    $table->timestamp('unlocked_at')->nullable();      // When completed
    
    $table->timestamps();  // created_at, updated_at
    $table->unique(['user_id', 'badge_id']);
    $table->index(['user_id', 'is_unlocked']);
    $table->index('progress_percentage');
});
```

### User_Badges Table (Existing)
```php
Schema::create('user_badges', function (Blueprint $table) {
    $table->id();  // PK
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
    
    $table->timestamp('tanggal_dapat')->useCurrent();  // When earned
    $table->boolean('reward_claimed')->default(true); // Reward given?
    
    $table->timestamps();
    $table->unique(['user_id', 'badge_id']);
});
```

**Relationship**:
- `badge_progress`: Tracks progress toward unlocking a badge (in-progress)
- `user_badges`: Records when badge was actually earned (completed)

---

## 🔍 Badge Types & Tracking Logic

### Badge Types (dari badges.tipe)

| Tipe | Syarat | Tracking Field | Example |
|------|--------|--------|---------|
| `poin` | Total poin ≥ syarat_poin | current_value = total_poin | "1000 Poin" badge |
| `setor` | Total setor ≥ syarat_setor | current_value = total_setor | "50 Deposits" badge |
| `kombinasi` | Both poin AND setor | current_value = min(poin%, setor%) | "1000 Poin + 50 Setor" |
| `special` | Event/promo based | custom_value = event_status | Time-limited achievement |
| `ranking` | Leaderboard rank | current_value = user_rank | Top 10 users |

---

## 📊 Tracking Queries & Analytics

### 1. Get User's Badge Progress (All Badges)
```sql
SELECT 
    bp.id,
    u.nama AS user_name,
    b.nama AS badge_name,
    b.tipe AS badge_type,
    b.reward_poin,
    bp.current_value,
    bp.target_value,
    bp.progress_percentage,
    bp.is_unlocked,
    bp.unlocked_at,
    CASE 
        WHEN bp.is_unlocked THEN 'COMPLETED'
        WHEN bp.progress_percentage >= 75 THEN 'ALMOST THERE'
        WHEN bp.progress_percentage >= 50 THEN 'HALFWAY'
        ELSE 'JUST STARTED'
    END AS status,
    bp.updated_at
FROM badge_progress bp
JOIN users u ON bp.user_id = u.id
JOIN badges b ON bp.badge_id = b.id
WHERE bp.user_id = ?
ORDER BY bp.is_unlocked DESC, bp.progress_percentage DESC;
```

**Output**:
```
┌─────────┬──────────┬───────────┬────────────┬──────────┬─────────┬────────────┬────────────┐
│ user_id │ username │ badge     │ type       │ progress │ current │ target     │ status     │
├─────────┼──────────┼───────────┼────────────┼──────────┼─────────┼────────────┼────────────┤
│ 1       │ Ahmad    │ Eco Hero  │ poin       │ 100%     │ 1000    │ 1000       │ COMPLETED  │
│ 1       │ Ahmad    │ Setor Pro │ setor      │ 87.5%    │ 35      │ 40         │ ALMOST     │
│ 1       │ Ahmad    │ Speedster │ kombinasi  │ 65%      │ 650     │ 1000+50    │ HALFWAY    │
│ 1       │ Ahmad    │ Premium   │ special    │ 0%       │ 0       │ 1          │ JUST START │
└─────────┴──────────┴───────────┴────────────┴──────────┴─────────┴────────────┴────────────┘
```

### 2. Get Completed vs Incomplete Badges
```sql
-- COMPLETED BADGES (user_badges table)
SELECT 
    ub.id,
    u.nama,
    b.nama AS badge_name,
    b.tipe,
    b.reward_poin,
    ub.tanggal_dapat AS earned_date,
    ub.reward_claimed
FROM user_badges ub
JOIN users u ON ub.user_id = u.id
JOIN badges b ON ub.badge_id = b.id
WHERE ub.user_id = ?
ORDER BY ub.tanggal_dapat DESC;

-- INCOMPLETE BADGES (badge_progress where is_unlocked = false)
SELECT 
    bp.id,
    u.nama,
    b.nama AS badge_name,
    b.tipe,
    bp.progress_percentage,
    bp.current_value,
    bp.target_value,
    (bp.target_value - bp.current_value) AS remaining
FROM badge_progress bp
JOIN users u ON bp.user_id = u.id
JOIN badges b ON bp.badge_id = b.id
WHERE bp.user_id = ? AND bp.is_unlocked = false
ORDER BY bp.progress_percentage DESC;
```

### 3. User Achievement Summary (Dashboard)
```sql
SELECT 
    u.id,
    u.nama,
    COUNT(CASE WHEN ub.id IS NOT NULL THEN 1 END) AS completed_count,
    COUNT(CASE WHEN bp.is_unlocked = false THEN 1 END) AS incomplete_count,
    COUNT(DISTINCT bp.badge_id) AS total_tracked,
    SUM(b.reward_poin) AS total_reward_poin,
    AVG(bp.progress_percentage) AS avg_progress_percentage,
    MAX(bp.progress_percentage) AS highest_progress,
    COUNT(CASE WHEN bp.progress_percentage >= 75 AND bp.is_unlocked = false THEN 1 END) AS almost_complete
FROM users u
LEFT JOIN user_badges ub ON u.id = ub.user_id
LEFT JOIN badge_progress bp ON u.id = bp.user_id
LEFT JOIN badges b ON bp.badge_id = b.id
WHERE u.id = ?
GROUP BY u.id, u.nama;
```

**Output**:
```
┌────────┬────────┬──────────────┬─────────────┬──────────────┬─────────────┬──────────────────┐
│ nama   │ earned │ incomplete   │ tracked     │ reward_poin  │ avg_progress│ almost_complete  │
├────────┼────────┼──────────────┼─────────────┼──────────────┼─────────────┼──────────────────┤
│ Ahmad  │ 3      │ 12           │ 15          │ 1500 poin    │ 58.5%       │ 4                │
└────────┴────────┴──────────────┴─────────────┴──────────────┴─────────────┴──────────────────┘
```

### 4. Top Achievers Leaderboard
```sql
SELECT 
    u.id,
    u.nama,
    u.foto_profil,
    COUNT(ub.id) AS badges_earned,
    SUM(b.reward_poin) AS total_reward_poin,
    MAX(bp.unlocked_at) AS last_achievement_date
FROM users u
LEFT JOIN user_badges ub ON u.id = ub.user_id
LEFT JOIN badges b ON ub.badge_id = b.id
LEFT JOIN badge_progress bp ON u.id = bp.user_id AND bp.is_unlocked = true
GROUP BY u.id, u.nama, u.foto_profil
ORDER BY badges_earned DESC, total_reward_poin DESC
LIMIT 10;
```

### 5. Progress Trend (Last 30 Days)
```sql
SELECT 
    DATE(bp.updated_at) AS date,
    COUNT(DISTINCT bp.user_id) AS users_updated,
    COUNT(CASE WHEN bp.is_unlocked = true THEN 1 END) AS badges_unlocked,
    AVG(bp.progress_percentage) AS avg_progress
FROM badge_progress bp
WHERE bp.updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(bp.updated_at)
ORDER BY date DESC;
```

---

## 🔄 Auto-Tracking Implementation (Backend Logic)

### When User Deposits Waste (setor_sampah)
```php
// 1. Update total_setor_sampah in users table
$user->increment('total_setor_sampah', 1);

// 2. Update badge_progress for 'setor' type badges
foreach (Badge::where('tipe', 'setor')->get() as $badge) {
    $progress = BadgeProgress::firstOrCreate(
        ['user_id' => $user->id, 'badge_id' => $badge->id],
        ['current_value' => 0, 'target_value' => $badge->syarat_setor]
    );
    
    $progress->current_value = $user->total_setor_sampah;
    $progress->progress_percentage = min(100, 
        ($progress->current_value / $progress->target_value) * 100
    );
    
    // Auto-unlock if condition met
    if ($progress->progress_percentage >= 100 && !$progress->is_unlocked) {
        $progress->is_unlocked = true;
        $progress->unlocked_at = now();
        
        // Create user_badges record
        UserBadge::firstOrCreate([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'tanggal_dapat' => now(),
            'reward_claimed' => true
        ]);
        
        // Add reward poin
        $user->increment('total_poin', $badge->reward_poin);
        PoinTransaksis::create([
            'user_id' => $user->id,
            'poin_didapat' => $badge->reward_poin,
            'sumber' => 'badge',
            'referensi_id' => $badge->id,
            'referensi_tipe' => 'badge',
            'keterangan' => "Unlock badge: {$badge->nama}"
        ]);
    }
    
    $progress->save();
}
```

### When User's Poin Changes (tukar_poin, setor, bonus)
```php
// Similar logic for 'poin' type badges
foreach (Badge::where('tipe', 'poin')->get() as $badge) {
    $progress = BadgeProgress::firstOrCreate(
        ['user_id' => $user->id, 'badge_id' => $badge->id],
        ['current_value' => 0, 'target_value' => $badge->syarat_poin]
    );
    
    $progress->current_value = $user->total_poin;
    $progress->progress_percentage = min(100, 
        ($progress->current_value / $progress->target_value) * 100
    );
    
    // Auto-unlock...
    if ($progress->progress_percentage >= 100 && !$progress->is_unlocked) {
        // Same unlock logic
    }
    
    $progress->save();
}
```

### For 'kombinasi' type badges
```php
// Both conditions must be met
$progress->current_value = min(
    ($user->total_poin / $badge->syarat_poin) * 100,
    ($user->total_setor_sampah / $badge->syarat_setor) * 100
);
```

---

## 📱 User-Facing APIs

### GET /api/user/badges/progress
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "nama": "Ahmad Sampah",
      "total_poin": 1250,
      "total_setor": 45
    },
    "summary": {
      "completed": 5,
      "incomplete": 10,
      "total_tracked": 15,
      "total_reward": 2500,
      "avg_progress": 62.5
    },
    "completed_badges": [
      {
        "id": 1,
        "nama": "Eco Hero",
        "tipe": "poin",
        "icon": "🌍",
        "earned_date": "2025-11-20 10:30:00",
        "reward_poin": 500
      }
    ],
    "in_progress": [
      {
        "id": 2,
        "nama": "Setor Pro",
        "tipe": "setor",
        "icon": "📦",
        "progress_percentage": 87.5,
        "current": 35,
        "target": 40,
        "remaining": 5,
        "status": "ALMOST THERE"
      }
    ]
  }
}
```

### GET /api/badges/leaderboard
```json
{
  "status": "success",
  "data": {
    "period": "all_time",
    "updated_at": "2025-11-25",
    "leaderboard": [
      {
        "rank": 1,
        "user": {
          "id": 5,
          "nama": "Budi Ramah Lingkungan",
          "foto_profil": "https://..."
        },
        "badges_earned": 12,
        "reward_poin_total": 4500,
        "last_achievement": "2025-11-24 15:20:00"
      }
    ]
  }
}
```

---

## 🎯 Dashboard Views

### User Badge Dashboard
```
┌─────────────────────────────────────────────────────┐
│  MY ACHIEVEMENTS                                    │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ✅ COMPLETED (5 badges)                            │
│  ├─ 🌍 Eco Hero (500 poin) - Nov 20               │
│  ├─ 📦 Setor Pro (300 poin) - Nov 18               │
│  └─ ...                                             │
│                                                     │
│  🔄 IN PROGRESS (10 badges)                         │
│                                                     │
│  87.5% ████████░░ Setor Pro - 5 more              │
│  75.0% ███████░░░ Speedster - 250 more poin        │
│  42.5% ████░░░░░░ Carbon Warrior - 575 more poin   │
│  15.0% █░░░░░░░░░ Platinum Member - 8500 more     │
│                                                     │
│  📊 STATISTICS                                      │
│  Total Earned: 1500 poin                           │
│  Avg Progress: 62.5%                               │
│  Almost Complete: 4 badges                          │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### Admin Analytics Dashboard
```
┌─────────────────────────────────────────────────────┐
│  ACHIEVEMENT ANALYTICS                              │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Total Users: 150                                  │
│  Badges Defined: 15                                │
│  Total Earned: 245                                 │
│  Avg per User: 1.6                                 │
│                                                     │
│  TOP EARNERS:                                      │
│  1. Budi (12 badges) - 4500 poin reward           │
│  2. Siti (11 badges) - 4200 poin reward           │
│  3. Ahmad (10 badges) - 3800 poin reward          │
│                                                     │
│  MOST EARNED BADGES:                               │
│  1. Eco Hero - 85 users                            │
│  2. Setor Pro - 42 users                           │
│  3. Speedster - 28 users                           │
│                                                     │
│  RAREST BADGES:                                    │
│  1. Platinum Member - 3 users                      │
│  2. Legend Status - 5 users                        │
│  3. Top Contributor - 8 users                      │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 🛠️ Implementation Checklist

### Database
- ✅ `badge_progress` table exists (auto-tracking fields present)
- ✅ `user_badges` table exists (completion records)
- ⏳ Add composite index for better query performance (optional)

### Backend Controllers
- ⏳ Create `BadgeProgressController` for tracking logic
- ⏳ Create `BadgeTrackingService` for auto-update logic
- ⏳ Update deposit/transaction logic to trigger badge updates
- ⏳ Create `BadgeLeaderboardController` for ranking

### APIs
- ⏳ GET `/api/user/badges/progress` - User's badge progress
- ⏳ GET `/api/user/badges/completed` - Completed badges only
- ⏳ GET `/api/user/badges/incomplete` - Incomplete badges only
- ⏳ GET `/api/badges/leaderboard` - Top achievers
- ⏳ GET `/api/admin/badges/analytics` - Analytics

### Frontend
- ⏳ Badge progress component with progress bars
- ⏳ Achievement notification when badge unlocked
- ⏳ User dashboard showing badge progress
- ⏳ Leaderboard view
- ⏳ Badge detail modal

### Notifications
- ⏳ Push notification when badge unlocked
- ⏳ Email notification for major achievements
- ⏳ In-app notification in `notifikasi` table

---

## 📊 Badge Progress Calculation Methods

### Method 1: Simple Count-Based (Setor Type)
```
current_value = total_setor_sampah
target_value = badge.syarat_setor
progress_percentage = (current_value / target_value) * 100
```

### Method 2: Point-Based
```
current_value = total_poin
target_value = badge.syarat_poin
progress_percentage = (current_value / target_value) * 100
```

### Method 3: Kombinasi (Both Required)
```
poin_progress = (total_poin / badge.syarat_poin) * 100
setor_progress = (total_setor_sampah / badge.syarat_setor) * 100
progress_percentage = MIN(poin_progress, setor_progress)

// Both conditions must be 100% to unlock
unlock_condition = (poin_progress >= 100 AND setor_progress >= 100)
```

### Method 4: Time-Based (Special)
```
days_active = DATE_DIFF(NOW(), user.created_at)
current_value = days_active
target_value = 30  // 30 days active
progress_percentage = (days_active / 30) * 100
```

### Method 5: Ranking-Based (Ranking Type)
```
// Calculate from leaderboard
user_rank = user_position_in_sorted_leaderboard
current_value = user_rank
target_value = 10  // Top 10
progress_percentage = ((10 - user_rank + 1) / 10) * 100

unlock_condition = (user_rank <= 10)
```

---

## 🔔 Tracking Events

| Event | Trigger | Update Fields | Action |
|-------|---------|--------------|--------|
| Deposit waste | tabung_sampah created | current_value (setor) | Check if unlock |
| Get points | poin_transaksis added | current_value (poin) | Check if unlock |
| Lose points | poin_transaksis subtracted | current_value (poin) | Downgrade? |
| Setor completed | tabung_sampah approved | current_value (setor) | Check if unlock |
| User profile created | users.created_at | current_value (time) | Start tracking |
| Daily cron job | 00:00 daily | all fields | Recalculate all |

---

## 🚀 Optimization Tips

### 1. Use Caching
```php
// Cache user badge progress for 5 minutes
Cache::remember("user_badges_{$userId}", 5 * 60, function() {
    return BadgeProgress::where('user_id', $userId)->get();
});
```

### 2. Batch Updates
```php
// Update all users' progress in one query
BadgeProgress::whereNull('updated_at')
    ->orWhere('updated_at', '<', now()->subHour())
    ->update(['updated_at' => now()]);
```

### 3. Separate Read/Write
- Read from cache for dashboard
- Write directly to DB for accuracy
- Update cache after write

### 4. Use Job Queue
```php
// Don't block request - queue the badge check
UpdateBadgeProgressJob::dispatch($user);
```

---

## ✅ Testing Scenarios

| Scenario | Expected Result |
|----------|-----------------|
| User reaches 1000 poin | "Eco Hero" badge unlocks, reward added |
| User makes 50 deposits | "Setor Pro" badge unlocks |
| User has 1000 poin + 50 deposits | "Master" badge (kombinasi) unlocks |
| User tops leaderboard | "Top Contributor" badge unlocks |
| User loses poin | Progress updates but doesn't unlock again |
| Badge progress shows 87.5% | Display "Almost there! 5 more needed" |

---

## 📝 SQL Optimization

### Add Composite Indexes
```sql
-- For faster badge progress lookups by user
ALTER TABLE badge_progress ADD INDEX idx_user_progress (user_id, is_unlocked, progress_percentage);

-- For faster leaderboard queries
ALTER TABLE user_badges ADD INDEX idx_user_earned (user_id, tanggal_dapat DESC);

-- For analytics queries
ALTER TABLE badge_progress ADD INDEX idx_date_range (user_id, created_at, is_unlocked);
```

---

## 🎯 Success Metrics

1. ✅ All users have badge_progress records created automatically
2. ✅ Badge unlocks happen within 1 second of condition met
3. ✅ Progress percentage accurate and updates in real-time
4. ✅ Dashboard loads badge data in < 500ms
5. ✅ Leaderboard updates daily or on-demand
6. ✅ Users receive notifications on badge unlock
7. ✅ 90%+ of badges have progress tracked

---

## 📚 Related Queries for Reference

```sql
-- Total users tracking badges
SELECT COUNT(DISTINCT user_id) FROM badge_progress;

-- Badges with highest completion rate
SELECT b.nama, COUNT(bp.id) total, 
       COUNT(CASE WHEN bp.is_unlocked THEN 1 END) completed,
       ROUND(COUNT(CASE WHEN bp.is_unlocked THEN 1 END) / COUNT(bp.id) * 100, 1) completion_rate
FROM badge_progress bp
JOIN badges b ON bp.badge_id = b.id
GROUP BY bp.badge_id
ORDER BY completion_rate DESC;

-- Users close to unlocking badges (75%+ progress)
SELECT u.nama, b.nama, bp.progress_percentage, 
       (bp.target_value - bp.current_value) AS needed
FROM badge_progress bp
JOIN users u ON bp.user_id = u.id
JOIN badges b ON bp.badge_id = b.id
WHERE bp.progress_percentage >= 75 AND bp.is_unlocked = false
ORDER BY bp.progress_percentage DESC;
```

---

**Status**: ✅ Ready for Implementation  
**Estimated Implementation Time**: 4-6 hours  
**Complexity Level**: Medium  
**Dependencies**: Existing badge_progress & user_badges tables  

