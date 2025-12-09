# 🔌 API Endpoints for Frontend Integration

**Base URL:** `http://127.0.0.1:8000/api`  
**Date:** November 15, 2025  
**Format:** JSON

---

## 📑 **Table of Contents**

1. [Authentication](#authentication)
2. [User Profile](#user-profile)
3. [Dashboard & Statistics](#dashboard--statistics)
4. [Badges & Achievements](#badges--achievements)
5. [Activity Logs](#activity-logs)
6. [Leaderboard](#leaderboard)
7. [Waste Management (Tabung Sampah)](#waste-management-tabung-sampah)
8. [Transactions](#transactions)
9. [Pickup Schedule (Jadwal Penyetoran)](#pickup-schedule-jadwal-penyetoran)
10. [Waste Types (Jenis Sampah)](#waste-types-jenis-sampah)
11. [Products (Produk)](#products-produk)
12. [Articles (Artikel)](#articles-artikel)

---

## 🔐 **1. Authentication**

### **POST** `/api/login`
Login user and get access token (optional with Sanctum)

**Request:**
```json
{
  "email": "adib@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "nama": "Adib Surya",
      "email": "adib@example.com",
      "total_poin": 150,
      "level": "Bronze"
    }
  }
}
```

---

### **POST** `/api/register`
Register new user

**Request:**
```json
{
  "nama": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "no_hp": "081234567890",
  "alamat": "Jl. Example No. 123"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Registrasi berhasil",
  "data": {
    "user": { ... }
  }
}
```

---

### **POST** `/api/logout`
Logout user (requires authentication)

**Response:**
```json
{
  "status": "success",
  "message": "Logout berhasil"
}
```

---

### **GET** `/api/profile`
Get authenticated user profile (requires authentication)

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "nama": "Adib Surya",
    "email": "adib@example.com",
    "no_hp": "081234567890",
    "alamat": "Jl. Gatot Subroto No. 123",
    "foto_profil": null,
    "total_poin": 150,
    "total_setor_sampah": 5,
    "level": "Bronze"
  }
}
```

---

### **PUT** `/api/profile`
Update authenticated user profile (requires authentication)

**Request:**
```json
{
  "nama": "Updated Name",
  "email": "newemail@example.com",
  "no_hp": "082345678901",
  "alamat": "New Address"
}
```

---

## 👤 **2. User Profile**

### **GET** `/api/users/{id}`
Get user profile by ID

**Example:** `GET /api/users/1`

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "nama": "Adib Surya",
    "email": "adib@example.com",
    "no_hp": "081234567890",
    "alamat": "Jl. Gatot Subroto No. 123",
    "foto_profil": null,
    "total_poin": 150,
    "total_setor_sampah": 5,
    "level": "Bronze",
    "created_at": "2025-11-15T08:55:45.000000Z",
    "updated_at": "2025-11-15T08:55:45.000000Z"
  }
}
```

---

### **PUT** `/api/users/{id}`
Update user profile

**Request:**
```json
{
  "nama": "Updated Name",
  "no_hp": "082345678901",
  "alamat": "Updated Address"
}
```

---

### **POST** `/api/users/{id}/update-photo`
Update user profile photo

**Request:** (multipart/form-data)
```
foto_profil: [file] (jpeg, jpg, png, gif, max 2MB)
```

**Response:**
```json
{
  "status": "success",
  "message": "Photo updated successfully",
  "data": {
    "foto_profil": "uploads/profiles/12345.jpg",
    "foto_url": "http://127.0.0.1:8000/storage/uploads/profiles/12345.jpg"
  }
}
```

---

### **GET** `/api/users/{id}/tabung-sampah`
Get user's waste deposit history

**Example:** `GET /api/users/1/tabung-sampah`

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "nama_lengkap": "Adib Surya",
      "jenis_sampah": "Plastik",
      "berat_kg": 5.5,
      "poin_didapat": 15,
      "status": "approved",
      "created_at": "2025-11-10T10:30:00.000000Z"
    }
  ]
}
```

---

### **GET** `/api/users/{id}/transaksi`
Get user's transaction history

**Example:** `GET /api/users/1/transaksi`

---

## 📊 **3. Dashboard & Statistics**

### **GET** `/api/dashboard/stats/{userId}`
Get user statistics for dashboard

**Example:** `GET /api/dashboard/stats/1`

**Response:**
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "nama": "Adib Surya",
      "total_poin": 150,
      "total_setor_sampah": 5,
      "level": "Bronze"
    },
    "statistics": {
      "rank": 2,
      "total_users": 3,
      "monthly_poin": 50,
      "monthly_setor": 2,
      "next_level": "Silver",
      "progress_to_next_level": 50.00,
      "poin_needed": 150
    },
    "recent_deposits": [...]
  }
}
```

---

### **GET** `/api/dashboard/global-stats`
Get platform-wide statistics

**Response:**
```json
{
  "status": "success",
  "data": {
    "total_users": 3,
    "total_poin_distributed": 500,
    "total_deposits": 19,
    "total_weight_kg": 95.50,
    "monthly_growth": 15.5,
    "this_month_deposits": 8
  }
}
```

---

## 🏆 **4. Badges & Achievements**

### **GET** `/api/badges`
Get all available badges

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
    },
    {
      "id": 2,
      "nama": "Eco Warrior",
      "deskripsi": "Setor sampah 5 kali",
      "icon": "♻️",
      "syarat_poin": 0,
      "syarat_setor": 5,
      "reward_poin": 100,
      "tipe": "setor"
    }
  ]
}
```

---

### **GET** `/api/users/{id}/badges`
Get user's unlocked badges

**Example:** `GET /api/users/1/badges`

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
      "tanggal_dapat": "2025-11-05T08:30:00.000000Z"
    }
  ]
}
```

---

### **GET** `/api/users/{userId}/badge-progress`
Get user's progress towards all badges

**Example:** `GET /api/users/1/badge-progress`

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
      "progress": 80
    }
  ]
}
```

---

### **POST** `/api/users/{userId}/check-badges`
Manually check and award badges (for testing)

**Example:** `POST /api/users/1/check-badges`

**Response:**
```json
{
  "status": "success",
  "message": "Badge(s) baru diberikan!",
  "data": {
    "newly_unlocked": [
      {
        "id": 2,
        "nama": "Eco Warrior",
        "reward_poin": 100
      }
    ],
    "count": 1
  }
}
```

---

## 📝 **5. Activity Logs**

### **GET** `/api/users/{id}/aktivitas`
Get user's activity history

**Query Parameters:**
- `limit` - Number of activities (default: 20, max: 100)

**Example:** `GET /api/users/1/aktivitas?limit=10`

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "tipe_aktivitas": "setor_sampah",
      "deskripsi": "Menyetor 5kg sampah plastik",
      "poin_perubahan": 15,
      "tanggal": "2025-11-05T08:30:00Z"
    },
    {
      "id": 2,
      "tipe_aktivitas": "badge_unlock",
      "deskripsi": "Mendapatkan badge 'Pemula Peduli' dan bonus 50 poin",
      "poin_perubahan": 50,
      "tanggal": "2025-11-05T08:31:00Z"
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

**Activity Types:**
- `setor_sampah` - Waste deposit (positive points)
- `badge_unlock` - Badge earned (positive points)
- `tukar_poin` - Point redemption (negative points)
- `poin_bonus` - Bonus points
- `level_up` - Level progression

---

## 🏅 **6. Leaderboard**

### **GET** `/api/dashboard/leaderboard`
Get top users ranked by different criteria

**Query Parameters:**
- `type` - Ranking type: `poin` (default), `setor`, or `badge`
- `limit` - Number of users (default: 10, max: 50)

**Examples:**
```
GET /api/dashboard/leaderboard                    → Top 10 by points
GET /api/dashboard/leaderboard?type=setor         → Top 10 by deposits
GET /api/dashboard/leaderboard?type=badge         → Top 10 by badges
GET /api/dashboard/leaderboard?limit=5            → Top 5 users
GET /api/dashboard/leaderboard?type=setor&limit=3 → Top 3 by deposits
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "rank": 1,
      "user_id": 2,
      "nama": "Siti Aminah",
      "foto_profil": null,
      "total_poin": 300,
      "total_setor_sampah": 12,
      "level": "Silver",
      "badge_count": 5
    },
    {
      "rank": 2,
      "user_id": 1,
      "nama": "Adib Surya",
      "foto_profil": null,
      "total_poin": 150,
      "total_setor_sampah": 5,
      "level": "Bronze",
      "badge_count": 3
    }
  ]
}
```

---

## ♻️ **7. Waste Management (Tabung Sampah)**

### **GET** `/api/tabung-sampah`
Get all waste deposits

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "jadwal_id": 1,
      "nama_lengkap": "Adib Surya",
      "no_hp": "081234567890",
      "titik_lokasi": "Rumah",
      "jenis_sampah": "Plastik",
      "foto_sampah": "uploads/sampah/12345.jpg",
      "berat_kg": 5.5,
      "poin_didapat": 15,
      "status": "pending",
      "created_at": "2025-11-14T10:30:00.000000Z"
    }
  ]
}
```

---

### **POST** `/api/tabung-sampah`
Submit new waste deposit

**Request:** (multipart/form-data)
```json
{
  "user_id": 1,
  "jadwal_id": 1,
  "nama_lengkap": "Adib Surya",
  "no_hp": "081234567890",
  "titik_lokasi": "Rumah",
  "jenis_sampah": "Plastik",
  "foto_sampah": [file]
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Setor sampah berhasil!",
  "data": { ... }
}
```

---

### **GET** `/api/tabung-sampah/{id}`
Get specific waste deposit

**Example:** `GET /api/tabung-sampah/1`

---

### **PUT** `/api/tabung-sampah/{id}`
Update waste deposit

---

### **DELETE** `/api/tabung-sampah/{id}`
Delete waste deposit

---

### **POST** `/api/tabung-sampah/{id}/approve`
Approve waste deposit (Admin) - **Auto-checks badges!**

**Request:**
```json
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
    "tabung_sampah": { ... },
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

**Note:** This endpoint automatically:
- Awards base points to user
- Checks for new badge unlocks
- Awards badge bonus points
- Creates activity logs
- Creates notifications

---

### **POST** `/api/tabung-sampah/{id}/reject`
Reject waste deposit (Admin)

**Response:**
```json
{
  "status": "success",
  "message": "Penyetoran ditolak",
  "data": { ... }
}
```

---

## 💰 **8. Transactions**

### **GET** `/api/transaksi`
Get all transactions

---

### **POST** `/api/transaksi`
Create new transaction (point redemption)

**Request:**
```json
{
  "user_id": 1,
  "produk_id": 1,
  "kategori_id": 1,
  "jumlah_poin": 100,
  "status": "pending"
}
```

---

### **GET** `/api/transaksi/{id}`
Get specific transaction

---

### **PUT** `/api/transaksi/{id}`
Update transaction

---

### **DELETE** `/api/transaksi/{id}`
Delete transaction

---

## 📅 **9. Pickup Schedule (Jadwal Penyetoran)**

### **GET** `/api/jadwal-penyetoran`
Get all pickup schedules

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "hari": "Senin",
      "jam_mulai": "08:00:00",
      "jam_selesai": "12:00:00",
      "lokasi": "Metro Barat",
      "status": "aktif"
    }
  ]
}
```

---

### **GET** `/api/jadwal-penyetoran-aktif`
Get only active pickup schedules

**Response:**
```json
{
  "status": "success",
  "data": [...]
}
```

---

### **GET** `/api/jadwal-penyetoran/{id}`
Get specific pickup schedule

---

### **POST** `/api/jadwal-penyetoran`
Create new pickup schedule (Admin)

---

### **PUT** `/api/jadwal-penyetoran/{id}`
Update pickup schedule (Admin)

---

### **DELETE** `/api/jadwal-penyetoran/{id}`
Delete pickup schedule (Admin)

---

## 🗑️ **10. Waste Types (Jenis Sampah)**

### **GET** `/api/jenis-sampah`
Get all waste types

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "nama": "Plastik",
      "deskripsi": "Botol plastik, kemasan, dll",
      "harga_per_kg": 3000,
      "satuan": "kg"
    },
    {
      "id": 2,
      "nama": "Kertas",
      "deskripsi": "Kardus, koran, majalah",
      "harga_per_kg": 2000,
      "satuan": "kg"
    }
  ]
}
```

---

### **GET** `/api/jenis-sampah/{id}`
Get specific waste type

---

## 🎁 **11. Products (Produk)**

### **GET** `/api/produk`
Get all products for point redemption

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "nama": "Voucher Grab 50k",
      "deskripsi": "Voucher transport Grab senilai 50 ribu",
      "harga_poin": 100,
      "stok": 50,
      "gambar": "uploads/products/grab.jpg"
    }
  ]
}
```

---

### **GET** `/api/produk/{id}`
Get specific product

---

## 📰 **12. Articles (Artikel)**

### **GET** `/api/artikel`
Get all articles

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "judul": "Tips Memilah Sampah",
      "slug": "tips-memilah-sampah",
      "konten": "...",
      "gambar": "uploads/articles/tips.jpg",
      "created_at": "2025-11-10T08:00:00.000000Z"
    }
  ]
}
```

---

### **GET** `/api/artikel/{slug}`
Get specific article by slug

**Example:** `GET /api/artikel/tips-memilah-sampah`

---

## 🧪 **13. Test Endpoint**

### **GET** `/api/user`
Simple test endpoint

**Response:**
```json
{
  "status": "success",
  "message": "API is working!",
  "data": "good"
}
```

---

## 📱 **Frontend Integration Examples**

### **React/JavaScript Example:**

```javascript
const API_BASE_URL = 'http://127.0.0.1:8000/api';

// 1. Login
const login = async (email, password) => {
  const response = await fetch(`${API_BASE_URL}/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  return response.json();
};

// 2. Get User Profile
const getUserProfile = async (userId) => {
  const response = await fetch(`${API_BASE_URL}/users/${userId}`);
  return response.json();
};

// 3. Get User Badges
const getUserBadges = async (userId) => {
  const response = await fetch(`${API_BASE_URL}/users/${userId}/badges`);
  return response.json();
};

// 4. Get Leaderboard
const getLeaderboard = async (type = 'poin', limit = 10) => {
  const response = await fetch(
    `${API_BASE_URL}/dashboard/leaderboard?type=${type}&limit=${limit}`
  );
  return response.json();
};

// 5. Get Activity Logs
const getActivityLogs = async (userId, limit = 20) => {
  const response = await fetch(
    `${API_BASE_URL}/users/${userId}/aktivitas?limit=${limit}`
  );
  return response.json();
};

// 6. Get Dashboard Stats
const getDashboardStats = async (userId) => {
  const response = await fetch(`${API_BASE_URL}/dashboard/stats/${userId}`);
  return response.json();
};

// 7. Submit Waste Deposit
const submitWaste = async (formData) => {
  const response = await fetch(`${API_BASE_URL}/tabung-sampah`, {
    method: 'POST',
    body: formData // multipart/form-data
  });
  return response.json();
};

// 8. Get All Badges
const getAllBadges = async () => {
  const response = await fetch(`${API_BASE_URL}/badges`);
  return response.json();
};
```

---

## 🔑 **Important Notes**

### **CORS Configuration:**
✅ CORS is enabled via `HandleCors` middleware  
✅ All origins are allowed (configured in `config/cors.php`)  
✅ Frontend at `localhost:5173` can access API

### **Authentication:**
- Most endpoints are currently **public** (no authentication required)
- Login returns user data (Sanctum token optional)
- Some endpoints require `auth:sanctum` middleware (marked in routes)

### **Response Format:**
All endpoints return JSON with this structure:
```json
{
  "status": "success" | "error",
  "message": "Optional message",
  "data": { ... }
}
```

### **Error Responses:**
```json
{
  "status": "error",
  "message": "Error description",
  "errors": { ... } // Validation errors if applicable
}
```

### **File Uploads:**
- Use `multipart/form-data` for file uploads
- Supported formats: jpeg, jpg, png, gif
- Max file size: 2MB
- Files stored in `public/uploads/` directory

---

## 📊 **Quick Reference Table**

| Feature | Method | Endpoint | Purpose |
|---------|--------|----------|---------|
| Login | POST | `/api/login` | Authenticate user |
| Get Profile | GET | `/api/users/{id}` | User profile data |
| Get Badges | GET | `/api/users/{id}/badges` | User's badges |
| Badge Progress | GET | `/api/users/{id}/badge-progress` | Progress tracking |
| Activity Logs | GET | `/api/users/{id}/aktivitas` | User activity history |
| Leaderboard | GET | `/api/dashboard/leaderboard` | Top users ranking |
| User Stats | GET | `/api/dashboard/stats/{id}` | Dashboard statistics |
| Submit Waste | POST | `/api/tabung-sampah` | New waste deposit |
| Approve Waste | POST | `/api/tabung-sampah/{id}/approve` | Admin approval |
| All Badges | GET | `/api/badges` | Available badges list |
| Global Stats | GET | `/api/dashboard/global-stats` | Platform metrics |

---

## 🎯 **Testing with cURL**

```bash
# Get leaderboard
curl http://127.0.0.1:8000/api/dashboard/leaderboard

# Get user profile
curl http://127.0.0.1:8000/api/users/1

# Get user badges
curl http://127.0.0.1:8000/api/users/1/badges

# Get activity logs
curl http://127.0.0.1:8000/api/users/1/aktivitas?limit=10

# Login
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"adib@example.com","password":"password"}'
```

---

**✅ All endpoints are tested and working!**  
**🚀 Ready for frontend integration!**

For detailed documentation, see:
- BADGE_REWARD_SYSTEM.md
- LEADERBOARD_SYSTEM.md
- ACTIVITY_LOG_SYSTEM.md
- GAMIFICATION_SYSTEM.md
