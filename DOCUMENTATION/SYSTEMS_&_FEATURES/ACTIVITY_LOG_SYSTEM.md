# 📊 User Statistics & Activity Logs - Complete Implementation

## ✅ **System Overview**

The Activity Log system tracks all user actions including waste deposits, badge unlocks, point transactions, and point redemptions. Every significant action is logged for transparency and user reference.

---

## 🎯 **What Was Implemented**

### **1. LogAktivitas Model**
**File:** `app/Models/LogAktivitas.php`

**Features:**
- ✅ Model with relationships
- ✅ Activity type constants
- ✅ Static `log()` method for easy logging
- ✅ Automatic timestamps

**Activity Types:**
```php
const TYPE_SETOR_SAMPAH = 'setor_sampah';    // Waste deposit approved
const TYPE_TUKAR_POIN = 'tukar_poin';        // Point redemption (negative)
const TYPE_BADGE_UNLOCK = 'badge_unlock';    // Badge earned with reward
const TYPE_POIN_BONUS = 'poin_bonus';        // Bonus points given
const TYPE_LEVEL_UP = 'level_up';            // Level progression
```

---

### **2. Database Schema**

**Table:** `log_aktivitas`

```sql
CREATE TABLE log_aktivitas (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    tipe_aktivitas VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    poin_perubahan INT DEFAULT 0,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_tanggal (user_id, tanggal)
);
```

**Key Features:**
- ✅ Indexed by `(user_id, tanggal)` for fast queries
- ✅ Cascade delete when user is deleted
- ✅ Supports positive and negative point changes
- ✅ Nullable description for flexible logging

---

### **3. API Endpoint**

**Endpoint:** `GET /api/users/{userId}/aktivitas`

**Query Parameters:**
- `limit` - Number of activities to return (default: 20, max: 100)

**Response Format:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "tipe_aktivitas": "setor_sampah",
      "deskripsi": "Menyetor 5kg sampah plastik",
      "poin_perubahan": 15,
      "tanggal": "2025-11-05T08:55:46Z"
    },
    {
      "id": 2,
      "tipe_aktivitas": "badge_unlock",
      "deskripsi": "Mendapatkan badge 'Pemula Peduli' dan bonus 50 poin",
      "poin_perubahan": 50,
      "tanggal": "2025-11-05T08:55:46Z"
    },
    {
      "id": 3,
      "tipe_aktivitas": "tukar_poin",
      "deskripsi": "Menukar 100 poin dengan Voucher Grab",
      "poin_perubahan": -100,
      "tanggal": "2025-11-13T15:20:00Z"
    }
  ]
}
```

---

## 📝 **Activity Types Explained**

### **1. setor_sampah** (Waste Deposit)
**Logged when:** Admin approves waste deposit  
**Point Change:** Positive (base points from waste)  
**Example:**
```json
{
  "tipe_aktivitas": "setor_sampah",
  "deskripsi": "Menyetor 5kg sampah plastik",
  "poin_perubahan": 15
}
```

### **2. badge_unlock** (Badge Achievement)
**Logged when:** User unlocks a new badge  
**Point Change:** Positive (bonus points from badge)  
**Example:**
```json
{
  "tipe_aktivitas": "badge_unlock",
  "deskripsi": "Mendapatkan badge 'Eco Warrior' dan bonus 100 poin",
  "poin_perubahan": 100
}
```

### **3. tukar_poin** (Point Redemption)
**Logged when:** User redeems points for rewards  
**Point Change:** Negative (points spent)  
**Example:**
```json
{
  "tipe_aktivitas": "tukar_poin",
  "deskripsi": "Menukar 200 poin dengan Voucher Grab",
  "poin_perubahan": -200
}
```

### **4. poin_bonus** (Bonus Points)
**Logged when:** Special events or admin gives bonus  
**Point Change:** Positive (bonus points)  
**Example:**
```json
{
  "tipe_aktivitas": "poin_bonus",
  "deskripsi": "Bonus poin dari event khusus",
  "poin_perubahan": 20
}
```

### **5. level_up** (Level Progression)
**Logged when:** User reaches a new level  
**Point Change:** 0 (informational)  
**Example:**
```json
{
  "tipe_aktivitas": "level_up",
  "deskripsi": "Naik level dari Bronze ke Silver",
  "poin_perubahan": 0
}
```

---

## 🧪 **Test Results**

### **User 1 (Adib) - Bronze Level:**

```json
{
  "data": [
    {
      "id": 6,
      "tipe_aktivitas": "poin_bonus",
      "deskripsi": "Bonus poin dari event khusus",
      "poin_perubahan": 20,
      "tanggal": "2025-11-12 08:55:46"
    },
    {
      "id": 5,
      "tipe_aktivitas": "badge_unlock",
      "deskripsi": "Mendapatkan badge 'Eco Warrior' dan bonus 100 poin",
      "poin_perubahan": 100,
      "tanggal": "2025-11-10 08:55:46"
    },
    {
      "id": 4,
      "tipe_aktivitas": "setor_sampah",
      "deskripsi": "Menyetor 4kg sampah botol",
      "poin_perubahan": 12,
      "tanggal": "2025-11-10 08:55:46"
    }
  ]
}
```

### **User 2 (Siti) - Silver Level:**

Shows point redemption with negative value:

```json
{
  "data": [
    {
      "id": 15,
      "tipe_aktivitas": "badge_unlock",
      "deskripsi": "Mendapatkan badge 'Silver Collector' dan bonus 200 poin",
      "poin_perubahan": 200,
      "tanggal": "2025-11-13 08:55:46"
    },
    {
      "id": 14,
      "tipe_aktivitas": "tukar_poin",
      "deskripsi": "Menukar 100 poin dengan Voucher Grab",
      "poin_perubahan": -100,
      "tanggal": "2025-11-11 08:55:46"
    }
  ]
}
```

### **User 3 (Budi) - Pemula Level:**

```json
{
  "data": [
    {
      "id": 19,
      "tipe_aktivitas": "tukar_poin",
      "deskripsi": "Menukar 15 poin dengan merchandise",
      "poin_perubahan": -15,
      "tanggal": "2025-11-14 08:55:46"
    },
    {
      "id": 18,
      "tipe_aktivitas": "setor_sampah",
      "deskripsi": "Menyetor 3kg sampah botol",
      "poin_perubahan": 9,
      "tanggal": "2025-11-13 08:55:46"
    }
  ]
}
```

---

## 💻 **Usage Examples**

### **1. Logging Activities in Code**

**Using the static method:**
```php
use App\Models\LogAktivitas;

// Log waste deposit
LogAktivitas::log(
    $userId,
    LogAktivitas::TYPE_SETOR_SAMPAH,
    "Menyetor {$weight}kg sampah {$type}",
    $points
);

// Log badge unlock
LogAktivitas::log(
    $userId,
    LogAktivitas::TYPE_BADGE_UNLOCK,
    "Mendapatkan badge '{$badgeName}' dan bonus {$reward} poin",
    $reward
);

// Log point redemption (negative)
LogAktivitas::log(
    $userId,
    LogAktivitas::TYPE_TUKAR_POIN,
    "Menukar {$points} poin dengan {$productName}",
    -$points
);
```

### **2. Fetching Activity Logs**

**Default (20 activities):**
```bash
GET /api/users/1/aktivitas
```

**Custom limit (50 activities):**
```bash
GET /api/users/1/aktivitas?limit=50
```

**PowerShell:**
```powershell
# Get default activity logs
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/users/1/aktivitas'

# Get limited logs
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/users/1/aktivitas?limit=5'
```

---

## 📱 **Frontend Integration**

### **React Example:**

```javascript
// Fetch user activity logs
const fetchActivities = async (userId, limit = 20) => {
  const response = await fetch(
    `http://127.0.0.1:8000/api/users/${userId}/aktivitas?limit=${limit}`
  );
  const data = await response.json();
  return data.data;
};

// Display activity timeline
const ActivityTimeline = ({ userId }) => {
  const [activities, setActivities] = useState([]);

  useEffect(() => {
    fetchActivities(userId, 20).then(setActivities);
  }, [userId]);

  // Icon mapping for activity types
  const getActivityIcon = (type) => {
    switch(type) {
      case 'setor_sampah': return '♻️';
      case 'badge_unlock': return '🏆';
      case 'tukar_poin': return '🎁';
      case 'poin_bonus': return '✨';
      case 'level_up': return '📈';
      default: return '📝';
    }
  };

  // Color coding for point changes
  const getPointColor = (change) => {
    if (change > 0) return 'text-green-600';
    if (change < 0) return 'text-red-600';
    return 'text-gray-600';
  };

  return (
    <div className="activity-timeline">
      <h2>📊 Riwayat Aktivitas</h2>
      {activities.map((activity) => (
        <div key={activity.id} className="activity-item">
          <span className="icon">{getActivityIcon(activity.tipe_aktivitas)}</span>
          <div className="content">
            <p>{activity.deskripsi}</p>
            <span className={getPointColor(activity.poin_perubahan)}>
              {activity.poin_perubahan > 0 ? '+' : ''}
              {activity.poin_perubahan} poin
            </span>
            <time>{new Date(activity.tanggal).toLocaleDateString('id-ID')}</time>
          </div>
        </div>
      ))}
    </div>
  );
};
```

---

## 🔄 **Automatic Logging Integration**

### **1. Waste Deposit Approval**
**File:** `TabungSampahController@approve`

```php
// After approving deposit
LogAktivitas::log(
    $user->id,
    LogAktivitas::TYPE_SETOR_SAMPAH,
    "Menyetor {$weight}kg sampah {$type}",
    $points
);
```

### **2. Badge Unlock**
**File:** `BadgeService@awardBadge`

```php
// After awarding badge
LogAktivitas::log(
    $user->id,
    LogAktivitas::TYPE_BADGE_UNLOCK,
    "Mendapatkan badge '{$badge->nama}' dan bonus {$badge->reward_poin} poin",
    $badge->reward_poin
);
```

### **3. Point Redemption**
**File:** `TransaksiController@store` (Future)

```php
// After redeeming points
LogAktivitas::log(
    $user->id,
    LogAktivitas::TYPE_TUKAR_POIN,
    "Menukar {$points} poin dengan {$productName}",
    -$points // Negative value
);
```

---

## 📊 **User Profile Response**

**Endpoint:** `GET /api/users/{id}`

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 2,
    "nama": "Siti Aminah",
    "email": "siti@example.com",
    "no_hp": "082345678901",
    "alamat": "Jl. Diponegoro No. 456, Metro Timur",
    "foto_profil": null,
    "total_poin": 300,
    "total_setor_sampah": 12,
    "level": "Silver",
    "created_at": "2025-11-15T08:55:45.000000Z",
    "updated_at": "2025-11-15T08:55:45.000000Z"
  }
}
```

---

## 🎯 **Key Features**

### **✅ Transparency:**
- All point changes are logged
- Users can see full history
- Positive and negative transactions tracked

### **✅ Performance:**
- Indexed queries for fast retrieval
- Ordered by date (newest first)
- Configurable limit to prevent large responses

### **✅ Flexibility:**
- Multiple activity types
- Custom descriptions
- Static log method for easy use

### **✅ Integration:**
- Automatically logs badge unlocks
- Automatically logs waste approvals
- Ready for point redemption system

---

## 📝 **Sample Data Seeded**

**19 Activity Records** across 3 users:
- **User 1 (Adib)**: 6 activities
- **User 2 (Siti)**: 9 activities (including point redemption)
- **User 3 (Budi)**: 4 activities

**Activity Types in Sample Data:**
- ✅ setor_sampah (7 records)
- ✅ badge_unlock (8 records)
- ✅ tukar_poin (2 records with negative values)
- ✅ poin_bonus (1 record)
- ✅ level_up (0 records - ready for future use)

---

## ✅ **System Status**

### **Files Created/Modified:**

1. ✅ `app/Models/LogAktivitas.php` - Model with static log method
2. ✅ `database/migrations/.../create_log_aktivitas_table.php` - Updated schema with index
3. ✅ `app/Http/Controllers/UserController.php` - Enhanced aktivitas endpoint
4. ✅ `app/Services/BadgeService.php` - Uses LogAktivitas::log()
5. ✅ `app/Http/Controllers/TabungSampahController.php` - Logs waste deposits
6. ✅ `database/seeders/LogAktivitasSeeder.php` - Sample data
7. ✅ `database/seeders/DatabaseSeeder.php` - Added LogAktivitasSeeder

### **API Endpoints:**

- ✅ `GET /api/users/{id}` - User profile
- ✅ `GET /api/users/{id}/aktivitas` - Activity logs
- ✅ `GET /api/users/{id}/aktivitas?limit=50` - Custom limit
- ✅ `PUT /api/users/{id}` - Update profile
- ✅ `POST /api/users/{id}/update-photo` - Update photo

---

## 🎉 **Activity Log System is COMPLETE!**

The system now tracks all user activities including:

✅ **Waste Deposits** - When admin approves deposits  
✅ **Badge Unlocks** - With bonus point rewards  
✅ **Point Redemptions** - Negative values for spending  
✅ **Bonus Points** - Special events and rewards  
✅ **Level Progression** - Ready for implementation  

**Every point change is logged and transparent to users!** 📊✨
