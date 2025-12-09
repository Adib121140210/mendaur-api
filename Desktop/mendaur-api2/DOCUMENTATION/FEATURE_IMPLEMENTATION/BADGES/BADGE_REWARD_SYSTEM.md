# 🏆 Badge Reward System - Complete Implementation

## ✅ System Overview

The Badge Reward System automatically awards users with bonus points when they unlock badges. The system checks badge requirements after each significant user action and automatically grants badges with their associated bonus points.

---

## 🎯 **How It Works**

```
1. User deposits waste → Admin approves
   ↓
2. User gets base points (e.g., 15 points from waste)
   ↓
3. System automatically checks ALL badge requirements
   ↓
4. User meets "Pemula Peduli" (1st deposit)
   ↓
5. System awards badge + 50 bonus points automatically
   ↓
6. Creates activity log: "badge_unlock" (+50 poin)
   ↓
7. Creates notification: "🎉 Badge baru! +50 poin!"
   ↓
8. User's total_poin = 15 (base) + 50 (bonus) = 65
```

---

## 📊 **Badge Schema**

### Badges Table Structure:
```sql
- id: Badge ID
- nama: Badge name (e.g., "Pemula Peduli")
- deskripsi: Description
- icon: Emoji icon (e.g., "🌱")
- syarat_poin: Points requirement (0 if not applicable)
- syarat_setor: Deposit count requirement (0 if not applicable)
- reward_poin: ✨ BONUS POINTS awarded when unlocked
- tipe: 'poin', 'setor', 'kombinasi', 'special'
```

### User Badges Pivot Table:
```sql
- id: Record ID
- user_id: User who earned the badge
- badge_id: Badge earned
- tanggal_dapat: When badge was awarded
- reward_claimed: TRUE (rewards given automatically)
- UNIQUE KEY (user_id, badge_id) - Prevents duplicates
```

---

## 🎁 **Available Badges & Rewards**

| Badge | Icon | Requirement | Bonus Points | Type |
|-------|------|-------------|--------------|------|
| Pemula Peduli | 🌱 | 1 deposit | +50 | setor |
| Eco Warrior | ♻️ | 5 deposits | +100 | setor |
| Green Hero | 🦸 | 10 deposits | +200 | setor |
| Planet Saver | 🌍 | 25 deposits | +500 | setor |
| Bronze Collector | 🥉 | 100 points | +100 | poin |
| Silver Collector | 🥈 | 300 points | +200 | poin |
| Gold Collector | 🥇 | 600 points | +400 | poin |

---

## 🔌 **API Endpoints**

### 1. Get All Available Badges
```
GET http://127.0.0.1:8000/api/badges
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "nama": "Pemula Peduli",
      "deskripsi": "Setor sampah pertama kali",
      "icon": "🌱",
      "syarat_poin": 0,
      "syarat_setor": 1,
      "reward_poin": 50,
      "tipe": "setor"
    }
  ]
}
```

---

### 2. Get User's Unlocked Badges
```
GET http://127.0.0.1:8000/api/users/{userId}/badges
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "nama": "Pemula Peduli",
      "deskripsi": "Setor sampah pertama kali",
      "icon": "🌱",
      "syarat_poin": 0,
      "syarat_setor": 1,
      "reward_poin": 50,
      "tipe": "setor",
      "tanggal_dapat": "2025-11-15T08:30:00.000000Z"
    }
  ]
}
```

---

### 3. Get User's Badge Progress
```
GET http://127.0.0.1:8000/api/users/{userId}/badge-progress
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "badge": {
        "id": 1,
        "nama": "Pemula Peduli",
        "reward_poin": 50
      },
      "unlocked": true,
      "progress": 100
    },
    {
      "badge": {
        "id": 2,
        "nama": "Eco Warrior",
        "reward_poin": 100
      },
      "unlocked": false,
      "progress": 40
    }
  ]
}
```

---

### 4. Manually Check Badges (Testing)
```
POST http://127.0.0.1:8000/api/users/{userId}/check-badges
```

**Response:**
```json
{
  "status": "success",
  "message": "Badge(s) baru diberikan!",
  "data": {
    "newly_unlocked": [
      {
        "id": 1,
        "nama": "Pemula Peduli",
        "reward_poin": 50
      }
    ],
    "count": 1
  }
}
```

---

### 5. Approve Waste Deposit (Auto Badge Check)
```
POST http://127.0.0.1:8000/api/tabung-sampah/{id}/approve
Content-Type: application/json

{
  "berat_kg": 5.5,
  "poin_didapat": 15
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Penyetoran disetujui!",
  "data": {
    "tabung_sampah": {...},
    "user": {
      "total_poin": 65,
      "total_setor_sampah": 1
    },
    "new_badges": [
      {
        "id": 1,
        "nama": "Pemula Peduli",
        "reward_poin": 50
      }
    ]
  }
}
```

---

## 🔄 **Automatic Badge Checking**

The `BadgeService::checkAndAwardBadges($userId)` method is automatically called:

### ✅ **Trigger Points:**

1. **After Waste Approval**
   ```php
   TabungSampahController@approve
   → Updates user points
   → Calls BadgeService::checkAndAwardBadges()
   ```

2. **Manual Trigger (Testing)**
   ```php
   BadgeController@checkBadges
   → Manually checks all badges
   → Awards any newly met requirements
   ```

---

## 💡 **Implementation Logic**

### BadgeService Flow:

```php
1. Get all badges from database
2. Get user's current stats (total_poin, total_setor_sampah)
3. For each badge:
   a. Check if user already has it (skip if yes)
   b. Check if requirements are met
   c. If met:
      - Insert into user_badges table
      - Add reward_poin to user's total_poin
      - Log activity: "badge_unlock"
      - Create notification
4. Return list of newly unlocked badges
```

### Badge Requirement Checking:

```php
- Type 'poin': user->total_poin >= badge->syarat_poin
- Type 'setor': user->total_setor_sampah >= badge->syarat_setor
- Type 'kombinasi': BOTH requirements must be met
- Type 'special': Custom logic (future use)
```

---

## 📝 **Database Changes**

### Migration Updates:
```php
// badges table
+ reward_poin INT DEFAULT 0
+ tipe ENUM includes 'special'

// user_badges table
+ tanggal_dapat TIMESTAMP (was DATE)
+ reward_claimed BOOLEAN DEFAULT TRUE
+ timestamps
+ UNIQUE KEY (user_id, badge_id)
```

---

## 🧪 **Testing the System**

### Test Scenario 1: First Deposit
```bash
# 1. Create user (already exists: Budi, ID=3, 2 deposits, 50 points)

# 2. Check current badges
curl http://127.0.0.1:8000/api/users/3/badges

# 3. Admin approves a new deposit
curl -X POST http://127.0.0.1:8000/api/tabung-sampah/1/approve \
  -H "Content-Type: application/json" \
  -d '{"berat_kg": 5, "poin_didapat": 15}'

# 4. Check badges again - should have new badge!
curl http://127.0.0.1:8000/api/users/3/badges
# Expected: "Pemula Peduli" badge + 50 bonus points
# Total: 15 (deposit) + 50 (bonus) = 65 points
```

### Test Scenario 2: Reaching Point Milestone
```bash
# User has 90 points, needs 10 more for Bronze Collector

# Admin approves deposit worth 15 points
# Total becomes 105 points
# System automatically awards "Bronze Collector" badge
# Bonus: +100 points
# Final total: 105 + 100 = 205 points
```

---

## 📱 **Frontend Integration**

### Display Badges:
```javascript
// Get all badges to show in gallery
const response = await fetch('http://127.0.0.1:8000/api/badges');
const badges = await response.json();

// Show user's unlocked badges
const userBadges = await fetch(`http://127.0.0.1:8000/api/users/${userId}/badges`);

// Show progress towards locked badges
const progress = await fetch(`http://127.0.0.1:8000/api/users/${userId}/badge-progress`);
```

### Listen for Notifications:
```javascript
// After waste approval, check notifications
const notif = await fetch(`http://127.0.0.1:8000/api/users/${userId}/notifications`);
// Look for type: 'badge' notifications
// Display popup: "🎉 Badge baru! Pemula Peduli +50 poin!"
```

---

## 📊 **Activity Log Examples**

When a badge is unlocked, `log_aktivitas` records:
```
tipe_aktivitas: "badge_unlock"
deskripsi: "Mendapatkan badge 'Pemula Peduli' dan bonus 50 poin"
poin_perubahan: 50
tanggal: 2025-11-15 13:45:00
```

---

## 🎯 **Benefits**

1. ✅ **Automatic**: No manual intervention needed
2. ✅ **Fair**: Unique constraint prevents duplicate awards
3. ✅ **Transparent**: All rewards logged in activity
4. ✅ **Engaging**: Users notified immediately
5. ✅ **Flexible**: Easy to add new badges
6. ✅ **Scalable**: Efficient database queries

---

## 🚀 **Files Created/Modified**

### New Files:
- ✅ `app/Services/BadgeService.php` - Core badge logic
- ✅ `app/Http/Controllers/BadgeController.php` - API endpoints

### Modified Files:
- ✅ `database/migrations/.../create_badges_table.php` - Added reward_poin
- ✅ `database/seeders/BadgeSeeder.php` - Added reward values
- ✅ `app/Http/Controllers/TabungSampahController.php` - Auto badge check
- ✅ `routes/api.php` - Added badge routes

---

## ✅ **System Status**

- [x] Badge table with reward_poin column
- [x] User badges pivot with unique constraint
- [x] BadgeService with auto-check logic
- [x] BadgeController with API endpoints
- [x] TabungSampah approve method with badge trigger
- [x] Activity logging on badge unlock
- [x] Notification creation on badge unlock
- [x] 7 badges with rewards configured
- [x] API routes registered
- [x] Migrations run successfully

**🎉 Badge Reward System is COMPLETE and ACTIVE!**

Every time a user's waste deposit is approved, the system automatically checks and awards badges with bonus points! 🏆
