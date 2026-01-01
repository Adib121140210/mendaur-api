# Analisis Fitur, Endpoint, API dan Controller
## Aplikasi Mendaur Bank Sampah

**Tanggal Analisis:** 28 Desember 2025  
**Versi API:** 1.0.1

---

## 📋 Daftar Isi

1. [Modul Autentikasi](#1-modul-autentikasi)
2. [Modul Profil Pengguna](#2-modul-profil-pengguna)
3. [Modul Setor/Tabung Sampah](#3-modul-setortabung-sampah)
4. [Modul Jadwal Penyetoran](#4-modul-jadwal-penyetoran)
5. [Modul Jenis & Kategori Sampah](#5-modul-jenis--kategori-sampah)
6. [Modul Produk](#6-modul-produk)
7. [Modul Penukaran Produk](#7-modul-penukaran-produk)
8. [Modul Penarikan Tunai](#8-modul-penarikan-tunai)
9. [Modul Badge & Gamifikasi](#9-modul-badge--gamifikasi)
10. [Modul Poin](#10-modul-poin)
11. [Modul Artikel](#11-modul-artikel)
12. [Modul Notifikasi](#12-modul-notifikasi)
13. [Modul Dashboard](#13-modul-dashboard)
14. [Modul Admin Dashboard](#14-modul-admin-dashboard)
15. [Modul Admin User Management](#15-modul-admin-user-management)
16. [Modul Admin Penyetoran Sampah](#16-modul-admin-penyetoran-sampah)
17. [Modul Admin Penukaran Produk](#17-modul-admin-penukaran-produk)
18. [Modul Admin Penarikan Tunai](#18-modul-admin-penarikan-tunai)
19. [Modul Admin Analytics](#19-modul-admin-analytics)
20. [Modul Admin Leaderboard](#20-modul-admin-leaderboard)
21. [Modul Superadmin](#21-modul-superadmin)

---

## 1. Modul Autentikasi

### Deskripsi
Modul untuk mengelola autentikasi pengguna termasuk login, registrasi, logout, dan reset password.

### Controller
- `App\Http\Controllers\AuthController`
- `App\Http\Controllers\Auth\ForgotPasswordController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| POST | `/api/login` | Login pengguna | ❌ |
| POST | `/api/register` | Registrasi pengguna baru | ❌ |
| POST | `/api/logout` | Logout pengguna | ✅ |
| GET | `/api/profile` | Mendapatkan profil pengguna | ✅ |
| PUT | `/api/profile` | Update profil pengguna | ✅ |
| POST | `/api/forgot-password` | Kirim OTP untuk reset password | ❌ |
| POST | `/api/verify-otp` | Verifikasi kode OTP | ❌ |
| POST | `/api/reset-password` | Reset password dengan OTP valid | ❌ |
| POST | `/api/resend-otp` | Kirim ulang kode OTP | ❌ |

### Request/Response Contoh

**Login:**
```json
// Request
POST /api/login
{
    "email": "user@example.com",
    "password": "password123"
}

// Response
{
    "status": "success",
    "message": "Login berhasil",
    "data": {
        "user": {
            "user_id": 1,
            "nama": "John Doe",
            "email": "user@example.com",
            "no_hp": "081234567890",
            "foto_profil": "https://...",
            "actual_poin": 500,
            "level": "bronze",
            "role_id": 1,
            "role": "nasabah",
            "permissions": 17
        },
        "token": "1|abc123..."
    }
}
```

---

## 2. Modul Profil Pengguna

### Deskripsi
Modul untuk mengelola data profil pengguna termasuk foto profil, badge, dan aktivitas.

### Controller
- `App\Http\Controllers\UserController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/users/{id}` | Mendapatkan detail user | ✅ |
| PUT | `/api/users/{id}` | Update data user | ✅ |
| POST | `/api/users/{id}/update-photo` | Upload foto profil | ✅ |
| POST | `/api/users/{id}/avatar` | Upload avatar | ✅ |
| GET | `/api/users/{id}/badges` | Mendapatkan badges user | ✅ |
| GET | `/api/users/{id}/badges-list` | Mendapatkan list badges | ✅ |
| GET | `/api/users/{id}/aktivitas` | Mendapatkan aktivitas user | ✅ |
| GET | `/api/users/{id}/badge-title` | Mendapatkan badge title | ✅ |
| PUT | `/api/users/{id}/badge-title` | Set badge title | ✅ |
| GET | `/api/users/{id}/unlocked-badges` | Mendapatkan badges yang terbuka | ✅ |
| GET | `/api/users/{userId}/point-history` | Riwayat poin user | ✅ |
| GET | `/api/users/{userId}/redeem-history` | Riwayat penukaran user | ✅ |
| GET | `/api/users/{userId}/tabung-sampah` | Riwayat setor sampah | ✅ |
| GET | `/api/users/{userId}/dashboard/points` | Dashboard poin user | ✅ |

---

## 3. Modul Setor/Tabung Sampah

### Deskripsi
Modul untuk mengelola penyetoran sampah oleh nasabah.

### Controller
- `App\Http\Controllers\TabungSampahController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/tabung-sampah` | List semua tabung sampah | ✅ |
| POST | `/api/tabung-sampah` | Buat penyetoran baru | ✅ |
| GET | `/api/tabung-sampah/{id}` | Detail penyetoran | ✅ |
| PUT | `/api/tabung-sampah/{id}` | Update penyetoran | ✅ |
| DELETE | `/api/tabung-sampah/{id}` | Hapus penyetoran | ✅ |
| GET | `/api/users/{id}/tabung-sampah` | Penyetoran by user | ✅ |
| POST | `/api/tabung-sampah/{id}/approve` | Approve penyetoran | ✅ Admin |
| POST | `/api/tabung-sampah/{id}/reject` | Reject penyetoran | ✅ Admin |

### Request Contoh

**Setor Sampah:**
```json
// Request
POST /api/tabung-sampah
Content-Type: multipart/form-data

{
    "user_id": 1,
    "jadwal_penyetoran_id": 1,
    "nama_lengkap": "John Doe",
    "no_hp": "081234567890",
    "titik_lokasi": "RT 05 RW 02",
    "jenis_sampah": "Plastik",
    "foto_sampah": [file] // max 10MB
}
```

---

## 4. Modul Jadwal Penyetoran

### Deskripsi
Modul untuk mengelola jadwal penyetoran sampah.

### Controller
- `App\Http\Controllers\JadwalPenyetoranController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/jadwal-penyetoran` | List jadwal penyetoran | ❌ |
| GET | `/api/jadwal-penyetoran/{id}` | Detail jadwal | ❌ |
| GET | `/api/jadwal-penyetoran-aktif` | Jadwal yang aktif | ❌ |
| POST | `/api/jadwal-penyetoran` | Buat jadwal baru | ✅ |
| PUT | `/api/jadwal-penyetoran/{id}` | Update jadwal | ✅ |
| DELETE | `/api/jadwal-penyetoran/{id}` | Hapus jadwal | ✅ |

---

## 5. Modul Jenis & Kategori Sampah

### Deskripsi
Modul untuk mengelola jenis dan kategori sampah yang dapat disetor.

### Controller
- `App\Http\Controllers\JenisSampahController`
- `App\Http\Controllers\KategoriSampahController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/jenis-sampah` | List jenis sampah | ❌ |
| GET | `/api/jenis-sampah/{id}` | Detail jenis sampah | ❌ |
| POST | `/api/jenis-sampah` | Buat jenis sampah | ✅ Superadmin |
| PUT | `/api/jenis-sampah/{id}` | Update jenis sampah | ✅ Superadmin |
| DELETE | `/api/jenis-sampah/{id}` | Hapus jenis sampah | ✅ Superadmin |
| GET | `/api/kategori-sampah` | List kategori sampah | ❌ |
| GET | `/api/kategori-sampah/{id}` | Detail kategori | ❌ |
| GET | `/api/kategori-sampah/{id}/jenis` | Jenis by kategori | ❌ |
| GET | `/api/jenis-sampah-all` | Semua jenis dengan kategori | ❌ |
| POST | `/api/kategori-sampah` | Buat kategori | ✅ Superadmin |
| PUT | `/api/kategori-sampah/{id}` | Update kategori | ✅ Superadmin |
| DELETE | `/api/kategori-sampah/{id}` | Hapus kategori | ✅ Superadmin |

---

## 6. Modul Produk

### Deskripsi
Modul untuk mengelola produk yang dapat ditukar dengan poin.

### Controller
- `App\Http\Controllers\ProdukController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/produk` | List produk | ❌ |
| GET | `/api/produk/{id}` | Detail produk | ❌ |
| POST | `/api/produk` | Buat produk baru | ✅ Superadmin |
| PUT | `/api/produk/{id}` | Update produk | ✅ Superadmin |
| DELETE | `/api/produk/{id}` | Hapus produk | ✅ Superadmin |

---

## 7. Modul Penukaran Produk

### Deskripsi
Modul untuk mengelola penukaran produk menggunakan poin oleh nasabah.

### Controller
- `App\Http\Controllers\PenukaranProdukController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/penukaran-produk` | List penukaran user | ✅ |
| POST | `/api/penukaran-produk` | Buat penukaran baru | ✅ |
| GET | `/api/penukaran-produk/{id}` | Detail penukaran | ✅ |
| GET | `/api/penukaran-produk/user/{userId}` | Penukaran by user | ✅ |
| PUT | `/api/penukaran-produk/{id}/cancel` | Batalkan penukaran | ✅ |
| DELETE | `/api/penukaran-produk/{id}` | Hapus penukaran | ✅ |

### Request Contoh

**Tukar Produk:**
```json
// Request
POST /api/penukaran-produk
{
    "produk_id": 1,
    "jumlah": 1,
    "jumlah_poin": 500
}
```

---

## 8. Modul Penarikan Tunai

### Deskripsi
Modul untuk mengelola penarikan tunai (konversi poin ke rupiah).

### Controller
- `App\Http\Controllers\PenarikanTunaiController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/penarikan-tunai` | List penarikan | ✅ |
| POST | `/api/penarikan-tunai` | Ajukan penarikan baru | ✅ |
| GET | `/api/penarikan-tunai/{id}` | Detail penarikan | ✅ |
| GET | `/api/penarikan-tunai/summary` | Summary penarikan | ✅ |
| GET | `/api/penarikan-tunai/user/{userId}` | Penarikan by user | ✅ |

### Request Contoh

**Ajukan Penarikan:**
```json
// Request
POST /api/penarikan-tunai
{
    "jumlah_poin": 2000,
    "nomor_rekening": "1234567890",
    "nama_bank": "BCA",
    "nama_penerima": "John Doe"
}

// Konversi: 100 poin = Rp 1.000
// 2000 poin = Rp 20.000
```

---

## 9. Modul Badge & Gamifikasi

### Deskripsi
Modul untuk mengelola sistem badge dan gamifikasi pengguna.

### Controller
- `App\Http\Controllers\BadgeController`
- `App\Http\Controllers\Api\BadgeProgressController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/badges` | List semua badges | ❌ |
| GET | `/api/users/{userId}/badge-progress` | Progress badge user | ❌ |
| POST | `/api/users/{userId}/check-badges` | Cek & award badges | ❌ |
| GET | `/api/user/badges/progress` | Progress badge user login | ✅ |
| GET | `/api/user/badges/completed` | Badges yang sudah selesai | ✅ |
| GET | `/api/badges/leaderboard` | Leaderboard badges | ✅ |
| GET | `/api/badges/available` | Badges yang tersedia | ✅ |

---

## 10. Modul Poin

### Deskripsi
Modul untuk mengelola sistem poin pengguna.

### Controller
- `App\Http\Controllers\PointController`
- `App\Http\Controllers\AdminPointController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/user/{id}/poin` | Poin user | ✅ |
| GET | `/api/poin/history` | Riwayat poin | ✅ |
| GET | `/api/poin/breakdown/{userId}` | Breakdown poin | ✅ |
| POST | `/api/poin/bonus` | Award bonus poin | ✅ |
| GET | `/api/user/{id}/poin/statistics` | Statistik poin | ✅ |
| GET | `/api/poin/admin/stats` | Admin stats poin | ✅ Admin |
| GET | `/api/poin/admin/history` | Admin history poin | ✅ Admin |
| GET | `/api/poin/admin/redemptions` | Admin redemptions | ✅ Admin |

---

## 11. Modul Artikel

### Deskripsi
Modul untuk mengelola artikel edukasi.

### Controller
- `App\Http\Controllers\ArtikelController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/artikel` | List artikel | ❌ |
| GET | `/api/artikel/{slug}` | Detail artikel by slug | ❌ |
| POST | `/api/artikel` | Buat artikel | ✅ Superadmin |
| PUT | `/api/artikel/{id}` | Update artikel | ✅ Superadmin |
| DELETE | `/api/artikel/{id}` | Hapus artikel | ✅ Superadmin |

---

## 12. Modul Notifikasi

### Deskripsi
Modul untuk mengelola notifikasi pengguna.

### Controller
- `App\Http\Controllers\NotificationController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/notifications` | List notifikasi | ✅ |
| GET | `/api/notifications/unread` | Notifikasi belum dibaca | ✅ |
| GET | `/api/notifications/unread-count` | Jumlah belum dibaca | ✅ |
| GET | `/api/notifications/{id}` | Detail notifikasi | ✅ |
| PATCH | `/api/notifications/{id}/read` | Tandai sudah dibaca | ✅ |
| PATCH | `/api/notifications/mark-all-read` | Tandai semua sudah dibaca | ✅ |
| DELETE | `/api/notifications/{id}` | Hapus notifikasi | ✅ |
| POST | `/api/notifications/create` | Buat notifikasi | ✅ |

---

## 13. Modul Dashboard

### Deskripsi
Modul untuk menampilkan dashboard statistik pengguna.

### Controller
- `App\Http\Controllers\DashboardController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/dashboard/stats/{userId}` | Stats dashboard user | ✅ |
| GET | `/api/dashboard/leaderboard` | Leaderboard nasabah | ✅ |
| GET | `/api/dashboard/global-stats` | Stats global | ❌ |

---

## 14. Modul Admin Dashboard

### Deskripsi
Modul untuk dashboard admin dengan overview dan statistik.

### Controller
- `App\Http\Controllers\DashboardAdminController`
- `App\Http\Controllers\Admin\AdminDashboardController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/admin/dashboard/overview` | Overview dashboard admin | ✅ Admin |
| GET | `/api/admin/dashboard/stats` | Stats dashboard admin | ✅ Admin |
| GET | `/api/admin/dashboard/users` | Users stats | ✅ Admin |
| GET | `/api/admin/dashboard/waste-summary` | Summary sampah | ✅ Admin |
| GET | `/api/admin/dashboard/point-summary` | Summary poin | ✅ Admin |
| GET | `/api/admin/dashboard/waste-by-user` | Sampah per user | ✅ Admin |
| GET | `/api/admin/dashboard/report` | Report dashboard | ✅ Admin |

---

## 15. Modul Admin User Management

### Deskripsi
Modul untuk mengelola pengguna oleh admin.

### Controller
- `App\Http\Controllers\Admin\AdminUserController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/admin/users` | List users | ✅ Admin |
| POST | `/api/admin/users` | Buat user baru | ✅ Admin |
| GET | `/api/admin/users/{userId}` | Detail user | ✅ Admin |
| PUT | `/api/admin/users/{userId}` | Update user | ✅ Admin |
| PATCH | `/api/admin/users/{userId}/status` | Update status user | ✅ Admin |
| PATCH | `/api/admin/users/{userId}/role` | Update role user | ✅ Admin |
| PATCH | `/api/admin/users/{userId}/tipe` | Update tipe user | ✅ Admin |
| DELETE | `/api/admin/users/{userId}` | Hapus user | ✅ Admin |

---

## 16. Modul Admin Penyetoran Sampah

### Deskripsi
Modul untuk mengelola penyetoran sampah oleh admin (approval/reject).

### Controller
- `App\Http\Controllers\Admin\AdminWasteController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/admin/penyetoran-sampah` | List penyetoran | ✅ Admin |
| GET | `/api/admin/penyetoran-sampah/{id}` | Detail penyetoran | ✅ Admin |
| PATCH | `/api/admin/penyetoran-sampah/{id}/approve` | Approve penyetoran | ✅ Admin |
| PATCH | `/api/admin/penyetoran-sampah/{id}/reject` | Reject penyetoran | ✅ Admin |
| DELETE | `/api/admin/penyetoran-sampah/{id}` | Hapus penyetoran | ✅ Admin |
| GET | `/api/admin/penyetoran-sampah/stats/overview` | Stats penyetoran | ✅ Admin |

---

## 17. Modul Admin Penukaran Produk

### Deskripsi
Modul untuk mengelola penukaran produk oleh admin (approval/reject).

### Controller
- `App\Http\Controllers\Admin\AdminPenukaranProdukController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/admin/penukar-produk` | List penukaran | ✅ Admin |
| GET | `/api/admin/penukar-produk/{exchangeId}` | Detail penukaran | ✅ Admin |
| PATCH | `/api/admin/penukar-produk/{exchangeId}/approve` | Approve penukaran | ✅ Admin |
| PATCH | `/api/admin/penukar-produk/{exchangeId}/reject` | Reject penukaran | ✅ Admin |
| DELETE | `/api/admin/penukar-produk/{exchangeId}` | Hapus penukaran | ✅ Admin |
| GET | `/api/admin/penukar-produk/stats/overview` | Stats penukaran | ✅ Admin |

---

## 18. Modul Admin Penarikan Tunai

### Deskripsi
Modul untuk mengelola penarikan tunai oleh admin (approval/reject).

### Controller
- `App\Http\Controllers\Admin\AdminPenarikanTunaiController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/admin/penarikan-tunai` | List penarikan | ✅ Admin |
| GET | `/api/admin/penarikan-tunai/{withdrawalId}` | Detail penarikan | ✅ Admin |
| PATCH | `/api/admin/penarikan-tunai/{withdrawalId}/approve` | Approve penarikan | ✅ Admin |
| PATCH | `/api/admin/penarikan-tunai/{withdrawalId}/reject` | Reject penarikan | ✅ Admin |
| DELETE | `/api/admin/penarikan-tunai/{withdrawalId}` | Hapus penarikan | ✅ Admin |
| GET | `/api/admin/penarikan-tunai/stats/overview` | Stats penarikan | ✅ Admin |

---

## 19. Modul Admin Analytics

### Deskripsi
Modul untuk analytics dan reporting admin.

### Controller
- `App\Http\Controllers\Admin\AdminAnalyticsController`
- `App\Http\Controllers\Admin\AdminReportsController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/admin/analytics/waste` | Analytics sampah | ✅ Admin |
| GET | `/api/admin/analytics/waste-by-user` | Sampah per user | ✅ Admin |
| GET | `/api/admin/analytics/points` | Analytics poin | ✅ Admin |
| GET | `/api/admin/reports/list` | List reports | ✅ Admin |
| POST | `/api/admin/reports/generate` | Generate report | ✅ Admin |
| GET | `/api/admin/export` | Export data | ✅ Admin |

---

## 20. Modul Admin Leaderboard

### Deskripsi
Modul untuk mengelola leaderboard oleh admin.

### Controller
- `App\Http\Controllers\Admin\AdminLeaderboardController`

### Endpoints

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/admin/leaderboard` | Leaderboard data | ✅ Admin |
| GET | `/api/admin/leaderboard/settings` | Settings leaderboard | ✅ Admin |
| PUT | `/api/admin/leaderboard/settings` | Update settings | ✅ Admin |
| POST | `/api/admin/leaderboard/reset` | Reset leaderboard | ✅ Admin |
| GET | `/api/admin/leaderboard/history` | History leaderboard | ✅ Admin |

---

## 21. Modul Superadmin

### Deskripsi
Modul khusus superadmin untuk mengelola admin, roles, permissions, dan sistem.

### Controller
- `App\Http\Controllers\Admin\AdminManagementController`
- `App\Http\Controllers\Admin\RoleManagementController`
- `App\Http\Controllers\Admin\PermissionAssignmentController`
- `App\Http\Controllers\Admin\AuditLogController`
- `App\Http\Controllers\Admin\SystemSettingsController`
- `App\Http\Controllers\Admin\BadgeManagementController`

### Endpoints Admin Management

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/superadmin/admins` | List admins | ✅ Superadmin |
| POST | `/api/superadmin/admins` | Buat admin baru | ✅ Superadmin |
| GET | `/api/superadmin/admins/{adminId}` | Detail admin | ✅ Superadmin |
| PUT | `/api/superadmin/admins/{adminId}` | Update admin | ✅ Superadmin |
| DELETE | `/api/superadmin/admins/{adminId}` | Hapus admin | ✅ Superadmin |
| GET | `/api/superadmin/admins/{adminId}/activity` | Activity admin | ✅ Superadmin |

### Endpoints Role Management

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/superadmin/roles` | List roles | ✅ Superadmin |
| POST | `/api/superadmin/roles` | Buat role baru | ✅ Superadmin |
| GET | `/api/superadmin/roles/{roleId}` | Detail role | ✅ Superadmin |
| PUT | `/api/superadmin/roles/{roleId}` | Update role | ✅ Superadmin |
| DELETE | `/api/superadmin/roles/{roleId}` | Hapus role | ✅ Superadmin |
| GET | `/api/superadmin/roles/{roleId}/users` | Users by role | ✅ Superadmin |

### Endpoints Permission Management

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/superadmin/permissions` | List permissions | ✅ Superadmin |
| GET | `/api/superadmin/roles/{roleId}/permissions` | Permissions by role | ✅ Superadmin |
| POST | `/api/superadmin/roles/{roleId}/permissions` | Assign permission | ✅ Superadmin |
| POST | `/api/superadmin/roles/{roleId}/permissions/bulk` | Bulk assign | ✅ Superadmin |
| DELETE | `/api/superadmin/roles/{roleId}/permissions/{permissionId}` | Revoke permission | ✅ Superadmin |

### Endpoints Badge Management (Admin)

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/admin/badges` | List badges | ✅ Admin |
| POST | `/api/admin/badges` | Buat badge baru | ✅ Admin |
| GET | `/api/admin/badges/{badgeId}` | Detail badge | ✅ Admin |
| PUT | `/api/admin/badges/{badgeId}` | Update badge | ✅ Admin |
| DELETE | `/api/admin/badges/{badgeId}` | Hapus badge | ✅ Admin |
| POST | `/api/admin/badges/{badgeId}/assign` | Assign badge ke user | ✅ Admin |
| POST | `/api/admin/badges/{badgeId}/revoke` | Revoke badge dari user | ✅ Admin |
| GET | `/api/admin/badges/{badgeId}/users` | Users dengan badge | ✅ Admin |

### Endpoints System Settings

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/superadmin/settings` | List settings | ✅ Superadmin |
| GET | `/api/superadmin/settings/{key}` | Get setting | ✅ Superadmin |
| PUT | `/api/superadmin/settings/{key}` | Update setting | ✅ Superadmin |
| GET | `/api/superadmin/health` | System health | ✅ Superadmin |
| GET | `/api/superadmin/cache-stats` | Cache statistics | ✅ Superadmin |
| POST | `/api/superadmin/cache/clear` | Clear cache | ✅ Superadmin |
| GET | `/api/superadmin/database-stats` | Database stats | ✅ Superadmin |

### Endpoints Audit Log

| Method | Endpoint | Fungsi | Auth |
|--------|----------|--------|------|
| GET | `/api/superadmin/audit-logs` | List audit logs | ✅ Superadmin |
| GET | `/api/superadmin/audit-logs/{logId}` | Detail audit log | ✅ Superadmin |
| GET | `/api/superadmin/system-logs` | System logs | ✅ Superadmin |
| GET | `/api/superadmin/audit-logs/users/activity` | User activity | ✅ Superadmin |
| POST | `/api/superadmin/audit-logs/clear-old` | Clear old logs | ✅ Superadmin |
| GET | `/api/superadmin/audit-logs/export` | Export logs | ✅ Superadmin |

---

## 📊 Ringkasan Statistik

| Kategori | Jumlah |
|----------|--------|
| Total Controllers | 35+ |
| Total Endpoints | 150+ |
| Public Endpoints | ~20 |
| Protected Endpoints | ~130 |
| Admin Only Endpoints | ~60 |
| Superadmin Only Endpoints | ~25 |

---

## 🔐 Level Akses

| Level | Deskripsi |
|-------|-----------|
| ❌ Public | Tidak perlu login |
| ✅ | Perlu login (Bearer token) |
| ✅ Admin | Perlu login sebagai Admin |
| ✅ Superadmin | Perlu login sebagai Superadmin |

---

## 🔄 UPDATE: Perubahan Skema Poin (1 Januari 2026)

### Skema Dual-Poin Baru

| Field | Deskripsi | Behavior |
|-------|-----------|----------|
| `display_poin` | Poin untuk leaderboard/ranking | Naik saat dapat poin, **TIDAK PERNAH TURUN** |
| `actual_poin` | Poin yang bisa digunakan | Naik saat dapat, turun saat spend |

### Field yang Deprecated:
- ❌ `total_poin` → Gunakan `display_poin` atau `actual_poin`
- ❌ `poin_tercatat` → Gunakan `display_poin`

### Controllers yang Diperbaiki:

1. **AdminPenukaranProdukController.php**
   - ✅ `reject()`: Gunakan `PointService::refundRedemptionPoints()`

2. **AdminPointsController.php**
   - ✅ `award()`: Gunakan `PointService::earnPoints()`

3. **AdminWasteController.php**
   - ✅ `approve()`: Gunakan `PointService::earnPoints()`

4. **AdminUserController.php**
   - ✅ `store()`: Init dengan `actual_poin`, `display_poin`

5. **BadgeProgressController.php**
   - ✅ Return `display_poin` dan `actual_poin`

6. **DashboardAdminController.php**
   - ✅ Select `actual_poin`, `display_poin`

7. **AdminPointController.php**
   - ✅ Sum `display_poin` untuk statistik total

8. **DualNasabahFeatureAccessService.php**
   - ✅ Gunakan PointService untuk konsistensi

### Aturan Penggunaan:

```php
// EARNING (dapat poin)
PointService::earnPoints($user, $amount, $source, $desc, $refId, $refType);
// Efek: actual_poin += amount, display_poin += amount

// SPENDING (gunakan poin)
PointService::spendPoints($user, $amount, $source, $refId, $refType, $desc);
// Efek: actual_poin -= amount, display_poin TIDAK BERUBAH

// REFUND (kembalikan poin)
PointService::refundPoints($user, $amount, $source, $desc, $refId, $refType);
// Efek: actual_poin += amount, display_poin TIDAK BERUBAH

// Cek saldo
$user->getUsablePoin();  // actual_poin (saldo)
$user->display_poin;     // untuk leaderboard
```

---

**Dokumen ini dibuat otomatis pada 28 Desember 2025**  
**Update terakhir: 1 Januari 2026 - Perbaikan skema dual-poin**
