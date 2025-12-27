# 📚 DOKUMENTASI DATABASE SCHEMA & API ENDPOINTS
## Mendaur - Tugas Akhir Project

**Generated:** December 23, 2025  
**Base URL:** `http://localhost:8000/api`

---

# 📊 BAGIAN 1: DATABASE SCHEMA (STRUKTUR TABEL)

## 1. TABEL `users`
**Primary Key:** `user_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `user_id` | bigint | NO | Primary key, auto increment |
| `role_id` | bigint | YES | Foreign key ke tabel roles |
| `no_hp` | varchar | YES | Nomor telepon |
| `nama` | varchar | NO | Nama lengkap |
| `email` | varchar | NO | Email (unique) |
| `password` | varchar | NO | Password (hashed) |
| `alamat` | text | YES | Alamat lengkap |
| `foto_profil` | varchar | YES | Path foto profil |
| `total_poin` | int | NO | Total poin user (default: 0) |
| `poin_tercatat` | int | YES | Poin tercatat |
| `nama_bank` | varchar | YES | Nama bank untuk penarikan |
| `nomor_rekening` | varchar | YES | Nomor rekening |
| `atas_nama_rekening` | varchar | YES | Nama pemilik rekening |
| `total_setor_sampah` | decimal | NO | Total berat sampah yang disetor (kg) |
| `level` | int | NO | Level user (default: 1) |
| `status` | enum | NO | `active`, `inactive`, `suspended` |
| `tipe_nasabah` | enum | NO | `reguler`, `premium` |
| `created_at` | timestamp | YES | Tanggal dibuat |
| `updated_at` | timestamp | YES | Tanggal diupdate |
| `deleted_at` | timestamp | YES | Soft delete |

---

## 2. TABEL `roles`
**Primary Key:** `role_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `role_id` | bigint | NO | Primary key, auto increment |
| `nama_role` | varchar | NO | Nama role (`superadmin`, `admin`, `nasabah`) |
| `level_akses` | int | NO | Level akses (1-10) |
| `deskripsi` | text | YES | Deskripsi role |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

## 3. TABEL `badges`
**Primary Key:** `badge_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `badge_id` | bigint | NO | Primary key, auto increment |
| `nama` | varchar | NO | Nama badge |
| `deskripsi` | text | YES | Deskripsi badge |
| `icon` | varchar | YES | Icon emoji (contoh: "🌱", "🏆") |
| `syarat_poin` | int | YES | Minimal poin untuk mendapat badge |
| `syarat_setor` | int | YES | Minimal jumlah setor untuk mendapat badge |
| `reward_poin` | int | YES | Reward poin yang didapat |
| `tipe` | enum | NO | `setor`, `poin`, `ranking` |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

## 4. TABEL `user_badges` (Pivot Table)
**Primary Key:** `user_badge_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `user_badge_id` | bigint | NO | Primary key |
| `user_id` | bigint | NO | Foreign key ke users |
| `badge_id` | bigint | NO | Foreign key ke badges |
| `tanggal_dapat` | date | YES | Tanggal mendapat badge |
| `reward_claimed` | boolean | NO | Apakah reward sudah diklaim |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

## 5. TABEL `produks`
**Primary Key:** `produk_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `produk_id` | bigint | NO | Primary key, auto increment |
| `nama` | varchar | NO | Nama produk |
| `deskripsi` | text | YES | Deskripsi produk |
| `harga_poin` | int | NO | Harga dalam poin |
| `stok` | int | NO | Jumlah stok tersedia |
| `kategori` | varchar | NO | Kategori produk |
| `foto` | varchar | YES | Path foto produk |
| `status` | enum | NO | `tersedia`, `habis`, `nonaktif` |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

## 6. TABEL `artikels`
**Primary Key:** `artikel_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `artikel_id` | bigint | NO | Primary key, auto increment |
| `judul` | varchar | NO | Judul artikel |
| `slug` | varchar | NO | URL-friendly slug |
| `konten` | text | NO | Isi artikel |
| `foto_cover` | varchar | YES | Path foto cover |
| `penulis` | varchar | NO | Nama penulis |
| `kategori` | varchar | NO | Kategori artikel |
| `tanggal_publikasi` | date | NO | Tanggal publikasi |
| `views` | int | NO | Jumlah views (default: 0) |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

## 7. TABEL `jadwal_penyetorans`
**Primary Key:** `jadwal_penyetoran_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `jadwal_penyetoran_id` | bigint | NO | Primary key |
| `hari` | enum | NO | Hari: `Senin`, `Selasa`, `Rabu`, `Kamis`, `Jumat`, `Sabtu`, `Minggu` |
| `waktu_mulai` | time | NO | Waktu mulai (format: HH:mm:ss) |
| `waktu_selesai` | time | NO | Waktu selesai (format: HH:mm:ss) |
| `lokasi` | varchar | NO | Lokasi penyetoran |
| `status` | enum | NO | `Buka`, `Tutup` (default: Buka) |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

> ⚠️ **UPDATE 23 Des 2025:** Kolom `tanggal` diubah menjadi `hari` (enum), `kapasitas` dihapus, `status` menggunakan huruf kapital.

---

## 8. TABEL `kategori_sampah`
**Primary Key:** `kategori_sampah_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `kategori_sampah_id` | bigint | NO | Primary key |
| `nama_kategori` | varchar | NO | Nama kategori |
| `deskripsi` | text | YES | Deskripsi |
| `icon` | varchar | YES | Icon emoji |
| `warna` | varchar | YES | Warna hex (contoh: #FF5733) |
| `is_active` | boolean | NO | Status aktif (default: true) |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

## 9. TABEL `jenis_sampah`
**Primary Key:** `jenis_sampah_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `jenis_sampah_id` | bigint | NO | Primary key |
| `kategori_sampah_id` | bigint | NO | Foreign key ke kategori_sampah |
| `nama_jenis` | varchar | NO | Nama jenis sampah |
| `harga_per_kg` | decimal | NO | Harga per kilogram |
| `satuan` | varchar | YES | Satuan (default: "kg") |
| `kode` | varchar | YES | Kode unik jenis sampah |
| `is_active` | boolean | NO | Status aktif (default: true) |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

## 10. TABEL `tabung_sampah` (Penyetoran Sampah)
**Primary Key:** `tabung_sampah_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `tabung_sampah_id` | bigint | NO | Primary key |
| `user_id` | bigint | NO | Foreign key ke users |
| `jadwal_penyetoran_id` | bigint | YES | Foreign key ke jadwal_penyetorans |
| `nama_lengkap` | varchar | NO | Nama penyetor |
| `no_hp` | varchar | NO | Nomor telepon |
| `titik_lokasi` | varchar | YES | Koordinat/alamat lokasi |
| `jenis_sampah` | varchar | NO | Jenis sampah (JSON atau string) |
| `berat_kg` | decimal | NO | Berat sampah (kg) |
| `foto_sampah` | varchar | YES | Path foto sampah |
| `status` | enum | NO | `pending`, `approved`, `rejected` |
| `poin_didapat` | int | YES | Poin yang didapat setelah approve |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

## 11. TABEL `penukaran_produk`
**Primary Key:** `penukaran_produk_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `penukaran_produk_id` | bigint | NO | Primary key |
| `user_id` | bigint | NO | Foreign key ke users |
| `produk_id` | bigint | NO | Foreign key ke produks |
| `nama_produk` | varchar | NO | Nama produk saat ditukar |
| `poin_digunakan` | int | NO | Jumlah poin yang dipakai |
| `jumlah` | int | NO | Jumlah produk |
| `status` | enum | NO | `pending`, `approved`, `rejected`, `completed` |
| `metode_ambil` | enum | YES | `ambil_ditempat`, `dikirim` |
| `catatan` | text | YES | Catatan tambahan |
| `tanggal_penukaran` | date | NO | Tanggal penukaran |
| `tanggal_diambil` | date | YES | Tanggal produk diambil |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

## 12. TABEL `penarikan_tunai`
**Primary Key:** `penarikan_tunai_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `penarikan_tunai_id` | bigint | NO | Primary key |
| `user_id` | bigint | NO | Foreign key ke users |
| `jumlah_poin` | int | NO | Jumlah poin yang ditarik |
| `jumlah_rupiah` | decimal | NO | Nominal rupiah |
| `nomor_rekening` | varchar | NO | Nomor rekening tujuan |
| `nama_bank` | varchar | NO | Nama bank |
| `nama_penerima` | varchar | NO | Nama pemilik rekening |
| `status` | enum | NO | `pending`, `approved`, `rejected` |
| `catatan_admin` | text | YES | Catatan dari admin |
| `processed_by` | bigint | YES | User ID admin yang memproses |
| `processed_at` | timestamp | YES | Waktu diproses |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

## 13. TABEL `notifikasi`
**Primary Key:** `notifikasi_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `notifikasi_id` | bigint | NO | Primary key |
| `user_id` | bigint | NO | Foreign key ke users |
| `judul` | varchar | NO | Judul notifikasi |
| `pesan` | text | NO | Isi pesan |
| `tipe` | varchar | YES | Tipe notifikasi |
| `is_read` | boolean | NO | Status sudah dibaca (default: false) |
| `related_id` | bigint | YES | ID terkait (opsional) |
| `related_type` | varchar | YES | Tipe relasi (opsional) |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

## 14. TABEL `poin_transaksis`
**Primary Key:** `poin_transaksi_id`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `poin_transaksi_id` | bigint | NO | Primary key |
| `user_id` | bigint | NO | Foreign key ke users |
| `tabung_sampah_id` | bigint | YES | Foreign key ke tabung_sampah |
| `jenis_sampah` | varchar | YES | Jenis sampah |
| `berat_kg` | decimal | YES | Berat (kg) |
| `poin_didapat` | int | NO | Poin yang didapat (+ atau -) |
| `is_usable` | boolean | NO | Apakah poin bisa digunakan |
| `reason_not_usable` | text | YES | Alasan tidak bisa digunakan |
| `sumber` | varchar | NO | Sumber poin (`setor`, `bonus`, `tukar`, `tarik`) |
| `keterangan` | text | YES | Keterangan tambahan |
| `referensi_id` | bigint | YES | ID referensi |
| `referensi_tipe` | varchar | YES | Tipe referensi |
| `created_at` | timestamp | YES | |
| `updated_at` | timestamp | YES | |

---

# 🔗 BAGIAN 2: API ENDPOINTS

## AUTHENTICATION

### POST `/api/login`
Login user.
```json
// Request
{
  "email": "user@example.com",
  "password": "password123"
}

// Response
{
  "status": "success",
  "data": {
    "user": { ... },
    "token": "sanctum_token_here"
  }
}
```

### POST `/api/register`
Register user baru.
```json
// Request
{
  "nama": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "no_hp": "081234567890"
}
```

### POST `/api/logout`
Logout user (requires auth token).

---

## ADMIN - USER MANAGEMENT

### GET `/api/admin/users`
Get all users with pagination.
```
Query params: ?page=1&limit=10&search=&sort=created_at&order=DESC
```

### POST `/api/admin/users` ⭐ NEW
Create new user.
```json
// Request
{
  "nama": "string (required)",
  "email": "string (required, unique)",
  "password": "string (required, min 6)",
  "no_hp": "string (optional)",
  "alamat": "string (optional)",
  "role_id": "integer (optional)",
  "tipe_nasabah": "reguler|premium (optional)",
  "status": "active|inactive|suspended (optional)"
}

// Response
{
  "status": "success",
  "message": "User berhasil dibuat",
  "data": {
    "user_id": 1,
    "nama": "John Doe",
    "email": "john@example.com",
    "status": "active"
  }
}
```

### GET `/api/admin/users/{userId}`
Get single user detail.

### PUT `/api/admin/users/{userId}`
Update user.
```json
{
  "nama": "string",
  "email": "string",
  "no_hp": "string",
  "alamat": "string",
  "status": "active|inactive|suspended",
  "tipe_nasabah": "reguler|premium"
}
```

### DELETE `/api/admin/users/{userId}`
Delete user (soft delete).

### PATCH `/api/admin/users/{userId}/status`
Update user status.
```json
{ "status": "active|inactive|suspended" }
```

### PATCH `/api/admin/users/{userId}/role`
Update user role.
```json
{ "role_id": 1 }
```

---

## ADMIN - BADGE MANAGEMENT

### GET `/api/admin/badges`
Get all badges.

### POST `/api/admin/badges`
Create badge.
```json
{
  "nama": "Eco Warrior (required, unique)",
  "deskripsi": "string (optional)",
  "icon": "🌱 (optional, emoji string)",
  "tipe": "setor|poin|ranking (required)",
  "syarat_setor": "integer (optional)",
  "syarat_poin": "integer (optional)",
  "reward_poin": "integer (optional)"
}
```

### GET `/api/admin/badges/{badgeId}`
Get single badge.

### PUT `/api/admin/badges/{badgeId}`
Update badge.

### DELETE `/api/admin/badges/{badgeId}`
Delete badge.

### POST `/api/admin/badges/{badgeId}/assign`
Assign badge to user.
```json
{ "user_id": 1 }
```

---

## ADMIN - PRODUK MANAGEMENT

### GET `/api/admin/produk`
Get all products.

### POST `/api/admin/produk`
Create product.
```json
{
  "nama": "string (required)",
  "deskripsi": "string (optional)",
  "harga_poin": "integer (required)",
  "stok": "integer (required)",
  "kategori": "string (required)",
  "foto": "file image (optional)",
  "status": "tersedia|habis|nonaktif (optional)"
}
```

### GET `/api/admin/produk/{id}`
Get single product.

### PUT `/api/admin/produk/{id}`
Update product.

### DELETE `/api/admin/produk/{id}`
Delete product.

---

## ADMIN - ARTIKEL MANAGEMENT

### GET `/api/admin/artikel`
Get all articles.

### POST `/api/admin/artikel`
Create article.
```json
{
  "judul": "string (required)",
  "konten": "string (required)",
  "penulis": "string (required)",
  "kategori": "string (required)",
  "tanggal_publikasi": "YYYY-MM-DD (required)",
  "foto_cover": "file image (optional)"
}
```

### GET `/api/admin/artikel/{slug}`
Get single article by slug.

### PUT `/api/admin/artikel/{slug}`
Update article.

### DELETE `/api/admin/artikel/{slug}`
Delete article.

---

## ADMIN - JADWAL PENYETORAN MANAGEMENT

> ⚠️ **UPDATE 23 Des 2025:** Field `tanggal` diubah menjadi `hari`, `kapasitas` dihapus, `status` menggunakan huruf kapital.

### GET `/api/admin/jadwal-penyetoran`
Get all schedules.

### POST `/api/admin/jadwal-penyetoran`
Create schedule.
```json
{
  "hari": "Senin|Selasa|Rabu|Kamis|Jumat|Sabtu|Minggu (required)",
  "waktu_mulai": "HH:mm (required)",
  "waktu_selesai": "HH:mm (required)",
  "lokasi": "string (required)",
  "status": "Buka|Tutup (optional, default: Buka)"
}
```

### GET `/api/admin/jadwal-penyetoran/{id}`
Get single schedule.

### PUT `/api/admin/jadwal-penyetoran/{id}`
Update schedule.
```json
{
  "hari": "Senin|Selasa|Rabu|Kamis|Jumat|Sabtu|Minggu (optional)",
  "waktu_mulai": "HH:mm (optional)",
  "waktu_selesai": "HH:mm (optional)",
  "lokasi": "string (optional)",
  "status": "Buka|Tutup (optional)"
}
```

### DELETE `/api/admin/jadwal-penyetoran/{id}`
Delete schedule.

---

## ADMIN - JENIS SAMPAH MANAGEMENT

### GET `/api/admin/jenis-sampah`
Get all waste types.

### POST `/api/admin/jenis-sampah`
Create waste type.
```json
{
  "kategori_sampah_id": "integer (required)",
  "nama_jenis": "string (required)",
  "harga_per_kg": "decimal (required)",
  "satuan": "string (optional, default: kg)",
  "kode": "string (optional, unique)"
}
```

### GET `/api/admin/jenis-sampah/{id}`
Get single waste type.

### PUT `/api/admin/jenis-sampah/{id}`
Update waste type.
```json
{
  "kategori_sampah_id": "integer (optional)",
  "nama_jenis": "string (optional)",
  "harga_per_kg": "decimal (optional)",
  "satuan": "string (optional)",
  "kode": "string (optional)",
  "is_active": "boolean (optional)"
}
```

### DELETE `/api/admin/jenis-sampah/{id}`
Delete waste type.

---

## ADMIN - KATEGORI SAMPAH MANAGEMENT

### GET `/api/admin/kategori-sampah`
### GET `/api/admin/waste-categories`
Get all waste categories.

### POST `/api/admin/kategori-sampah`
Create waste category.
```json
{
  "nama_kategori": "string (required)",
  "deskripsi": "string (optional)",
  "icon": "string emoji (optional)",
  "warna": "string hex (optional)"
}
```

### GET `/api/admin/kategori-sampah/{id}`
Get single category.

### PUT `/api/admin/kategori-sampah/{id}`
Update category.

### DELETE `/api/admin/kategori-sampah/{id}`
Delete category.

---

## ADMIN - PENYETORAN SAMPAH (WASTE DEPOSIT) MANAGEMENT

### GET `/api/admin/penyetoran-sampah`
Get all waste deposits.
```
Query params: ?page=1&limit=10&status=pending
```

### GET `/api/admin/penyetoran-sampah/{id}`
Get single deposit.

### PATCH `/api/admin/penyetoran-sampah/{id}/approve`
Approve waste deposit.
```json
{
  "poin_didapat": "integer (optional, akan dihitung otomatis jika tidak diisi)"
}
```

### PATCH `/api/admin/penyetoran-sampah/{id}/reject`
Reject waste deposit.
```json
{
  "alasan": "string (optional)"
}
```

### DELETE `/api/admin/penyetoran-sampah/{id}`
Delete waste deposit.

---

## ADMIN - PENUKARAN PRODUK MANAGEMENT

### GET `/api/admin/penukar-produk`
Get all product exchanges.

### GET `/api/admin/penukar-produk/{id}`
Get single exchange.

### PATCH `/api/admin/penukar-produk/{id}/approve`
Approve product exchange.

### PATCH `/api/admin/penukar-produk/{id}/reject`
Reject product exchange.
```json
{
  "alasan": "string (optional)"
}
```

### DELETE `/api/admin/penukar-produk/{id}`
Delete exchange.

---

## ADMIN - PENARIKAN TUNAI MANAGEMENT

### GET `/api/admin/penarikan-tunai`
Get all cash withdrawals.

### GET `/api/admin/penarikan-tunai/{id}`
Get single withdrawal.

### PATCH `/api/admin/penarikan-tunai/{id}/approve`
Approve cash withdrawal.
```json
{
  "catatan_admin": "string (optional)"
}
```

### PATCH `/api/admin/penarikan-tunai/{id}/reject`
Reject cash withdrawal.
```json
{
  "catatan_admin": "string (required - alasan penolakan)"
}
```

### DELETE `/api/admin/penarikan-tunai/{id}`
Delete withdrawal.

---

## ADMIN - NOTIFICATION MANAGEMENT

### GET `/api/admin/notifications`
Get all notifications.

### POST `/api/admin/notifications`
Create notification.
```json
{
  "user_id": "integer (required)",
  "judul": "string (required)",
  "pesan": "string (required)",
  "tipe": "string (optional)",
  "related_id": "integer (optional)",
  "related_type": "string (optional)"
}
```

### GET `/api/admin/notifications/{id}`
Get single notification.

### DELETE `/api/admin/notifications/{id}`
Delete notification.

---

## ADMIN - DASHBOARD & ANALYTICS

### GET `/api/admin/dashboard/overview`
Get dashboard overview stats.

### GET `/api/admin/dashboard/stats`
Get detailed stats.

### GET `/api/admin/analytics/waste`
Get waste analytics.

### GET `/api/admin/analytics/points`
Get points analytics.

### GET `/api/admin/leaderboard`
Get user leaderboard.

---

# ⚠️ BAGIAN 3: PENTING UNTUK FRONTEND

## 1. Gunakan snake_case untuk semua field

```javascript
// ❌ SALAH (camelCase)
{
  hargaPoin: 100,
  namaJenis: "Plastik",
  kategoriSampahId: 1
}

// ✅ BENAR (snake_case)
{
  harga_poin: 100,
  nama_jenis: "Plastik",
  kategori_sampah_id: 1
}
```

## 2. Primary Key Names

| Table | Primary Key Column |
|-------|-------------------|
| users | `user_id` |
| roles | `role_id` |
| badges | `badge_id` |
| produks | `produk_id` |
| artikels | `artikel_id` |
| jadwal_penyetorans | `jadwal_penyetoran_id` |
| kategori_sampah | `kategori_sampah_id` |
| jenis_sampah | `jenis_sampah_id` |
| tabung_sampah | `tabung_sampah_id` |
| penukaran_produk | `penukaran_produk_id` |
| penarikan_tunai | `penarikan_tunai_id` |
| notifikasi | `notifikasi_id` |
| poin_transaksis | `poin_transaksi_id` |
| user_badges | `user_badge_id` |

## 3. Enum Values

### User Status
- `active`
- `inactive`
- `suspended`

### User Tipe Nasabah
- `reguler`
- `premium`

### Badge Tipe
- `setor`
- `poin`
- `ranking`

### Produk Status
- `tersedia`
- `habis`
- `nonaktif`

### Jadwal Status
- `buka`
- `tutup`

### Transaction Status (Penyetoran, Penukaran, Penarikan)
- `pending`
- `approved`
- `rejected`
- `completed` (for penukaran only)

### Metode Ambil (Penukaran Produk)
- `ambil_ditempat`
- `dikirim`

## 4. Date & Time Formats

```javascript
// Date format
"2025-12-25"  // YYYY-MM-DD

// Time format (both accepted)
"08:00"       // HH:mm
"08:00:00"    // HH:mm:ss

// Timestamp from API
"2025-12-25T08:00:00.000000Z"  // ISO 8601
```

## 5. File Upload

Untuk upload file (foto), gunakan `FormData`:

```javascript
const formData = new FormData();
formData.append('nama', 'Product Name');
formData.append('harga_poin', 100);
formData.append('foto', fileInput.files[0]); // File object

fetch('/api/admin/produk', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    // DON'T set Content-Type for FormData, browser will set it automatically
  },
  body: formData
});
```

---

# 📝 BAGIAN 4: CONTOH RESPONSE API

## Success Response
```json
{
  "status": "success",
  "message": "Data berhasil disimpan",
  "data": { ... }
}
```

## Error Response (Validation)
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "nama": ["The nama field is required."],
    "email": ["The email has already been taken."]
  }
}
```

## Error Response (Not Found)
```json
{
  "status": "error",
  "message": "Data tidak ditemukan"
}
```

## Error Response (Unauthorized)
```json
{
  "status": "error",
  "message": "Unauthorized - Admin role required"
}
```

## Pagination Response
```json
{
  "status": "success",
  "data": {
    "users": [ ... ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 10,
      "totalUsers": 100,
      "limit": 10,
      "hasNextPage": true,
      "hasPrevPage": false
    }
  }
}
```

---

**End of Document**
Generated for Mendaur TA Project - Database Schema & API Documentation
