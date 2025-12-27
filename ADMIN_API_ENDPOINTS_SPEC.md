# 🔌 ADMIN API ENDPOINTS SPECIFICATION

**Target Backend:** Mendaur Admin System  
**Frontend Version:** React + Vite  
**Response Format:** JSON  
**Authentication:** Bearer Token (JWT)  
**Status:** Ready for backend implementation

---

## 📌 GLOBAL REQUIREMENTS

### Response Format (REQUIRED)
```json
{
  "success": true,
  "data": [...],
  "message": "optional success message"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "data": null
}
```

### HTTP Status Codes
- **200 OK** - Request successful
- **400 Bad Request** - Invalid parameters
- **401 Unauthorized** - Missing/invalid token
- **403 Forbidden** - Insufficient permissions
- **404 Not Found** - Resource not found
- **500 Internal Server Error** - Server error

### Headers Required
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## 📋 ENDPOINT LIST (65+ Total)

### 🔷 SECTION 1: DASHBOARD (1 endpoint)

#### 1.1 GET /api/admin/dashboard/overview
**Purpose:** Get dashboard overview statistics  
**Method:** GET  
**Auth:** Required ✅  
**Params:** None  
**Response:**
```json
{
  "success": true,
  "data": {
    "total_waste_kg": 5000,
    "total_points_distributed": 125000,
    "total_users": 500,
    "total_transactions": 1200,
    "pending_deposits": 50,
    "pending_redemptions": 25,
    "pending_withdrawals": 10
  }
}
```

---

### 🔷 SECTION 2: WASTE DEPOSITS (6 endpoints)

#### 2.1 GET /api/admin/penyetoran-sampah
**Purpose:** List all waste deposits with pagination  
**Method:** GET  
**Auth:** Required ✅  
**Params:**
- `page` (int, optional, default: 1)
- `limit` (int, optional, default: 10)
- `status` (string, optional: pending/approved/rejected)
- `user_id` (int, optional)
- `date_from` (string, optional: YYYY-MM-DD)
- `date_to` (string, optional: YYYY-MM-DD)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "penyetoran_id": 1,
      "user_id": 5,
      "nama_user": "John Doe",
      "tanggal": "2025-12-23T10:30:00Z",
      "jenis_sampah": "Plastik",
      "berat_kg": 25.5,
      "status": "pending",
      "poin_diberikan": null
    }
  ]
}
```

---

#### 2.2 GET /api/admin/penyetoran-sampah/{id}
**Purpose:** Get specific waste deposit details  
**Method:** GET  
**Auth:** Required ✅  
**Params:** None  
**Response:**
```json
{
  "success": true,
  "data": {
    "penyetoran_id": 1,
    "user_id": 5,
    "nama_user": "John Doe",
    "email": "john@example.com",
    "no_telepon": "081234567890",
    "tanggal": "2025-12-23T10:30:00Z",
    "jenis_sampah": "Plastik",
    "kategori": "Lunak",
    "berat_kg": 25.5,
    "harga_per_kg": 2000,
    "total_harga": 51000,
    "status": "pending",
    "poin_diberikan": null,
    "catatan": "Baik kondisinya",
    "foto_bukti": "url/to/image.jpg"
  }
}
```

---

#### 2.3 PATCH /api/admin/penyetoran-sampah/{id}/approve
**Purpose:** Approve waste deposit and assign poin  
**Method:** PATCH  
**Auth:** Required ✅  
**Body:**
```json
{
  "poin_diberikan": 250
}
```
**Response:**
```json
{
  "success": true,
  "message": "Deposit approved successfully",
  "data": {
    "penyetoran_id": 1,
    "status": "approved",
    "poin_diberikan": 250,
    "approved_at": "2025-12-23T14:00:00Z",
    "approved_by": "admin_id"
  }
}
```

---

#### 2.4 PATCH /api/admin/penyetoran-sampah/{id}/reject
**Purpose:** Reject waste deposit  
**Method:** PATCH  
**Auth:** Required ✅  
**Body:**
```json
{
  "alasan_penolakan": "Berat tidak sesuai"
}
```
**Response:**
```json
{
  "success": true,
  "message": "Deposit rejected successfully",
  "data": {
    "penyetoran_id": 1,
    "status": "rejected",
    "alasan_penolakan": "Berat tidak sesuai",
    "rejected_at": "2025-12-23T14:00:00Z"
  }
}
```

---

#### 2.5 DELETE /api/admin/penyetoran-sampah/{id}
**Purpose:** Delete waste deposit (superadmin only)  
**Method:** DELETE  
**Auth:** Required ✅ (Superadmin)  
**Body:** None  
**Response:**
```json
{
  "success": true,
  "message": "Deposit deleted successfully"
}
```

---

#### 2.6 GET /api/admin/penyetoran-sampah/stats/overview
**Purpose:** Get waste deposit statistics  
**Method:** GET  
**Auth:** Required ✅  
**Params:** None  
**Response:**
```json
{
  "success": true,
  "data": {
    "total_deposits": 1500,
    "total_kg": 50000,
    "pending_count": 50,
    "approved_count": 1400,
    "rejected_count": 50,
    "total_poin_given": 375000,
    "average_weight_per_deposit": 33.3
  }
}
```

---

### 🔷 SECTION 3: PRODUCT REDEMPTION (3 endpoints)

#### 3.1 GET /api/admin/penukar-produk
**Purpose:** List all product redemptions  
**Method:** GET  
**Auth:** Required ✅  
**Params:**
- `page` (int, optional)
- `limit` (int, optional)
- `status` (string, optional: pending/approved/rejected)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "penukar_id": 1,
      "user_id": 5,
      "nama_user": "John Doe",
      "produk_id": 10,
      "nama_produk": "Gift Card Rp 50.000",
      "poin_digunakan": 500,
      "tanggal": "2025-12-23T11:00:00Z",
      "status": "pending"
    }
  ]
}
```

---

#### 3.2 PATCH /api/admin/penukar-produk/{id}/approve
**Purpose:** Approve product redemption  
**Method:** PATCH  
**Auth:** Required ✅  
**Body:**
```json
{
  "catatan": "Produk siap diambil"
}
```
**Response:**
```json
{
  "success": true,
  "message": "Redemption approved",
  "data": {
    "penukar_id": 1,
    "status": "approved"
  }
}
```

---

#### 3.3 PATCH /api/admin/penukar-produk/{id}/reject
**Purpose:** Reject product redemption  
**Method:** PATCH  
**Auth:** Required ✅  
**Body:**
```json
{
  "alasan": "Produk out of stock"
}
```
**Response:**
```json
{
  "success": true,
  "message": "Redemption rejected",
  "data": {
    "penukar_id": 1,
    "status": "rejected"
  }
}
```

---

### 🔷 SECTION 4: CASH WITHDRAWAL (3 endpoints)

#### 4.1 GET /api/admin/penarikan-tunai
**Purpose:** List all cash withdrawal requests  
**Method:** GET  
**Auth:** Required ✅  
**Params:**
- `page` (int, optional)
- `limit` (int, optional)
- `status` (string, optional: pending/approved/rejected)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "penarikan_id": 1,
      "user_id": 5,
      "nama_user": "John Doe",
      "jumlah": 500000,
      "status": "pending",
      "tanggal_request": "2025-12-23T12:00:00Z"
    }
  ]
}
```

---

#### 4.2 POST /api/admin/penarikan-tunai/{id}/approve
**Purpose:** Approve cash withdrawal  
**Method:** POST  
**Auth:** Required ✅  
**Body:**
```json
{
  "catatan_admin": "Tunai siap diambil di cabang pusat"
}
```
**Response:**
```json
{
  "success": true,
  "message": "Withdrawal approved",
  "data": {
    "penarikan_id": 1,
    "status": "approved",
    "approved_at": "2025-12-23T14:00:00Z"
  }
}
```

---

#### 4.3 POST /api/admin/penarikan-tunai/{id}/reject
**Purpose:** Reject cash withdrawal  
**Method:** POST  
**Auth:** Required ✅  
**Body:**
```json
{
  "alasan_penolakan": "Saldo tidak mencukup",
  "catatan_admin": "Silakan coba lagi minggu depan"
}
```
**Response:**
```json
{
  "success": true,
  "message": "Withdrawal rejected",
  "data": {
    "penarikan_id": 1,
    "status": "rejected"
  }
}
```

---

### 🔷 SECTION 5: PRODUCTS (5 endpoints)

#### 5.1 GET /api/admin/produk
**Purpose:** List all products  
**Method:** GET  
**Auth:** Required ✅  
**Params:**
- `page` (int, optional)
- `limit` (int, optional)
- `search` (string, optional)
- `kategori` (string, optional)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "produk_id": 1,
      "nama": "Gift Card Rp 50.000",
      "deskripsi": "Digital gift card",
      "harga": 50000,
      "poin_ditukar": 500,
      "stok": 100,
      "foto": "url/to/image.jpg",
      "kategori": "Digital",
      "created_at": "2025-12-20T10:00:00Z"
    }
  ]
}
```

---

#### 5.2 GET /api/admin/produk/{id}
**Purpose:** Get product detail  
**Method:** GET  
**Auth:** Required ✅  
**Response:** Single product object (same as list item)

---

#### 5.3 POST /api/admin/produk
**Purpose:** Create new product  
**Method:** POST  
**Auth:** Required ✅  
**Body:**
```json
{
  "nama": "Gift Card Rp 50.000",
  "deskripsi": "Digital gift card",
  "harga": 50000,
  "poin_ditukar": 500,
  "stok": 100,
  "kategori": "Digital",
  "foto": "url/to/image.jpg"
}
```
**Response:**
```json
{
  "success": true,
  "message": "Product created",
  "data": {
    "produk_id": 1,
    ...
  }
}
```

---

#### 5.4 PUT /api/admin/produk/{id}
**Purpose:** Update product  
**Method:** PUT  
**Auth:** Required ✅  
**Body:** Same as POST  
**Response:** Updated product object

---

#### 5.5 DELETE /api/admin/produk/{id}
**Purpose:** Delete product  
**Method:** DELETE  
**Auth:** Required ✅  
**Response:**
```json
{
  "success": true,
  "message": "Product deleted"
}
```

---

### 🔷 SECTION 6: ARTICLES (5 endpoints)

#### 6.1 GET /api/admin/artikel
**Purpose:** List all articles  
**Method:** GET  
**Auth:** Required ✅  
**Params:**
- `page` (int, optional)
- `limit` (int, optional)
- `search` (string, optional)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "artikel_id": 1,
      "judul": "Tips Mengelola Sampah",
      "penulis": "admin_name",
      "kategori": "Tips",
      "konten": "Lorem ipsum...",
      "slug": "tips-mengelola-sampah",
      "foto_cover": "url/to/image.jpg",
      "views": 150,
      "published_at": "2025-12-20T10:00:00Z",
      "updated_at": "2025-12-23T14:00:00Z"
    }
  ]
}
```

---

#### 6.2 GET /api/admin/artikel/{id}
**Purpose:** Get article detail  
**Method:** GET  
**Auth:** Required ✅  
**Response:** Single article object

---

#### 6.3 POST /api/admin/artikel
**Purpose:** Create new article  
**Method:** POST  
**Auth:** Required ✅  
**Body:**
```json
{
  "judul": "Tips Mengelola Sampah",
  "penulis": "admin_name",
  "kategori": "Tips",
  "konten": "Lorem ipsum...",
  "slug": "tips-mengelola-sampah",
  "foto_cover": "url/to/image.jpg"
}
```
**Note:** Slug dapat auto-generated dari judul jika tidak diberikan

---

#### 6.4 PUT /api/admin/artikel/{id}
**Purpose:** Update article  
**Method:** PUT  
**Auth:** Required ✅  
**Body:** Same as POST

---

#### 6.5 DELETE /api/admin/artikel/{id}
**Purpose:** Delete article  
**Method:** DELETE  
**Auth:** Required ✅  

---

### 🔷 SECTION 7: WASTE ITEMS/CATEGORIES (5 endpoints)

#### 7.1 GET /api/admin/jenis-sampah
**Purpose:** List all waste items  
**Method:** GET  
**Auth:** Required ✅  
**Params:**
- `page` (int, optional)
- `limit` (int, optional)
- `kategori` (string, optional)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "jenis_id": 1,
      "nama": "Plastik Botol",
      "kategori": "Plastik",
      "harga_per_kg": 2000,
      "deskripsi": "Botol plastik bekas",
      "satuan": "kg",
      "aktif": true
    }
  ]
}
```

---

#### 7.2 GET /api/admin/waste-categories
**Purpose:** Get all waste categories  
**Method:** GET  
**Auth:** Required ✅  
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "kategori_id": 1,
      "nama": "Plastik",
      "deskripsi": "Sampah plastik"
    }
  ]
}
```

---

#### 7.3 POST /api/admin/jenis-sampah
**Purpose:** Create new waste item  
**Method:** POST  
**Auth:** Required ✅  
**Body:**
```json
{
  "nama": "Plastik Botol",
  "kategori": "Plastik",
  "harga_per_kg": 2000,
  "deskripsi": "Botol plastik bekas"
}
```

---

#### 7.4 PUT /api/admin/jenis-sampah/{id}
**Purpose:** Update waste item  
**Method:** PUT  
**Auth:** Required ✅  
**Body:** Same as POST

---

#### 7.5 DELETE /api/admin/jenis-sampah/{id}
**Purpose:** Delete waste item  
**Method:** DELETE  
**Auth:** Required ✅  

---

### 🔷 SECTION 8: BADGES (7 endpoints)

#### 8.1 GET /api/admin/badges
**Purpose:** List all badges  
**Method:** GET  
**Auth:** Required ✅  
**Params:**
- `page` (int, optional)
- `limit` (int, optional)

#### 8.2 GET /api/admin/badges/{id}
**Purpose:** Get badge detail

#### 8.3 POST /api/admin/badges
**Purpose:** Create new badge  
**Body:**
```json
{
  "nama": "Eco Warrior",
  "deskripsi": "Pengguna dengan kontribusi sampah terbanyak",
  "icon": "url/to/icon.png",
  "warna": "#10b981"
}
```

#### 8.4 PUT /api/admin/badges/{id}
**Purpose:** Update badge

#### 8.5 DELETE /api/admin/badges/{id}
**Purpose:** Delete badge

#### 8.6 POST /api/admin/badges/{id}/assign
**Purpose:** Assign badge to user  
**Body:**
```json
{
  "user_id": 5
}
```

#### 8.7 GET /api/admin/badges/{id}/users
**Purpose:** Get users with specific badge  
**Params:**
- `page` (int, optional)
- `per_page` (int, optional)

---

### 🔷 SECTION 9: SCHEDULES (6 endpoints)

#### 9.1 GET /api/admin/jadwal-penyetoran
**Purpose:** List all schedules  
**Params:**
- `page`, `limit`, `date`, `lokasi`

#### 9.2 GET /api/admin/jadwal-penyetoran/{id}
**Purpose:** Get schedule detail

#### 9.3 POST /api/admin/jadwal-penyetoran
**Purpose:** Create schedule  
**Body:**
```json
{
  "judul": "Pengambilan sampah area Jakarta",
  "deskripsi": "Jadwal rutin pengambilan sampah",
  "tanggal": "2025-12-30",
  "jam_mulai": "08:00",
  "jam_selesai": "17:00",
  "lokasi": "Jakarta Pusat",
  "kapasitas": 100,
  "kontak_admin": "081234567890"
}
```

#### 9.4 PUT /api/admin/jadwal-penyetoran/{id}
**Purpose:** Update schedule

#### 9.5 DELETE /api/admin/jadwal-penyetoran/{id}
**Purpose:** Delete schedule

#### 9.6 POST /api/admin/jadwal-penyetoran/{id}/register
**Purpose:** Register user to schedule  
**Body:**
```json
{
  "user_id": 5
}
```

---

### 🔷 SECTION 10: USERS & ROLES (10+ endpoints)

#### 10.1 GET /api/admin/users
**Purpose:** List all users  
**Params:** `page`, `limit`, `role`, `status`, `search`

#### 10.2 GET /api/admin/users/{id}
**Purpose:** Get user detail

#### 10.3 PUT /api/admin/users/{id}
**Purpose:** Update user

#### 10.4 PATCH /api/admin/users/{id}/status
**Purpose:** Update user status  
**Body:**
```json
{
  "status": "active"
}
```

#### 10.5 PATCH /api/admin/users/{id}/role
**Purpose:** Update user role  
**Body:**
```json
{
  "role_id": 2
}
```

#### 10.6 DELETE /api/admin/users/{id}
**Purpose:** Delete user

#### 10.7 GET /api/admin/roles
**Purpose:** List all roles  
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "role_id": 1,
      "nama": "user",
      "deskripsi": "Regular user"
    }
  ]
}
```

#### 10.8 GET /api/admin/roles/{id}
**Purpose:** Get role detail

#### 10.9 POST /api/admin/roles
**Purpose:** Create role

#### 10.10 PUT /api/admin/roles/{id}
**Purpose:** Update role

#### 10.11 DELETE /api/admin/roles/{id}
**Purpose:** Delete role

---

### 🔷 SECTION 11: NOTIFICATIONS (4 endpoints)

#### 11.1 GET /api/admin/notifications
**Purpose:** List notifications

#### 11.2 GET /api/admin/notifications/templates
**Purpose:** Get notification templates

#### 11.3 POST /api/admin/notifications
**Purpose:** Create notification  
**Body:**
```json
{
  "judul": "Alert Penyetoran Sampah",
  "isi": "Ada penyetoran sampah menunggu persetujuan",
  "tipe": "notification",
  "user_ids": [1, 2, 3],
  "kirim_sekarang": true
}
```

#### 11.4 DELETE /api/admin/notifications/{id}
**Purpose:** Delete notification

---

### 🔷 SECTION 12: ANALYTICS (4 endpoints)

#### 12.1 GET /api/admin/analytics/waste
**Purpose:** Get waste analytics  
**Params:** `period` (daily/weekly/monthly)

#### 12.2 GET /api/admin/analytics/waste-by-user
**Purpose:** Get waste by user  
**Params:** `page`, `limit`

#### 12.3 GET /api/admin/analytics/points
**Purpose:** Get points analytics  
**Params:** `period`

#### 12.4 POST /api/admin/points/award
**Purpose:** Award points  
**Body:**
```json
{
  "user_id": 5,
  "points": 250,
  "reason": "Bonus penyetoran"
}
```

---

### 🔷 SECTION 13: ADDITIONAL (5+ endpoints)

#### 13.1 GET /api/admin/leaderboard
**Purpose:** Get leaderboard  
**Params:** `period`, `limit`

#### 13.2 GET /api/admin/transactions
**Purpose:** List all transactions  
**Params:** `page`, `limit`, `type`, `status`

#### 13.3 GET /api/admin/activity-logs
**Purpose:** Get activity logs  
**Params:** `page`, `per_page`, `user_id`, `activity_type`, `date_from`, `date_to`

#### 13.4 GET /api/admin/activity-logs/stats/overview
**Purpose:** Get activity logs stats

#### 13.5 GET /api/admin/activity-logs/export/csv
**Purpose:** Export activity logs  
**Params:** Filter same as 13.3  
**Response:** CSV file

---

## 📊 ENDPOINT SUMMARY

| Category | Count | Status |
|:---|:---:|:---|
| Dashboard | 1 | 🔴 |
| Waste Deposits | 6 | 🔴 |
| Product Redemption | 3 | 🔴 |
| Cash Withdrawal | 3 | 🔴 |
| Products | 5 | 🔴 |
| Articles | 5 | 🔴 |
| Waste Items | 5 | 🔴 |
| Badges | 7 | 🔴 |
| Schedules | 6 | 🔴 |
| Users & Roles | 11 | 🔴 |
| Notifications | 4 | 🔴 |
| Analytics | 4 | 🔴 |
| Additional | 5 | 🔴 |
| **TOTAL** | **65** | **🔴** |

---

## 🎯 IMPLEMENTATION PRIORITY

### Phase 1 - CRITICAL (Week 1) - 5 endpoints
1. GET /api/admin/penyetoran-sampah
2. PATCH /api/admin/penyetoran-sampah/{id}/approve
3. PATCH /api/admin/penyetoran-sampah/{id}/reject
4. GET /api/admin/dashboard/overview
5. POST /api/admin/points/award

### Phase 2 - HIGH (Week 2) - 20 endpoints
- All CRUD endpoints (Products, Articles, Waste Items, Badges, Schedules)
- All User Management endpoints
- Analytics endpoints

### Phase 3 - MEDIUM (Week 3) - 25 endpoints
- Notification Management
- Transaction History
- Reports & Export
- Activity Logs

### Phase 4 - LOW (Week 4) - 15 endpoints
- Admin Management (if needed)
- Advanced filters & searches
- Caching & optimization

---

## ✅ CHECKLIST FOR BACKEND

- [ ] Create database models for all entities
- [ ] Create database migrations
- [ ] Setup authentication middleware (Bearer token)
- [ ] Create all 65 endpoints
- [ ] Implement proper error handling
- [ ] Add input validation
- [ ] Add pagination support
- [ ] Setup response formatting (success/data/message)
- [ ] Add authorization checks (admin/superadmin)
- [ ] Add activity logging
- [ ] Add unit tests for all endpoints
- [ ] API documentation
- [ ] Load testing

---

**Status:** ✅ Frontend ready | 🔴 Backend pending  
**Next Action:** Send to backend team for implementation
