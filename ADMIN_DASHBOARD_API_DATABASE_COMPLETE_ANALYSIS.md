# ADMIN DASHBOARD - COMPLETE API ENDPOINTS & DATABASE STRUCTURE ANALYSIS
**Generated:** December 23, 2025  
**Base URL:** `http://localhost:8000/api`

---

## 📊 1. DASHBOARD OVERVIEW

### Endpoint:
```
GET /api/admin/dashboard/overview
```

### Database Tables:
- `users`
- `penyetoran_sampah`
- `penukar_produk`
- `penarikan_tunai`
- `transaksi_poin`

### Expected Response Fields:
```json
{
  "total_users": number,
  "total_waste_deposits": number,
  "total_points_distributed": number,
  "total_active_schedules": number,
  "recent_activities": array
}
```

---

## 👥 2. USER MANAGEMENT

### 2.1 List All Users
**Endpoint:** `GET /api/admin/users`  
**Query Params:** `page`, `limit`, `search`, `role`, `status`

**Table:** `users`  
**Required Columns:**
```sql
- user_id (PK, INT)
- nama (VARCHAR)
- email (VARCHAR, UNIQUE)
- no_telepon (VARCHAR, NULLABLE)
- alamat (TEXT, NULLABLE)
- role_id (FK → roles.role_id, NULLABLE)
- role (ENUM: 'user', 'admin', 'superadmin', NULLABLE)
- status (ENUM: 'active', 'inactive', 'suspended', DEFAULT 'active')
- poin_total (INT, DEFAULT 0)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### 2.2 Create User
**Endpoint:** `POST /api/admin/users` ❌ **NOT IMPLEMENTED**  
**Required Fields:**
```json
{
  "nama": string,
  "email": string,
  "password": string,
  "role": string,
  "no_telepon": string (optional),
  "alamat": string (optional)
}
```

### 2.3 Get User by ID
**Endpoint:** `GET /api/admin/users/{userId}`  
**Table:** `users`

### 2.4 Update User
**Endpoint:** `PUT /api/admin/users/{userId}`  
**Table:** `users`

### 2.5 Update User Status
**Endpoint:** `PATCH /api/admin/users/{userId}/status`  
**Payload:**
```json
{
  "status": "active|inactive|suspended"
}
```

### 2.6 Update User Role
**Endpoint:** `PATCH /api/admin/users/{userId}/role`  
**Payload:**
```json
{
  "role_id": number
}
```

### 2.7 Delete User
**Endpoint:** `DELETE /api/admin/users/{userId}`

### 2.8 Get User Activity Logs
**Endpoint:** `GET /api/admin/users/{userId}/activity-logs`  
**Table:** `activity_logs`

---

## 🗑️ 3. WASTE DEPOSIT MANAGEMENT (Penyetoran Sampah)

### 3.1 List All Waste Deposits
**Endpoint:** `GET /api/admin/penyetoran-sampah`  
**Query Params:** `page`, `limit`, `status`, `user_id`, `date_from`, `date_to`

**Table:** `penyetoran_sampah`  
**Required Columns:**
```sql
- penyetoran_id (PK, INT)
- user_id (FK → users.user_id)
- jadwal_id (FK → jadwal_penyetoran.jadwal_id, NULLABLE)
- tanggal_penyetoran (DATE)
- waktu_penyetoran (TIME, NULLABLE)
- lokasi (VARCHAR, NULLABLE)
- status (ENUM: 'pending', 'approved', 'rejected', DEFAULT 'pending')
- poin_diberikan (INT, NULLABLE)
- berat_total (DECIMAL(10,2), NULLABLE)
- foto_bukti (TEXT, NULLABLE)
- catatan (TEXT, NULLABLE)
- catatan_admin (TEXT, NULLABLE)
- alasan_penolakan (TEXT, NULLABLE)
- approved_by (FK → users.user_id, NULLABLE)
- approved_at (TIMESTAMP, NULLABLE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

**Relations:**
- `detail_penyetoran` (One-to-Many): Details of each waste type in the deposit

### 3.2 Get Waste Deposit Detail
**Endpoint:** `GET /api/admin/penyetoran-sampah/{depositId}`  
**Tables:** `penyetoran_sampah`, `detail_penyetoran`, `users`, `jenis_sampah`

### 3.3 Approve Waste Deposit
**Endpoint:** `PATCH /api/admin/penyetoran-sampah/{depositId}/approve`  
**Payload:**
```json
{
  "poin_diberikan": number,
  "catatan_admin": string (optional)
}
```
**Updates:** `penyetoran_sampah.status = 'approved'`, adds points to `users.poin_total`

### 3.4 Reject Waste Deposit
**Endpoint:** `PATCH /api/admin/penyetoran-sampah/{depositId}/reject`  
**Payload:**
```json
{
  "alasan_penolakan": string,
  "catatan_admin": string (optional)
}
```
**Updates:** `penyetoran_sampah.status = 'rejected'`

### 3.5 Delete Waste Deposit
**Endpoint:** `DELETE /api/admin/penyetoran-sampah/{depositId}`

### 3.6 Get Waste Statistics
**Endpoint:** `GET /api/admin/penyetoran-sampah/stats/overview`  
**Returns:** Total deposits, approved, rejected, pending counts

---

## 📦 4. WASTE ITEMS MANAGEMENT (Jenis Sampah)

### 4.1 List All Waste Items
**Endpoint:** `GET /api/admin/jenis-sampah`  
**Query Params:** `page`, `limit`, `category_id`, `is_active`

**Table:** `jenis_sampah`  
**Required Columns:**
```sql
- jenis_sampah_id (PK, INT)
- nama_jenis (VARCHAR)
- deskripsi (TEXT, NULLABLE)
- kategori_sampah_id (FK → kategori_sampah.kategori_id, NULLABLE)
- kategori (VARCHAR, NULLABLE - for backward compatibility)
- poin_per_kg (INT)
- harga_per_kg (DECIMAL(10,2), NULLABLE)
- satuan (ENUM: 'kg', 'pcs', 'liter', DEFAULT 'kg')
- is_active (BOOLEAN, DEFAULT true)
- foto_sampah (TEXT, NULLABLE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### 4.2 Get Waste Categories
**Endpoint:** `GET /api/admin/waste-categories`  

**Table:** `kategori_sampah`  
**Required Columns:**
```sql
- kategori_id (PK, INT)
- nama_kategori (VARCHAR)
- deskripsi (TEXT, NULLABLE)
- icon (VARCHAR, NULLABLE)
- created_at (TIMESTAMP)
```

### 4.3 Create Waste Item
**Endpoint:** `POST /api/admin/jenis-sampah`  
**Payload:**
```json
{
  "nama_jenis": string,
  "deskripsi": string,
  "kategori_sampah_id": number,
  "poin_per_kg": number,
  "harga_per_kg": number,
  "satuan": "kg|pcs|liter",
  "is_active": boolean,
  "foto_sampah": string (optional)
}
```

### 4.4 Update Waste Item
**Endpoint:** `PUT /api/admin/jenis-sampah/{jenisSampahId}`

### 4.5 Delete Waste Item
**Endpoint:** `DELETE /api/admin/jenis-sampah/{jenisSampahId}`

---

## 📅 5. SCHEDULE MANAGEMENT (Jadwal Penyetoran)

### 5.1 List All Schedules
**Endpoint:** `GET /api/admin/jadwal-penyetoran`  
**Query Params:** `page`, `limit`, `status`, `date_from`, `date_to`

**Table:** `jadwal_penyetoran`  
**Required Columns:**
```sql
- jadwal_id (PK, INT)
- tanggal (DATE)
- jam_mulai (TIME)
- jam_selesai (TIME)
- lokasi (VARCHAR)
- alamat_lengkap (TEXT, NULLABLE)
- latitude (DECIMAL(10,8), NULLABLE)
- longitude (DECIMAL(11,8), NULLABLE)
- kapasitas_maksimal (INT)
- kapasitas_terisi (INT, DEFAULT 0)
- status (ENUM: 'buka', 'tutup', DEFAULT 'buka') -- ✅ NEW FIELD
- deskripsi (TEXT, NULLABLE)
- created_by (FK → users.user_id, NULLABLE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### 5.2 Get Schedule Detail
**Endpoint:** `GET /api/admin/jadwal-penyetoran/{jadwalId}`

### 5.3 Create Schedule
**Endpoint:** `POST /api/admin/jadwal-penyetoran`  
**Payload:**
```json
{
  "tanggal": "YYYY-MM-DD",
  "jam_mulai": "HH:MM:SS",
  "jam_selesai": "HH:MM:SS",
  "lokasi": string,
  "alamat_lengkap": string,
  "latitude": number,
  "longitude": number,
  "kapasitas_maksimal": number,
  "status": "buka|tutup",
  "deskripsi": string
}
```

### 5.4 Update Schedule
**Endpoint:** `PUT /api/admin/jadwal-penyetoran/{jadwalId}`

### 5.5 Delete Schedule
**Endpoint:** `DELETE /api/admin/jadwal-penyetoran/{jadwalId}`

---

## 🏆 6. BADGE MANAGEMENT

### 6.1 List All Badges
**Endpoint:** `GET /api/admin/badges`  
**Query Params:** `page`, `limit`, `tipe`

**Table:** `badges`  
**Required Columns:**
```sql
- badge_id (PK, INT)
- nama_badge (VARCHAR)
- deskripsi (TEXT)
- icon (VARCHAR) -- ✅ EMOJI STRING (e.g., '🌱')
- tipe (ENUM: 'setor', 'poin', 'ranking') -- ✅ CHANGED from 'automatic'/'manual'
- syarat_setor (INT, NULLABLE) -- For 'setor' type: number of deposits required
- syarat_poin (INT, NULLABLE) -- For 'poin' type: points threshold
- reward_poin (INT, DEFAULT 0) -- ✅ RENAMED from 'poin_bonus'
- is_active (BOOLEAN, DEFAULT true)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

**Relations:**
- `user_badges` (Many-to-Many with users)

### 6.2 Get Badge by ID
**Endpoint:** `GET /api/admin/badges/{badgeId}`

### 6.3 Create Badge
**Endpoint:** `POST /api/admin/badges`  
**Payload:**
```json
{
  "nama_badge": string,
  "deskripsi": string,
  "icon": string, // emoji character
  "tipe": "setor|poin|ranking",
  "syarat_setor": number (for setor type),
  "syarat_poin": number (for poin type),
  "reward_poin": number,
  "is_active": boolean
}
```

### 6.4 Update Badge
**Endpoint:** `PUT /api/admin/badges/{badgeId}`

### 6.5 Delete Badge
**Endpoint:** `DELETE /api/admin/badges/{badgeId}`

### 6.6 Assign Badge to User
**Endpoint:** `POST /api/admin/badges/{badgeId}/assign`  
**Payload:**
```json
{
  "user_id": number
}
```

**Table:** `user_badges`  
**Required Columns:**
```sql
- user_badge_id (PK, INT)
- user_id (FK → users.user_id)
- badge_id (FK → badges.badge_id)
- tanggal_diperoleh (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
```

### 6.7 Get Users with Badge
**Endpoint:** `GET /api/admin/badges/{badgeId}/users`

---

## 🎁 7. PRODUCT MANAGEMENT (Produk)

### 7.1 List All Products
**Endpoint:** `GET /api/admin/produk`  
**Query Params:** `page`, `limit`, `kategori`, `is_active`

**Table:** `produk`  
**Required Columns:**
```sql
- produk_id (PK, INT)
- nama_produk (VARCHAR)
- deskripsi (TEXT)
- kategori (VARCHAR)
- harga_poin (INT)
- stok (INT)
- is_active (BOOLEAN, DEFAULT true)
- foto_produk (TEXT, NULLABLE)
- berat (DECIMAL(10,2), NULLABLE)
- dimensi (VARCHAR, NULLABLE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### 7.2 Get Product by ID
**Endpoint:** `GET /api/admin/produk/{produkId}`

### 7.3 Create Product
**Endpoint:** `POST /api/admin/produk`  
**Payload:**
```json
{
  "nama_produk": string,
  "deskripsi": string,
  "kategori": string,
  "harga_poin": number,
  "stok": number,
  "is_active": boolean,
  "foto_produk": string,
  "berat": number,
  "dimensi": string
}
```

### 7.4 Update Product
**Endpoint:** `PUT /api/admin/produk/{produkId}`

### 7.5 Delete Product
**Endpoint:** `DELETE /api/admin/produk/{produkId}`

---

## 🔄 8. PRODUCT REDEMPTION MANAGEMENT (Penukar Produk)

### 8.1 List All Redemptions
**Endpoint:** `GET /api/admin/penukar-produk`  
**Query Params:** `page`, `limit`, `status`, `user_id`, `produk_id`

**Table:** `penukar_produk`  
**Required Columns:**
```sql
- penukar_id (PK, INT)
- user_id (FK → users.user_id)
- produk_id (FK → produk.produk_id)
- jumlah (INT)
- total_poin (INT)
- status (ENUM: 'pending', 'approved', 'picked_up', 'rejected', DEFAULT 'pending')
- alamat_pengiriman (TEXT, NULLABLE)
- catatan (TEXT, NULLABLE)
- catatan_admin (TEXT, NULLABLE)
- alasan_penolakan (TEXT, NULLABLE)
- tanggal_penukaran (TIMESTAMP)
- tanggal_verifikasi (TIMESTAMP, NULLABLE)
- verified_by (FK → users.user_id, NULLABLE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### 8.2 Approve Redemption
**Endpoint:** `PATCH /api/admin/penukar-produk/{redemptionId}/approve`  
**Payload:**
```json
{
  "catatan_admin": string (optional)
}
```

### 8.3 Reject Redemption
**Endpoint:** `PATCH /api/admin/penukar-produk/{redemptionId}/reject`  
**Payload:**
```json
{
  "alasan_penolakan": string,
  "catatan_admin": string (optional)
}
```
**Updates:** Returns points to `users.poin_total`

---

## 💰 9. CASH WITHDRAWAL MANAGEMENT (Penarikan Tunai)

### 9.1 List All Withdrawals
**Endpoint:** `GET /api/admin/penarikan-tunai`  
**Query Params:** `page`, `limit`, `status`, `user_id`

**Table:** `penarikan_tunai`  
**Required Columns:**
```sql
- penarikan_id (PK, INT)
- user_id (FK → users.user_id)
- jumlah_poin (INT)
- jumlah_rupiah (DECIMAL(10,2))
- nama_bank (VARCHAR)
- nomor_rekening (VARCHAR)
- nama_penerima (VARCHAR)
- status (ENUM: 'pending', 'approved', 'rejected', 'processed', DEFAULT 'pending')
- catatan_admin (TEXT, NULLABLE)
- alasan_penolakan (TEXT, NULLABLE)
- tanggal_verifikasi (TIMESTAMP, NULLABLE)
- verified_by (FK → users.user_id, NULLABLE)
- processed_at (TIMESTAMP, NULLABLE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### 9.2 Approve Cash Withdrawal
**Endpoint:** `PATCH /api/admin/penarikan-tunai/{id}/approve`  
**Payload:**
```json
{
  "catatan_admin": string (optional)
}
```

### 9.3 Reject Cash Withdrawal
**Endpoint:** `PATCH /api/admin/penarikan-tunai/{id}/reject`  
**Payload:**
```json
{
  "alasan_penolakan": string,
  "catatan_admin": string (optional)
}
```
**Updates:** Returns points to `users.poin_total`

---

## 🔔 10. NOTIFICATION MANAGEMENT

### 10.1 List All Notifications
**Endpoint:** `GET /api/admin/notifications`  
**Query Params:** `page`, `limit`, `tipe`, `target_user_id`

**Table:** `notifikasi`  
**Required Columns:**
```sql
- notifikasi_id (PK, INT)
- user_id (FK → users.user_id, NULLABLE) -- NULL for broadcast
- judul (VARCHAR)
- pesan (TEXT)
- tipe (ENUM: 'info', 'success', 'warning', 'error', 'broadcast')
- is_read (BOOLEAN, DEFAULT false)
- link (VARCHAR, NULLABLE)
- metadata (JSON, NULLABLE)
- created_at (TIMESTAMP)
```

### 10.2 Get Notification Templates
**Endpoint:** `GET /api/admin/notifications/templates` ❌ **404 NOT FOUND**

### 10.3 Create Notification
**Endpoint:** `POST /api/admin/notifications`  
**Payload:**
```json
{
  "user_id": number (null for broadcast),
  "judul": string,
  "pesan": string,
  "tipe": "info|success|warning|error|broadcast",
  "link": string (optional)
}
```

### 10.4 Delete Notification
**Endpoint:** `DELETE /api/admin/notifications/{notificationId}`

---

## 📰 11. ARTICLE MANAGEMENT (Artikel)

### 11.1 List All Articles
**Endpoint:** `GET /api/admin/artikel`  
**Query Params:** `page`, `limit`, `kategori`, `search`

**Table:** `artikel`  
**Required Columns:**
```sql
- artikel_id (PK, INT)
- judul (VARCHAR)
- penulis (VARCHAR)
- kategori (VARCHAR)
- tanggal_publikasi (DATE)
- konten (TEXT)
- slug (VARCHAR, UNIQUE)
- foto_cover (TEXT, NULLABLE)
- views (INT, DEFAULT 0) -- ✅ ADD THIS COLUMN
- is_published (BOOLEAN, DEFAULT true)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### 11.2 Get Article Detail
**Endpoint:** `GET /api/admin/artikel/{artikelId}`

### 11.3 Create Article
**Endpoint:** `POST /api/admin/artikel`  
**Payload:**
```json
{
  "judul": string,
  "penulis": string,
  "kategori": string,
  "tanggal_publikasi": "YYYY-MM-DD",
  "konten": string,
  "slug": string,
  "foto_cover": string (optional)
}
```

### 11.4 Update Article
**Endpoint:** `PUT /api/admin/artikel/{artikelId}`

### 11.5 Delete Article
**Endpoint:** `DELETE /api/admin/artikel/{artikelId}`

---

## 📊 12. ANALYTICS

### 12.1 Waste Analytics
**Endpoint:** `GET /api/admin/analytics/waste`  
**Query Params:** `period` (daily|weekly|monthly), `date_from`, `date_to`  
**Tables:** `penyetoran_sampah`, `detail_penyetoran`

### 12.2 Waste by User Analytics
**Endpoint:** `GET /api/admin/analytics/waste-by-user`  
**Query Params:** `page`, `perPage`, `date_from`, `date_to`  
**Tables:** `users`, `penyetoran_sampah`

### 12.3 Points Analytics
**Endpoint:** `GET /api/admin/analytics/points`  
**Query Params:** `period`, `date_from`, `date_to`  
**Tables:** `transaksi_poin`, `users`

---

## 💎 13. POINTS MANAGEMENT

### 13.1 Award Points
**Endpoint:** `POST /api/admin/points/award`  
**Payload:**
```json
{
  "user_id": number,
  "jumlah_poin": number,
  "keterangan": string
}
```

**Table:** `transaksi_poin`  
**Required Columns:**
```sql
- transaksi_id (PK, INT)
- user_id (FK → users.user_id)
- jenis_transaksi (ENUM: 'credit', 'debit')
- jumlah_poin (INT)
- saldo_sebelum (INT)
- saldo_sesudah (INT)
- keterangan (VARCHAR)
- referensi_tipe (VARCHAR, NULLABLE) -- e.g., 'penyetoran', 'penukaran', 'penarikan'
- referensi_id (INT, NULLABLE) -- FK to related table
- created_at (TIMESTAMP)
```

### 13.2 Points History
**Endpoint:** `GET /api/admin/points/history`  
**Query Params:** `page`, `limit`, `user_id`, `jenis_transaksi`  
**Table:** `transaksi_poin`

### 13.3 Leaderboard
**Endpoint:** `GET /api/admin/leaderboard`  
**Query Params:** `period`, `limit`  
**Table:** `users` (ORDER BY poin_total DESC)

---

## 📝 14. TRANSACTION HISTORY

### 14.1 List All Transactions
**Endpoint:** `GET /api/admin/transactions`  
**Query Params:** `page`, `limit`, `user_id`, `type`, `date_from`, `date_to`  
**Table:** `transaksi_poin`

### 14.2 Export Transactions
**Endpoint:** `GET /api/admin/transactions/export`  
**Query Params:** `format` (csv|xlsx), filters

---

## 📋 15. ACTIVITY LOGS

### 15.1 List All Activity Logs
**Endpoint:** `GET /api/admin/activity-logs`  
**Query Params:** `page`, `perPage`, `user_id`, `action`, `date_from`, `date_to`

**Table:** `activity_logs`  
**Required Columns:**
```sql
- log_id (PK, INT)
- user_id (FK → users.user_id, NULLABLE)
- action (VARCHAR) -- e.g., 'create_user', 'approve_deposit'
- entity_type (VARCHAR, NULLABLE) -- e.g., 'user', 'penyetoran_sampah'
- entity_id (INT, NULLABLE)
- description (TEXT, NULLABLE)
- ip_address (VARCHAR, NULLABLE)
- user_agent (TEXT, NULLABLE)
- metadata (JSON, NULLABLE)
- created_at (TIMESTAMP)
```

### 15.2 Activity Logs Stats
**Endpoint:** `GET /api/admin/activity-logs/stats/overview`  
**Query Params:** `dateFrom`, `dateTo`

### 15.3 Export Activity Logs CSV
**Endpoint:** `GET /api/admin/activity-logs/export/csv`  
**Query Params:** filters

---

## 🔐 16. ROLES & PERMISSIONS (Superadmin Only)

### 16.1 List All Roles
**Endpoint:** `GET /api/superadmin/roles`

**Table:** `roles`  
**Required Columns:**
```sql
- role_id (PK, INT)
- nama_role (VARCHAR, UNIQUE)
- deskripsi (TEXT, NULLABLE)
- created_at (TIMESTAMP)
```

### 16.2 Get Role by ID
**Endpoint:** `GET /api/superadmin/roles/{roleId}`

### 16.3 Create Role
**Endpoint:** `POST /api/superadmin/roles`

### 16.4 Update Role
**Endpoint:** `PUT /api/superadmin/roles/{roleId}`

### 16.5 Delete Role
**Endpoint:** `DELETE /api/superadmin/roles/{roleId}`

### 16.6 Get All Permissions
**Endpoint:** `GET /api/superadmin/permissions`

**Table:** `permissions`  
**Required Columns:**
```sql
- permission_id (PK, INT)
- nama_permission (VARCHAR, UNIQUE)
- deskripsi (TEXT, NULLABLE)
- kategori (VARCHAR, NULLABLE)
```

### 16.7 Get Role Permissions
**Endpoint:** `GET /api/superadmin/roles/{roleId}/permissions`

### 16.8 Assign Permissions to Role
**Endpoint:** `POST /api/superadmin/roles/{roleId}/permissions`  
**Payload:**
```json
{
  "permission_ids": [1, 2, 3]
}
```

**Table:** `role_permissions`  
**Required Columns:**
```sql
- role_permission_id (PK, INT)
- role_id (FK → roles.role_id)
- permission_id (FK → permissions.permission_id)
```

---

## 🔧 17. ADMIN MANAGEMENT (Superadmin Only)

### 17.1 List All Admins
**Endpoint:** `GET /api/superadmin/admins`  
**Query Params:** `page`, `limit`  
**Table:** `users` (WHERE role IN ('admin', 'superadmin'))

### 17.2 Get Admin by ID
**Endpoint:** `GET /api/superadmin/admins/{adminId}`

### 17.3 Create Admin
**Endpoint:** `POST /api/superadmin/admins`

### 17.4 Update Admin
**Endpoint:** `PUT /api/superadmin/admins/{adminId}`

### 17.5 Delete Admin
**Endpoint:** `DELETE /api/superadmin/admins/{adminId}`

### 17.6 Get Admin Activity Logs
**Endpoint:** `GET /api/superadmin/admins/{adminId}/activity-logs`  
**Query Params:** `page`, `limit`

---

## 📊 18. REPORTS

### 18.1 Generate Report
**Endpoint:** `POST /api/admin/reports/generate`  
**Payload:**
```json
{
  "type": "waste|points|users|transactions",
  "period": "daily|weekly|monthly|yearly",
  "start_date": "YYYY-MM-DD",
  "end_date": "YYYY-MM-DD",
  "format": "pdf|csv|xlsx"
}
```

### 18.2 Export Data
**Endpoint:** `GET /api/admin/export`  
**Query Params:** `type`, `format`

---

## 🔗 RELATIONSHIP SUMMARY

### Core Entity Relationships:
```
users
  ├── has many → penyetoran_sampah
  ├── has many → penukar_produk
  ├── has many → penarikan_tunai
  ├── has many → transaksi_poin
  ├── has many → notifikasi
  ├── has many → activity_logs
  ├── has many → user_badges
  ├── belongs to → roles
  └── has many → detail_penyetoran (through penyetoran_sampah)

penyetoran_sampah
  ├── belongs to → users
  ├── belongs to → jadwal_penyetoran
  ├── has many → detail_penyetoran
  └── approved_by → users (admin)

detail_penyetoran
  ├── belongs to → penyetoran_sampah
  └── belongs to → jenis_sampah

jenis_sampah
  ├── belongs to → kategori_sampah
  └── has many → detail_penyetoran

produk
  └── has many → penukar_produk

penukar_produk
  ├── belongs to → users
  ├── belongs to → produk
  └── verified_by → users (admin)

penarikan_tunai
  ├── belongs to → users
  └── verified_by → users (admin)

badges
  └── has many → user_badges

user_badges
  ├── belongs to → users
  └── belongs to → badges

roles
  ├── has many → users
  └── has many → role_permissions

role_permissions
  ├── belongs to → roles
  └── belongs to → permissions
```

---

## ⚠️ BACKEND ISSUES IDENTIFIED

### Critical (Blocking Features):
1. ❌ **POST /api/admin/users** - Returns 405 Method Not Allowed
   - Feature affected: User creation in User Management
   - Required: Implement route with validation

2. ❌ **GET /api/admin/notifications/templates** - Returns 404 Not Found
   - Feature affected: Notification templates dropdown
   - Required: Implement endpoint or remove frontend feature

### High Priority (Data Issues):
3. ⚠️ **PATCH /api/admin/penarikan-tunai/{id}/reject** - Returns 500 Internal Server Error
   - Issue: Payload validation or database constraint
   - Fix: Check payload structure matches `{ alasan_penolakan, catatan_admin }`

4. ⚠️ **PATCH /api/admin/penukar-produk/{id}/reject** - Returns 422 Unprocessable Content
   - Issue: Validation rules mismatch
   - Fix: Verify payload structure and validation rules

5. ⚠️ **POST /api/admin/badges** - Returns 500 Internal Server Error
   - Issue: Field mismatch or validation error
   - Fix: Ensure `icon`, `tipe`, `syarat_setor`, `syarat_poin`, `reward_poin` fields exist

6. ⚠️ **POST /api/admin/jadwal-penyetoran** - Returns 500 Internal Server Error
   - Issue: Missing `status` field or validation
   - Fix: Add `status` ENUM field to `jadwal_penyetoran` table

### Medium Priority (Missing Columns):
7. ⚠️ **artikel.views** - Column missing, causing NaN errors
   - Fix: `ALTER TABLE artikel ADD COLUMN views INT DEFAULT 0;`
   - Frontend expects: `article.views` for statistics and display

---

## ✅ FRONTEND-BACKEND FIELD MAPPING

### Common Field Name Differences:
| Frontend | Backend | Table Column |
|----------|---------|--------------|
| `nama` | `nama` | `users.nama` |
| `poin_diberikan` | `poin_diberikan` | `penyetoran_sampah.poin_diberikan` |
| `alasan_penolakan` | `alasan_penolakan` | Multiple tables |
| `catatan_admin` | `catatan_admin` | Multiple tables |
| `is_active` | `is_active` | Multiple tables |
| `foto_cover` | `foto_cover` | `artikel.foto_cover` |
| `foto_produk` | `foto_produk` | `produk.foto_produk` |
| `foto_sampah` | `foto_sampah` | `jenis_sampah.foto_sampah` |

---

## 🚀 RECOMMENDED DATABASE MIGRATIONS

```sql
-- 1. Add missing columns to jadwal_penyetoran
ALTER TABLE jadwal_penyetoran ADD COLUMN IF NOT EXISTS status ENUM('buka', 'tutup') DEFAULT 'buka';

-- 2. Add missing views column to artikel
ALTER TABLE artikel ADD COLUMN IF NOT EXISTS views INT DEFAULT 0;

-- 3. Modify badges table structure
ALTER TABLE badges MODIFY COLUMN tipe ENUM('setor', 'poin', 'ranking');
ALTER TABLE badges CHANGE COLUMN poin_bonus reward_poin INT DEFAULT 0;
ALTER TABLE badges ADD COLUMN IF NOT EXISTS syarat_setor INT NULL;
ALTER TABLE badges ADD COLUMN IF NOT EXISTS syarat_poin INT NULL;

-- 4. Ensure users table has required columns
ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin', 'superadmin') DEFAULT 'user';
ALTER TABLE users ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'suspended') DEFAULT 'active';
ALTER TABLE users ADD COLUMN IF NOT EXISTS poin_total INT DEFAULT 0;

-- 5. Add indexes for performance
CREATE INDEX idx_penyetoran_status ON penyetoran_sampah(status);
CREATE INDEX idx_penukar_status ON penukar_produk(status);
CREATE INDEX idx_penarikan_status ON penarikan_tunai(status);
CREATE INDEX idx_artikel_views ON artikel(views DESC);
CREATE INDEX idx_users_poin ON users(poin_total DESC);
```

---

## 📌 NOTES FOR BACKEND TEAM

1. **Payload Structure Consistency**: Ensure all rejection endpoints accept:
   ```json
   {
     "alasan_penolakan": string,
     "catatan_admin": string
   }
   ```

2. **Response Format**: Standardize all responses:
   ```json
   {
     "success": boolean,
     "data": object|array,
     "message": string,
     "meta": { "pagination": {...} } // for paginated endpoints
   }
   ```

3. **File Upload Handling**: Badge icons are now emoji strings, not file uploads. Update validation accordingly.

4. **Status Enums**: Verify all ENUM values match frontend expectations.

5. **Timestamps**: Use consistent timestamp format (ISO 8601 recommended).

6. **Foreign Key Constraints**: Ensure all FK relationships have proper ON DELETE/UPDATE actions.

---

**End of Document**  
Generated for Mendaur TA Project - Admin Dashboard API Analysis
