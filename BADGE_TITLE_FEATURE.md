# 🏆 Badge Title Feature - Backend Implementation

## ✅ Status: COMPLETED

**Date:** December 24, 2025

---

## 📝 Feature Overview

User dapat memilih badge yang sudah diklaim sebagai "title" yang ditampilkan di profile mereka.

### Requirements:
1. ✅ User dapat memilih badge yang sudah diklaim sebagai "title"
2. ✅ Dropdown selector di ProfilHeader (frontend)
3. ✅ Badge title disimpan ke backend (`/users/{id}/badge-title`)
4. ✅ Only unlocked badges can be selected as title

---

## 🔧 Backend Changes

### 1. Database Migration

**File:** `database/migrations/2025_12_24_185926_add_badge_title_to_users_table.php`

```php
$table->unsignedBigInteger('badge_title_id')->nullable()->after('level');
$table->foreign('badge_title_id')
    ->references('badge_id')
    ->on('badges')
    ->onDelete('set null'); // If badge deleted, reset to null
```

### 2. User Model

**File:** `app/Models/User.php`

Added:
- `badge_title_id` to `$fillable`
- `badgeTitle()` relationship

```php
protected $fillable = [
    // ... other fields
    'badge_title_id', // Selected badge to display as user title
];

public function badgeTitle()
{
    return $this->belongsTo(\App\Models\Badge::class, 'badge_title_id', 'badge_id');
}
```

### 3. New Endpoints

**File:** `app/Http/Controllers/UserController.php`

---

## 📚 API Documentation

### 1. Get Badge Title

**Endpoint:** `GET /api/users/{userId}/badge-title`

**Authentication:** Required (Sanctum token)

**Access:** Public (any authenticated user can see)

**Response:**
```json
{
    "status": "success",
    "data": {
        "badge_title_id": 1,
        "badge_title": {
            "badge_id": 1,
            "nama": "Eco Warrior",
            "deskripsi": "Setor sampah 10 kali",
            "icon": "🌿"
        }
    }
}
```

**If no badge title set:**
```json
{
    "status": "success",
    "data": {
        "badge_title_id": null,
        "badge_title": null
    }
}
```

---

### 2. Set Badge Title

**Endpoint:** `PUT /api/users/{userId}/badge-title`

**Authentication:** Required (Sanctum token)

**Access:** Owner only (IDOR protection)

**Request Body:**
```json
{
    "badge_id": 1
}
```

**To remove badge title:**
```json
{
    "badge_id": null
}
```

**Success Response:**
```json
{
    "status": "success",
    "message": "Badge title berhasil diatur",
    "data": {
        "badge_title_id": 1,
        "badge_title": {
            "badge_id": 1,
            "nama": "Eco Warrior",
            "deskripsi": "Setor sampah 10 kali",
            "icon": "🌿"
        }
    }
}
```

**Error: Badge not unlocked:**
```json
{
    "status": "error",
    "message": "Anda belum membuka badge ini. Hanya badge yang sudah diklaim yang dapat dijadikan title."
}
```

**Error: Unauthorized (not owner):**
```json
{
    "status": "error",
    "message": "Unauthorized to set badge title for this user"
}
```

---

### 3. Get Unlocked Badges List

**Endpoint:** `GET /api/users/{userId}/unlocked-badges`

**Authentication:** Required (Sanctum token)

**Access:** Owner only (IDOR protection)

**Use Case:** For badge title selector dropdown

**Response:**
```json
{
    "status": "success",
    "data": {
        "unlocked_badges": [
            {
                "badge_id": 1,
                "nama": "Eco Warrior",
                "deskripsi": "Setor sampah 10 kali",
                "icon": "🌿",
                "reward_poin": 100,
                "tipe": "setor",
                "tanggal_dapat": "2025-12-20 10:30:00"
            },
            {
                "badge_id": 2,
                "nama": "Point Collector",
                "deskripsi": "Kumpulkan 500 poin",
                "icon": "💰",
                "reward_poin": 50,
                "tipe": "poin",
                "tanggal_dapat": "2025-12-15 14:22:00"
            }
        ],
        "count": 2,
        "current_badge_title_id": 1
    }
}
```

---

## 🎯 Frontend Integration Guide

### 1. Fetch Unlocked Badges for Selector

```javascript
const fetchUnlockedBadges = async (userId) => {
  const response = await fetch(`/api/users/${userId}/unlocked-badges`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  const data = await response.json();
  return data.data; // { unlocked_badges: [...], count, current_badge_title_id }
};
```

### 2. Set Badge Title

```javascript
const setBadgeTitle = async (userId, badgeId) => {
  const response = await fetch(`/api/users/${userId}/badge-title`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ badge_id: badgeId })
  });
  return await response.json();
};

// To remove badge title
const removeBadgeTitle = async (userId) => {
  return setBadgeTitle(userId, null);
};
```

### 3. Get Current Badge Title (for profile display)

```javascript
const getBadgeTitle = async (userId) => {
  const response = await fetch(`/api/users/${userId}/badge-title`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  const data = await response.json();
  return data.data.badge_title; // { badge_id, nama, deskripsi, icon } or null
};
```

### 4. Example React Component (ProfilHeader)

```jsx
const ProfilHeader = ({ userId }) => {
  const [unlockedBadges, setUnlockedBadges] = useState([]);
  const [currentBadgeTitle, setCurrentBadgeTitle] = useState(null);
  const [selectedBadgeId, setSelectedBadgeId] = useState(null);

  useEffect(() => {
    const loadBadges = async () => {
      const data = await fetchUnlockedBadges(userId);
      setUnlockedBadges(data.unlocked_badges);
      setSelectedBadgeId(data.current_badge_title_id);
    };
    loadBadges();
  }, [userId]);

  const handleBadgeSelect = async (badgeId) => {
    const result = await setBadgeTitle(userId, badgeId);
    if (result.status === 'success') {
      setSelectedBadgeId(badgeId);
      setCurrentBadgeTitle(result.data.badge_title);
    }
  };

  return (
    <div className="profile-header">
      <h2>My Profile</h2>
      
      {/* Badge Title Selector */}
      <div className="badge-title-selector">
        <label>Badge Title:</label>
        <select 
          value={selectedBadgeId || ''} 
          onChange={(e) => handleBadgeSelect(e.target.value || null)}
        >
          <option value="">-- No Title --</option>
          {unlockedBadges.map(badge => (
            <option key={badge.badge_id} value={badge.badge_id}>
              {badge.icon} {badge.nama}
            </option>
          ))}
        </select>
      </div>

      {/* Display Current Badge Title */}
      {currentBadgeTitle && (
        <div className="current-badge-title">
          <span className="badge-icon">{currentBadgeTitle.icon}</span>
          <span className="badge-name">{currentBadgeTitle.nama}</span>
        </div>
      )}
    </div>
  );
};
```

---

## 🧪 Testing

### Test 1: Get Empty Badge Title

```bash
curl -X GET http://localhost:8000/api/users/1/badge-title \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Test 2: Set Badge Title (must be unlocked)

```bash
curl -X PUT http://localhost:8000/api/users/1/badge-title \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"badge_id": 1}'
```

### Test 3: Get Unlocked Badges

```bash
curl -X GET http://localhost:8000/api/users/1/unlocked-badges \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Test 4: Try to Set Non-Unlocked Badge (should fail)

```bash
curl -X PUT http://localhost:8000/api/users/1/badge-title \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"badge_id": 999}'
# Expected: 400 error "Anda belum membuka badge ini..."
```

---

## 📁 Files Modified

| File | Changes |
|------|---------|
| `database/migrations/2025_12_24_185926_add_badge_title_to_users_table.php` | New migration |
| `app/Models/User.php` | Added `badge_title_id` to fillable, added `badgeTitle()` relationship |
| `app/Http/Controllers/UserController.php` | Added 3 new methods: `getBadgeTitle`, `setBadgeTitle`, `badgesList` |
| `routes/api.php` | Added 3 new routes |

---

## ✅ Verification Checklist

- [x] Migration created and executed
- [x] `badge_title_id` column exists in `users` table
- [x] Foreign key constraint to `badges` table
- [x] User model updated with `$fillable` and relationship
- [x] `GET /api/users/{id}/badge-title` endpoint working
- [x] `PUT /api/users/{id}/badge-title` endpoint working  
- [x] `GET /api/users/{id}/unlocked-badges` endpoint working
- [x] IDOR protection (owner only for set/list)
- [x] Validation: only unlocked badges can be selected
- [x] Routes registered in api.php

---

## 🚀 Ready for Frontend Integration!

Backend is complete. Frontend can now:
1. Fetch user's unlocked badges for dropdown selector
2. Display current badge title on profile
3. Allow user to change their badge title
4. Remove badge title (set to null)

**Note:** Badge title is persisted in database, so it will be available across sessions and devices. No need for localStorage fallback if backend is working.
