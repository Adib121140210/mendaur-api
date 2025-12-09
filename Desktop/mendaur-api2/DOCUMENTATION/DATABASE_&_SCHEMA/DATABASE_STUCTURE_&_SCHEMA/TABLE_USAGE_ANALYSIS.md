# 📊 ANALISIS TABEL DATABASE MENDAUR
## Tabel yang Berkaitan dengan Sistem vs Tidak Digunakan

---

## 📈 RINGKASAN KESELURUHAN

| Kategori | Jumlah | Tabel |
|----------|--------|-------|
| **INTI SISTEM** (Business Logic) | **15 tabel** | Core functionality |
| **LARAVEL SUPPORT** (Framework) | **8 tabel** | Infrastructure |
| **TIDAK DIGUNAKAN** | **6 tabel** | Empty/Legacy |
| **TOTAL** | **29 tabel** | - |

---

## 🎯 GROUP 1: INTI SISTEM MENDAUR (15 Tabel - PENTING)

Tabel-tabel ini adalah **backbone dari sistem** dan berisi data bisnis aktual:

### **A. Manajemen User & Otentikasi (5 tabel)**

| # | Tabel | Rows | Status | Deskripsi |
|---|-------|------|--------|-----------|
| 1 | `USERS` | ✓ Active | ✅ **CRITICAL** | Data user (nasabah, admin, superadmin) |
| 2 | `ROLES` | 3 roles | ✅ **CRITICAL** | Tipe user: nasabah (1), admin (2), superadmin (3) |
| 3 | `ROLE_PERMISSIONS` | 119 entries | ✅ **CRITICAL** | Mapping permission ke role |
| 4 | `SESSIONS` | - | ✅ **IMPORTANT** | User sessions (Laravel) |
| 5 | `NOTIFIKASI` | ✓ Active | ✅ **IMPORTANT** | Notifikasi untuk user |

**Hubungan:**
```
USERS ──1:M──> ROLES (melalui role_id)
         ──1:M──> ROLE_PERMISSIONS (validasi permission)
         ──1:M──> SESSIONS (login tracking)
         ──1:M──> NOTIFIKASI (terima notifikasi)
```

---

### **B. Manajemen Sampah & Tabung (4 tabel)**

| # | Tabel | Rows | Status | Deskripsi |
|---|-------|------|--------|-----------|
| 6 | `KATEGORI_SAMPAH` | ✓ Active | ✅ **CRITICAL** | Kategori sampah (plastik, kertas, dll) |
| 7 | `JENIS_SAMPAH` | 20 rows | ✅ **CRITICAL** | Jenis detail (botol, kardus, dll) |
| 8 | `TABUNG_SAMPAH` | ✓ Active | ✅ **CRITICAL** | Lokasi tempat sampah |
| 9 | `JADWAL_PENYETORANS` | ✓ Active | ✅ **CRITICAL** | Jadwal pengambilan sampah |

**Hubungan:**
```
KATEGORI_SAMPAH ──1:M──> JENIS_SAMPAH
JENIS_SAMPAH ──1:M──> TABUNG_SAMPAH
JADWAL_PENYETORANS ──1:M──> (tracking pengambilan)
```

---

### **C. Transaksi & Poin (3 tabel)**

| # | Tabel | Rows | Status | Deskripsi |
|---|-------|------|--------|-----------|
| 10 | `TRANSAKSIS` | ✓ Active | ✅ **CRITICAL** | Transaksi deposit sampah |
| 11 | `KATEGORI_TRANSAKSI` | ✓ Active | ✅ **CRITICAL** | Jenis transaksi |
| 12 | `POIN_TRANSAKSIS` | ✓ Active | ✅ **CRITICAL** | History poin (naik/turun) |

**Hubungan:**
```
KATEGORI_TRANSAKSI ──1:M──> TRANSAKSIS (jenis transaksi)
TRANSAKSIS ──1:M──> POIN_TRANSAKSIS (poin diperoleh)
USERS ──1:M──> TRANSAKSIS (siapa setor)
```

---

### **D. Produk & Penukaran (2 tabel)**

| # | Tabel | Rows | Status | Deskripsi |
|---|-------|------|--------|-----------|
| 13 | `PRODUKS` | 5 items | ✅ **CRITICAL** | Produk yang bisa ditukar |
| 14 | `PENUKARAN_PRODUK` | ✓ Active | ✅ **CRITICAL** | Riwayat penukaran poin ke produk |

**Hubungan:**
```
PRODUKS ──1:M──> PENUKARAN_PRODUK
USERS ──1:M──> PENUKARAN_PRODUK (siapa tukar)
PENUKARAN_PRODUK ──M:1──> PRODUKS (produk apa)
```

---

### **E. Gamifikasi (2 tabel)**

| # | Tabel | Rows | Status | Deskripsi |
|---|-------|------|--------|-----------|
| 15 | `BADGES` | 10 badges | ✅ **IMPORTANT** | Badge/achievement list |
| 16 | `USER_BADGES` | ✓ Active | ✅ **IMPORTANT** | Badge yang dimiliki user |

**Hubungan:**
```
BADGES ──1:M──> USER_BADGES
USERS ──1:M──> USER_BADGES (punya badge)
BADGE_PROGRESS ──1:M──> (track progress)
```

---

### **F. Penarikan Tunai (1 tabel)**

| # | Tabel | Rows | Status | Deskripsi |
|---|-------|------|--------|-----------|
| 17 | `PENARIKAN_TUNAI` | ✓ Active | ✅ **IMPORTANT** | Request penarikan poin jadi uang |

**Hubungan:**
```
USERS ──1:M──> PENARIKAN_TUNAI (siapa minta)
PENARIKAN_TUNAI ──M:1──> (approval admin)
```

---

### **G. Audit & Logging (2 tabel)**

| # | Tabel | Rows | Status | Deskripsi |
|---|-------|------|--------|-----------|
| 18 | `LOG_AKTIVITAS` | 19+ logs | ✅ **IMPORTANT** | Activity log user |
| 19 | `AUDIT_LOGS` | Empty | ✅ **IMPORTANT** | Audit trail admin actions |

**Hubungan:**
```
USERS ──1:M──> LOG_AKTIVITAS (siapa aktivitas)
USERS ──1:M──> AUDIT_LOGS (admin mana action)
```

---

### **H. Content Management (1 tabel)**

| # | Tabel | Rows | Status | Deskripsi |
|---|-------|------|--------|-----------|
| 20 | `ARTIKELS` | 8 articles | ✅ **INFORMATIONAL** | Blog/artikel edukatif |

**Hubungan:**
```
ARTIKELS ──(standalone)──> (tidak ada FK, content only)
```

---

## 🔧 GROUP 2: LARAVEL FRAMEWORK SUPPORT (8 Tabel - INFRASTRUCTURE)

Tabel-tabel ini adalah **sistem infrastruktur Laravel** yang diperlukan framework:

| # | Tabel | Rows | Status | Tujuan |
|---|-------|------|--------|--------|
| 21 | `MIGRATIONS` | - | ✅ Required | Track database migrations |
| 22 | `SESSIONS` | - | ✅ Required | Session management |
| 23 | `PERSONAL_ACCESS_TOKENS` | - | ✅ Required | API token authentication (Sanctum) |
| 24 | `PASSWORD_RESET_TOKENS` | - | ✅ Required | Password reset functionality |
| 25 | `CACHE` | Empty | ✅ Optional | Cache storage |
| 26 | `CACHE_LOCKS` | Empty | ✅ Optional | Cache locking mechanism |
| 27 | `JOBS` | Empty | ✅ Optional | Queue job tracking |
| 28 | `JOB_BATCHES` | Empty | ✅ Optional | Batch job tracking |

**Catatan:**
- Tabel ini **HARUS ADA** untuk fungsi framework Laravel
- Beberapa mungkin empty tapi tidak boleh dihapus
- Jangan modifikasi unless Anda tahu konsekuensinya

---

## ⚠️ GROUP 3: TIDAK DIGUNAKAN / LEGACY (6 Tabel - BISA DIHAPUS)

Tabel-tabel ini **EMPTY** atau **TIDAK DIGUNAKAN** dalam sistem:

| # | Tabel | Rows | Status | Rekomendasi |
|---|-------|------|--------|-------------|
| 29 | `FAILED_JOBS` | Empty | 🟡 Legacy | Hapus jika tidak perlu job queue |
| - | `BADGE_PROGRESS` | 60 | ⚠️ CHECK | **SEHARUSNYA DIGUNAKAN** tapi mungkin belum terintegrasi |
| - | - | - | - | - |

---

## 🔍 ANALISIS HUBUNGAN TABEL

### **Struktur Relasi Utama:**

```
┌─────────────────────────────────────────────────────────┐
│                    USERS (HUB UTAMA)                    │
│  ├─ role_id ──> ROLES                                   │
│  ├─ 1:M ──> TRANSAKSIS (deposit sampah)                │
│  ├─ 1:M ──> POIN_TRANSAKSIS (history poin)             │
│  ├─ 1:M ──> PENUKARAN_PRODUK (tukar poin)              │
│  ├─ 1:M ──> PENARIKAN_TUNAI (minta uang)               │
│  ├─ 1:M ──> USER_BADGES (badge dimiliki)               │
│  ├─ 1:M ──> LOG_AKTIVITAS (activity log)               │
│  ├─ 1:M ──> AUDIT_LOGS (admin action)                  │
│  ├─ 1:M ──> NOTIFIKASI (terima notif)                  │
│  └─ 1:M ──> SESSIONS (login session)                   │
└─────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────┐
│            KATEGORI_SAMPAH (Waste Hierarchy)        │
│  └─ 1:M ──> JENIS_SAMPAH                             │
│     └─ 1:M ──> TABUNG_SAMPAH (location)             │
│        └─ 1:M ──> JADWAL_PENYETORANS (schedule)     │
└──────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│          KATEGORI_TRANSAKSI (Transaction Types)     │
│  └─ 1:M ──> TRANSAKSIS                              │
│     └─ 1:M ──> POIN_TRANSAKSIS (poin effects)       │
└─────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────┐
│             BADGES (Gamification System)             │
│  ├─ 1:M ──> USER_BADGES (who has)                   │
│  └─ 1:M ──> BADGE_PROGRESS (progress tracking)      │
└──────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────┐
│             PRODUKS (Redemption Shop)                │
│  └─ 1:M ──> PENUKARAN_PRODUK (redemption records)   │
└──────────────────────────────────────────────────────┘
```

---

## 📋 TABEL DATA FLOW

### **Alur 1: Deposit Sampah (Core Business)**
```
User (USERS) 
  ↓ submit deposit
Transaksi (TRANSAKSIS)
  ├─ record: jenis_sampah + tabung_sampah + kategori_transaksi
  ↓ admin approve
Poin (POIN_TRANSAKSIS) ← poin ditambah
  ↓ if poin enough
Penukaran_Produk (PENUKARAN_PRODUK) ← user tukar poin
  ↓ track achievement
Badge_Progress (BADGE_PROGRESS) ← progress track
  ↓ if achieve
User_Badges (USER_BADGES) ← unlock badge
```

### **Alur 2: Penarikan Tunai**
```
User (USERS)
  ↓ request withdraw
Penarikan_Tunai (PENARIKAN_TUNAI)
  ↓ admin approve
LOG_AKTIVITAS ← dicatat
  ↓ process payment
AUDIT_LOGS ← audit trail
```

### **Alur 3: Sistem Notifikasi**
```
Any Event (deposit, badge, withdrawal approval, etc.)
  ↓ trigger
Notifikasi (NOTIFIKASI)
  ↓ send to user
Users (USERS) ← notif received
```

---

## ✅ TABEL YANG HARUS DIGUNAKAN

### **Priority 1 - CRITICAL (Sistem tidak jalan tanpa ini):**
1. ✅ USERS
2. ✅ ROLES
3. ✅ ROLE_PERMISSIONS
4. ✅ KATEGORI_SAMPAH
5. ✅ JENIS_SAMPAH
6. ✅ TRANSAKSIS
7. ✅ POIN_TRANSAKSIS
8. ✅ PRODUKS
9. ✅ PENUKARAN_PRODUK

### **Priority 2 - IMPORTANT (Berfungsi penuh):**
10. ✅ TABUNG_SAMPAH
11. ✅ JADWAL_PENYETORANS
12. ✅ KATEGORI_TRANSAKSI
13. ✅ BADGES
14. ✅ USER_BADGES
15. ✅ BADGE_PROGRESS
16. ✅ PENARIKAN_TUNAI
17. ✅ LOG_AKTIVITAS
18. ✅ AUDIT_LOGS
19. ✅ NOTIFIKASI

### **Priority 3 - INFORMATIONAL:**
20. ✅ ARTIKELS (content only, tidak block sistem)

### **Priority 4 - FRAMEWORK (Jangan dihapus):**
21-28. Laravel infrastructure tables (REQUIRED)

---

## ⚠️ TABEL YANG TIDAK DIGUNAKAN

### **Definitely Empty/Not Used:**
- `FAILED_JOBS` - Hanya jika tidak pakai queue jobs
- `CACHE` - Empty, tapi bisa digunakan untuk caching
- `CACHE_LOCKS` - Bergantung pada CACHE

### **Mungkin Not Used:**
- Lihat tabel mana yang ROW COUNT = 0 dan tidak ada FK masuk

---

## 🎯 REKOMENDASI

### **1. Untuk Development:**
```sql
-- Jangan hapus:
-- - Semua Priority 1 & 2 tabel
-- - Semua Laravel framework tables

-- Bisa dikosongkan sementara:
-- CACHE, CACHE_LOCKS, FAILED_JOBS
-- (tapi jangan dihapus table-nya)
```

### **2. Untuk Production:**
```sql
-- HARUS digunakan semua Priority 1, 2, 3 tabel
-- Framework tables HARUS ada
-- FAILED_JOBS bisa dihapus jika tidak pakai queue
```

### **3. Untuk Optimasi:**
```sql
-- Clean up empty tables secara berkala
-- Tapi JANGAN delete table structure-nya
-- TRUNCATE table lebih aman daripada DELETE
```

---

## 📊 DATA USAGE STATISTICS

| Tabel | Row Count | Used? | Status |
|-------|-----------|-------|--------|
| USERS | ✓ | YES | 🟢 Active |
| ROLES | 3 | YES | 🟢 Active |
| TRANSAKSIS | ✓ | YES | 🟢 Active |
| POIN_TRANSAKSIS | ✓ | YES | 🟢 Active |
| BADGE_PROGRESS | 60 | YES | 🟢 Active |
| LOG_AKTIVITAS | 19+ | YES | 🟢 Active |
| KATEGORI_SAMPAH | ✓ | YES | 🟢 Active |
| JENIS_SAMPAH | 20 | YES | 🟢 Active |
| BADGES | 10 | YES | 🟢 Active |
| PRODUKS | 5 | YES | 🟢 Active |
| PENARIKAN_TUNAI | ✓ | YES | 🟢 Active |
| ARTIKELS | 8 | YES | 🟢 Active |
| NOTIFIKASI | ✓ | YES | 🟢 Active |
| PENUKARAN_PRODUK | ✓ | YES | 🟢 Active |
| USER_BADGES | ✓ | YES | 🟢 Active |
| AUDIT_LOGS | 0 | MAYBE | 🟡 Setup ready |
| SESSIONS | - | AUTO | 🟢 Active |
| CACHE | 0 | NO | 🟡 Optional |
| FAILED_JOBS | 0 | NO | 🟡 Optional |
| JOBS | 0 | NO | 🟡 Optional |

---

## 🎬 KESIMPULAN

✅ **Sistem Mendaur menggunakan:**
- **20 tabel** untuk business logic + framework
- **9 tabel** untuk inti sistem sampah/poin
- **8 tabel** untuk Laravel infrastructure
- **3 tabel** untuk gamification
- **2 tabel** untuk commerce/redemption

⚠️ **Tabel yang perlu diperhatikan:**
- Semua tabel Priority 1-3 HARUS ada dan aktif
- Framework tables HARUS ada (jangan dihapus)
- CACHE & JOBS bisa dikonfigurasi sesuai kebutuhan
- BADGE_PROGRESS dan AUDIT_LOGS mungkin belum fully integrated

✋ **Jangan dihapus:**
- Setiap tabel framework Laravel
- Setiap tabel yang punya FK masuk/keluar
- Setiap tabel business logic

---

**Last Updated:** Dec 1, 2025
**Database Version:** 23 business tables + 6 system tables
**Status:** ✅ VERIFIED & DOCUMENTED
