# 📊 BADGE_PROGRESS Table - Detailed Visualization

**Date**: November 26, 2025  
**Status**: ✅ Production Ready

---

## 🏗️ Table Structure

```
TABLE NAME: badge_progress
PRIMARY KEY: id (BIGINT AUTO_INCREMENT)
COMPOSITE UNIQUE: (user_id, badge_id)
ENGINE: InnoDB
```

---

## 📝 Column Definitions

### 1. **id** (BIGINT, PK)
```
Type: BIGINT AUTO_INCREMENT
Role: Primary Key - unique identifier for each progress record
Range: 1, 2, 3, 4, ... (auto-generated)
Example: id = 1
```

### 2. **user_id** (BIGINT, FK)
```
Type: BIGINT
Role: Foreign Key → users.id
Purpose: Identifies which user is tracking this badge
Cascade: CASCADE DELETE (delete progress if user deleted)
Example: user_id = 5
```

### 3. **badge_id** (BIGINT, FK)
```
Type: BIGINT
Role: Foreign Key → badges.id
Purpose: Identifies which badge is being tracked
Cascade: CASCADE DELETE (delete progress if badge deleted)
Example: badge_id = 1
```

### 4. **current_value** (INT)
```
Type: INT (default: 0)
Purpose: Current progress value toward target
Logic by Badge Type:
  • 'poin':       User's total points accumulated
  • 'setor':      User's total waste deposited (in tons)
  • 'kombinasi':  Minimum of (poin%, setor%)
  • 'special':    0 (not triggered) or 100 (triggered)
  • 'ranking':    User's current ranking position

Example Values:
  Eco Warrior (poin badge): 250
  Green Depositor (setor badge): 75
  Special Event: 0 or 100
  Top 5 Ranker (ranking badge): 3
```

### 5. **target_value** (INT)
```
Type: INT (default: 0)
Purpose: Target value needed to unlock badge
Source: Copied from badge.syarat_poin or badge.syarat_setor

Example Values:
  Eco Warrior (poin badge): 1000
  Green Depositor (setor badge): 100
  Special Event badge: 1
  Top 5 Ranker: 5
```

### 6. **progress_percentage** (DECIMAL(5,2))
```
Type: DECIMAL(5,2) - Precision: 5 digits, 2 decimal places
Range: 0.00 to 100.00
Calculation: (current_value / target_value) * 100
Auto-Update: Recalculated on every update
Precision: Can store 0.00%, 25.50%, 100.00%, etc.

Examples:
  current=250, target=1000 → (250/1000)*100 = 25.00%
  current=500, target=1000 → (500/1000)*100 = 50.00%
  current=100, target=100  → (100/100)*100 = 100.00%
```

### 7. **is_unlocked** (BOOLEAN)
```
Type: BOOLEAN (TINYINT 0 or 1)
Default: false (0)
Purpose: Has badge been earned/completed?

Values:
  0 (false) = Still in progress
  1 (true)  = Badge earned! (is_unlocked = true)

When becomes TRUE:
  → Create record in user_badges table
  → Add reward_poin to user's total_poin
  → Record audit trail in poin_transaksis
  → Set unlocked_at timestamp
```

### 8. **unlocked_at** (TIMESTAMP)
```
Type: TIMESTAMP
Default: NULL
Purpose: When was badge completed/earned?

Values:
  NULL = Not yet unlocked
  2025-11-25 14:30:45 = Unlocked at this date/time

Only set when:
  is_unlocked changes from false to true
  Example: 2025-11-25 14:30:45
```

### 9. **created_at** (TIMESTAMP)
```
Type: TIMESTAMP
Default: CURRENT_TIMESTAMP
Purpose: When was this progress record created?
Set by: Laravel automatically when record inserted
Updated by: Never changes after creation

Example: 2025-11-20 08:00:00
Purpose: Tracks when user started tracking this badge
```

### 10. **updated_at** (TIMESTAMP)
```
Type: TIMESTAMP
Default: CURRENT_TIMESTAMP on UPDATE
Purpose: When was progress last updated?
Updated by: Laravel automatically on every change
Example: 2025-11-26 14:30:45

Changes whenever:
  • current_value changes
  • progress_percentage recalculated
  • is_unlocked status changes
  • Any other field updated
```

---

## 📊 Sample Data

```
┌────┬─────────┬──────────┬────────────────┬──────────────┬────────────────┬────────────┬──────────────────┬─────────────────────┬─────────────────────┐
│ id │ user_id │ badge_id │ current_value  │ target_value │ progress_%     │ is_unlocked│ unlocked_at       │ created_at          │ updated_at          │
├────┼─────────┼──────────┼────────────────┼──────────────┼────────────────┼────────────┼──────────────────┼─────────────────────┼─────────────────────┤
│ 1  │ 5       │ 1        │ 250            │ 1000         │ 25.00          │ 0          │ NULL              │ 2025-11-20 08:00:00 │ 2025-11-26 10:30:00 │
│ 2  │ 5       │ 2        │ 75             │ 100          │ 75.00          │ 0          │ NULL              │ 2025-11-20 08:00:00 │ 2025-11-26 12:00:00 │
│ 3  │ 5       │ 3        │ 40             │ 50           │ 80.00          │ 0          │ NULL              │ 2025-11-20 08:00:00 │ 2025-11-26 14:15:00 │
│ 4  │ 5       │ 1        │ 1000           │ 1000         │ 100.00         │ 1          │ 2025-11-25 14:30  │ 2025-11-20 08:00:00 │ 2025-11-25 14:30:00 │
│ 5  │ 6       │ 1        │ 500            │ 1000         │ 50.00          │ 0          │ NULL              │ 2025-11-21 09:30:00 │ 2025-11-26 15:00:00 │
│ 6  │ 6       │ 4        │ 1              │ 1            │ 100.00         │ 1          │ 2025-11-24 16:45  │ 2025-11-21 09:30:00 │ 2025-11-24 16:45:00 │
│ 7  │ 7       │ 2        │ 30             │ 100          │ 30.00          │ 0          │ NULL              │ 2025-11-22 10:15:00 │ 2025-11-26 11:20:00 │
│ 8  │ 7       │ 5        │ 2              │ 5            │ 40.00          │ 0          │ NULL              │ 2025-11-22 10:15:00 │ 2025-11-26 13:45:00 │
└────┴─────────┴──────────┴────────────────┴──────────────┴────────────────┴────────────┴──────────────────┴─────────────────────┴─────────────────────┘
```

---

## 🔍 Row Explanations

### Row 1: In Progress - 25%
```
id=1, user_id=5, badge_id=1
Badge: "Eco Warrior" (poin badge)
Progress: 250/1000 = 25.00% ░░░░ (JUST STARTED)
Status: IN PROGRESS
Last Updated: 2025-11-26 10:30
```

### Row 2: Almost Complete - 75%
```
id=2, user_id=5, badge_id=2
Badge: "Green Depositor" (setor badge)
Progress: 75/100 = 75.00% ██████░░ (ALMOST THERE!)
Status: IN PROGRESS (very close to completion)
Last Updated: 2025-11-26 12:00
```

### Row 3: Very Close - 80%
```
id=3, user_id=5, badge_id=3
Badge: "Eco Master" (kombinasi badge)
Progress: 40/50 = 80.00% ███████░ (ALMOST THERE!)
Status: IN PROGRESS (one last push!)
Last Updated: 2025-11-26 14:15
```

### Row 4: ✅ COMPLETED!
```
id=4, user_id=5, badge_id=1
Badge: "Eco Warrior" (poin badge)
Progress: 1000/1000 = 100.00% ████████ (COMPLETED ✅)
Status: UNLOCKED!
Unlocked At: 2025-11-25 14:30
Actions Taken:
  ✓ Record created in user_badges
  ✓ Reward points added to user
  ✓ Audit trail recorded in poin_transaksis
```

### Row 5: Halfway - 50%
```
id=5, user_id=6, badge_id=1
Badge: "Eco Warrior" (poin badge)
Progress: 500/1000 = 50.00% ████░░░░ (HALFWAY)
Status: IN PROGRESS
Last Updated: 2025-11-26 15:00
```

### Row 6: ✅ COMPLETED!
```
id=6, user_id=6, badge_id=4
Badge: "Special Event Winner" (special badge)
Progress: 1/1 = 100.00% ████████ (COMPLETED ✅)
Status: UNLOCKED!
Unlocked At: 2025-11-24 16:45
Type: Special badge (no progress tracking, just trigger)
```

### Rows 7-8: User 7 Tracking Multiple Badges
```
Row 7: Badge 2 (setor) - 30% - IN PROGRESS
Row 8: Badge 5 (ranking) - 40% (rank 2 of 5) - IN PROGRESS
```

---

## 📈 Progress Percentage Mapping

```
0-25%      ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
           🟦 "JUST STARTED" - Beginning the journey

25-50%     ████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
           🟦 "HALFWAY" - Good progress!

50-75%     ████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
           🟦 "ALMOST THERE" - Keep going!

75-100%    ███████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
           🟦 "ALMOST THERE" - Final push!

100%       ███████████████████████████████████████████████████████████
           ✅ "COMPLETED" - Badge earned! → Moves to user_badges
```

---

## 🔗 Relationships

```
┌─ badge_progress.user_id
│  └─ Foreign Key to users.id
│     Identifies WHO is tracking the badge
│     When user deleted → ALL their progress deleted (CASCADE)
│
├─ badge_progress.badge_id
│  └─ Foreign Key to badges.id
│     Identifies WHICH badge is tracked
│     When badge deleted → ALL progress for it deleted (CASCADE)
│
└─ When is_unlocked = 1 (TRUE)
   ├─ Create record in user_badges table
   │  ├─ user_id → same user
   │  ├─ badge_id → same badge
   │  ├─ tanggal_dapat → NOW
   │  └─ reward_claimed → true
   │
   ├─ Add reward_poin to users.total_poin
   │  └─ From badges.reward_poin
   │
   └─ Create audit trail in poin_transaksis
      ├─ poin_transaksis.user_id → user_id
      ├─ poin_transaksis.sumber → 'badge_reward'
      ├─ poin_transaksis.jumlah → badges.reward_poin
      └─ poin_transaksis.deskripsi → 'Badge "Eco Warrior" earned!'
```

---

## 🏃 Auto-Update Triggers

```
EVENT                              ACTION                RESULT
─────────────────────────────────────────────────────────────────────────

1. setor_sampah CREATED
   ├─ What: New waste deposit
   ├─ Trigger: TabungSampahCreated event
   └─ Update: 'setor' badge progress
      └─ Recalculate current_value
      └─ Recalculate progress_percentage
      └─ Check if should unlock

2. poin_transaksis ADDED
   ├─ What: New points added
   ├─ Trigger: PoinTransaksiCreated event
   └─ Update: 'poin' badge progress
      └─ Recalculate current_value
      └─ Recalculate progress_percentage
      └─ Check if should unlock

3. poin_transaksis SUBTRACTED
   ├─ What: Points withdrawn/used
   ├─ Trigger: PoinTransaksiCreated event (sumber=manual/other)
   └─ Update: 'poin' badge progress
      └─ Recalculate (may go backward!)
      └─ Recalculate progress_percentage
      └─ Badge status unchanged (can't unlock/unlock once earned)

4. Daily at 01:00 AM
   ├─ What: Scheduled cron job
   ├─ Command: php artisan badge:recalculate
   └─ Recalculate: ALL users' ALL badges
      └─ Check for any missed updates
      └─ Update ranking badges
      └─ Handle edge cases

5. User CREATED
   ├─ What: New user registered
   ├─ Trigger: UserCreated event
   └─ Action: Initialize all badges
      └─ Create badge_progress record for EACH badge
      └─ Set current_value = 0
      └─ Set progress_percentage = 0
      └─ Set is_unlocked = false
```

---

## 🎯 Common Queries

### 1️⃣ Get User's All Badge Progress
```sql
SELECT * FROM badge_progress WHERE user_id = 5;
```
**Result**: All badges user is tracking

### 2️⃣ Get User's Completed Badges
```sql
SELECT * FROM badge_progress 
WHERE user_id = 5 AND is_unlocked = 1;
```
**Result**: Badges user has earned

### 3️⃣ Get Almost-Complete Badges (75%+)
```sql
SELECT * FROM badge_progress 
WHERE user_id = 5 
AND progress_percentage >= 75 
AND is_unlocked = 0;
```
**Result**: Badges user is close to completing

### 4️⃣ Top 10 Badge Achievers
```sql
SELECT user_id, COUNT(*) as badges_earned
FROM badge_progress 
WHERE is_unlocked = 1 
GROUP BY user_id 
ORDER BY badges_earned DESC 
LIMIT 10;
```
**Result**: Top 10 users by number of badges

### 5️⃣ Track User's Progress Over Time
```sql
SELECT progress_percentage, updated_at 
FROM badge_progress 
WHERE user_id = 5 AND badge_id = 1 
ORDER BY updated_at DESC 
LIMIT 20;
```
**Result**: How user's progress changed over time

### 6️⃣ Get All Unlocks Today
```sql
SELECT * FROM badge_progress 
WHERE is_unlocked = 1 
AND DATE(unlocked_at) = CURDATE();
```
**Result**: All badges unlocked today

### 7️⃣ Count Total Users Unlocking Each Badge
```sql
SELECT badge_id, COUNT(*) as total_unlocks
FROM badge_progress 
WHERE is_unlocked = 1 
GROUP BY badge_id;
```
**Result**: Popularity of each badge

### 8️⃣ Find Stalled Progress (No update in 7 days)
```sql
SELECT * FROM badge_progress 
WHERE is_unlocked = 0 
AND updated_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
```
**Result**: Users who haven't progressed in a week

---

## 🔧 Performance Indexes

```
Index 1: (user_id, is_unlocked)
├─ Purpose: Find all completed badges for a user
├─ Query: WHERE user_id = ? AND is_unlocked = 1
└─ Speed: Fast ⚡

Index 2: (user_id, progress_percentage)
├─ Purpose: Find badges by progress level
├─ Query: WHERE user_id = ? AND progress_percentage >= 75
└─ Speed: Fast ⚡

Index 3: (badge_id, is_unlocked)
├─ Purpose: How many users unlocked this badge?
├─ Query: WHERE badge_id = ? AND is_unlocked = 1
└─ Speed: Fast ⚡

Index 4: (user_id, is_unlocked, progress_percentage)
├─ Purpose: Complex queries with multiple filters
├─ Query: WHERE user_id = ? AND is_unlocked = 0 AND progress_percentage >= 50
└─ Speed: Very Fast ⚡⚡

All indexes: BTREE (default, best for range queries)
```

---

## 💾 Size Estimation

```
Per Record: ~80 bytes
- id: 8 bytes
- user_id: 8 bytes
- badge_id: 8 bytes
- current_value: 4 bytes
- target_value: 4 bytes
- progress_percentage: 8 bytes
- is_unlocked: 1 byte
- unlocked_at: 8 bytes
- created_at: 8 bytes
- updated_at: 8 bytes
- Indexes/Overhead: ~15 bytes

Expected Rows: (users * badges)
├─ 1000 users × 10 badges = 10,000 rows ~= 800 KB
├─ 10,000 users × 10 badges = 100,000 rows ~= 8 MB
└─ 100,000 users × 10 badges = 1,000,000 rows ~= 80 MB
```

---

## ✅ Validation Rules

```
current_value >= 0
target_value >= 0
progress_percentage BETWEEN 0 AND 100
is_unlocked IN (0, 1)
unlocked_at IS NULL OR unlocked_at IS TIMESTAMP
created_at IS NOT NULL
updated_at IS NOT NULL
(user_id, badge_id) MUST BE UNIQUE
user_id MUST EXIST in users table
badge_id MUST EXIST in badges table
```

---

## 📋 Migration SQL

```sql
CREATE TABLE badge_progress (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    badge_id BIGINT UNSIGNED NOT NULL,
    current_value INT NOT NULL DEFAULT 0,
    target_value INT NOT NULL DEFAULT 0,
    progress_percentage DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    is_unlocked BOOLEAN NOT NULL DEFAULT FALSE,
    unlocked_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE KEY unique_user_badge (user_id, badge_id),
    
    -- Foreign Keys
    CONSTRAINT fk_badge_progress_user 
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_badge_progress_badge 
        FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_user_unlocked (user_id, is_unlocked),
    INDEX idx_user_progress (user_id, progress_percentage),
    INDEX idx_badge_unlocked (badge_id, is_unlocked),
    INDEX idx_user_unlocked_progress (user_id, is_unlocked, progress_percentage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🎮 Usage Example (PHP/Laravel)

```php
// Get user's progress on all badges
$progressRecords = BadgeProgress::where('user_id', $userId)->get();

// Check if badge is completed
$isBadgeUnlocked = BadgeProgress::where([
    'user_id' => $userId,
    'badge_id' => $badgeId,
    'is_unlocked' => true
])->exists();

// Get progress percentage
$progress = BadgeProgress::where('user_id', $userId)
    ->where('badge_id', $badgeId)
    ->value('progress_percentage'); // Returns: 45.50

// Find all almost-complete badges (75%+)
$almostThere = BadgeProgress::where('user_id', $userId)
    ->where('is_unlocked', false)
    ->where('progress_percentage', '>=', 75)
    ->get();

// Get completion date
$unlockedAt = BadgeProgress::where('user_id', $userId)
    ->where('badge_id', $badgeId)
    ->value('unlocked_at'); // Returns: 2025-11-25 14:30:45

// Update progress (automatic in BadgeTrackingService)
BadgeProgress::updateOrCreate(
    ['user_id' => $userId, 'badge_id' => $badgeId],
    [
        'current_value' => $newValue,
        'target_value' => $targetValue,
        'progress_percentage' => ($newValue / $targetValue) * 100,
        'is_unlocked' => $isComplete ? true : false,
        'unlocked_at' => $isComplete ? now() : null
    ]
);
```

---

## 🔗 Related Tables

- **badges** - Badge definitions (nama, deskripsi, syarat, reward)
- **user_badges** - Earned badges (when is_unlocked=true, record created here)
- **users** - User data (linked via user_id)
- **poin_transaksis** - Point audit trail (records reward points)

---

**File Created**: November 26, 2025
**Status**: ✅ Production Ready
