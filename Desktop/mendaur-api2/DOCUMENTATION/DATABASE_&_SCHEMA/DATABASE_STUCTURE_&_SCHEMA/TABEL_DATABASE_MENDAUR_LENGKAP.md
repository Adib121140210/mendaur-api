# 📊 DAFTAR TABEL DATABASE MENDAUR - YANG BENAR-BENAR ADA

**Update**: November 29, 2025  
**Total Tabel**: 20 (sudah verified)  
**Total Relationship**: 27+ dengan FK constraints

---

## ✅ 20 TABEL YANG SEBENARNYA ADA

### **DOMAIN 1: USER & AUTHENTICATION (7 tabel)**

| No | Tabel | FK | Relasi | Constraint | Keterangan |
|----|----|---|----|---|---|
| 1 | **ROLES** | - | 1:M ke ROLE_PERMISSIONS | - | Tabel lookup roles (nasabah=1, admin=2, superadmin=3) |
| 2 | **ROLE_PERMISSIONS** | role_id | M:1 ke ROLES | CASCADE DELETE | Permission per role |
| 3 | **USERS** | role_id | M:1 ke ROLES | RESTRICT | Core user table - NO NASABAH_DETAILS |
| 4 | **SESSIONS** | user_id | M:1 ke USERS | CASCADE DELETE | User sessions/login |
| 5 | **NOTIFIKASI** | user_id | M:1 ke USERS | CASCADE DELETE | User notifications |
| 6 | **LOG_AKTIVITAS** | user_id | M:1 ke USERS | CASCADE DELETE | User activity logs |
| 7 | **AUDIT_LOGS** | user_id | M:1 ke USERS | CASCADE DELETE | Admin action logs |

### **DOMAIN 2: WASTE MANAGEMENT (4 tabel)**

| No | Tabel | FK | Relasi | Constraint | Keterangan |
|----|----|---|----|---|---|
| 8 | **KATEGORI_SAMPAH** | - | 1:M ke JENIS_SAMPAH | - | Categories (Plastik, Organik, etc) |
| 9 | **JENIS_SAMPAH** | kategori_sampah_id | M:1 ke KATEGORI | RESTRICT | Waste types dengan harga/kg |
| 10 | **JADWAL_PENYETORAN** | - | 1:M ke TABUNG_SAMPAH | - | Deposit schedules |
| 11 | **TABUNG_SAMPAH** | user_id, jenis_sampah_id, jadwal_id | M:1 ke USERS, JENIS_SAMPAH, JADWAL | CASCADE (user), SET NULL (jenis, jadwal) | User waste deposits |

### **DOMAIN 3: POINTS & AUDIT TRAIL (2 tabel)**

| No | Tabel | FK | Relasi | Constraint | Keterangan |
|----|----|---|----|---|---|
| 12 | **POIN_TRANSAKSIS** | user_id, tabung_sampah_id | M:1 ke USERS, TABUNG | CASCADE (user), SET NULL (tabung) | Point transactions (audit trail) |
| 13 | **POIN_LEDGER** | - | - | - | Point ledger history |

### **DOMAIN 4: PRODUCTS & REDEMPTION (5 tabel)**

| No | Tabel | FK | Relasi | Constraint | Keterangan |
|----|----|---|----|---|---|
| 14 | **KATEGORI_TRANSAKSI** | - | 1:M ke TRANSAKSIS | - | Transaction types |
| 15 | **PRODUKS** | - | 1:M ke PENUKARAN_PRODUK | - | Product catalog |
| 16 | **PENUKARAN_PRODUK** | user_id, produk_id | M:1 ke USERS, PRODUKS | CASCADE | Point → Product redemptions |
| 17 | **PENUKARAN_PRODUK_DETAIL** | penukaran_produk_id, produk_id | M:1 ke PENUKARAN, PRODUKS | CASCADE | Redemption line items |
| 18 | **TRANSAKSIS** | user_id, produk_id, kategori_id | M:1 ke USERS, PRODUKS, KATEGORI_TRANSAKSI | CASCADE | General transactions |

### **DOMAIN 5: CASH MANAGEMENT (2 tabel)**

| No | Tabel | FK | Relasi | Constraint | Keterangan |
|----|----|---|----|---|---|
| 19 | **BANK_ACCOUNTS** | - | 1:M ke PENARIKAN_TUNAI | - | Bank account info |
| 20 | **PENARIKAN_TUNAI** | user_id, processed_by, bank_id | M:1 ke USERS | CASCADE (user), SET NULL (processed_by, bank) | Cash withdrawal requests |

### **DOMAIN 6: GAMIFICATION (3 tabel)**

| No | Tabel | FK | Relasi | Constraint | Keterangan |
|----|----|---|----|---|---|
| 21 | **BADGES** | - | M:M ke USERS (via USER_BADGES) | - | Badge definitions |
| 22 | **USER_BADGES** | user_id, badge_id | M:M junction | CASCADE | User badges earned |
| 23 | **BADGE_PROGRESS** | user_id, badge_id | M:1 ke USERS, BADGES | CASCADE | Badge achievement progress |

---

## ❌ TABEL YANG TIDAK ADA (JANGAN GUNAKAN)

- ❌ **NASABAH_DETAILS** - TIDAK ADA! Data nasabah ada di kolom USERS (tipe_nasabah, nama_bank, nomor_rekening, dll)
- ❌ **ASSET_UPLOADS** - TIDAK ADA di database ini
- ❌ **ARTIKEL** - TIDAK ADA di dokumen ERD yang latest
- ❌ **BANNERS** - TIDAK ADA di dokumen ERD yang latest
- ❌ **WASTE_CATEGORIES** - SALAH! Nama yang benar adalah **KATEGORI_SAMPAH**
- ❌ **WASTE_TYPES** - SALAH! Nama yang benar adalah **JENIS_SAMPAH**

---

## 📋 27 FOREIGN KEY RELATIONSHIPS (YANG BENAR)

```
1.  ROLES (1:M) ──CASCADE DELETE──> ROLE_PERMISSIONS
2.  ROLES (1:M) ──RESTRICT──> USERS
3.  USERS (1:M) ──CASCADE DELETE──> SESSIONS
4.  USERS (1:M) ──CASCADE DELETE──> NOTIFIKASI
5.  USERS (1:M) ──CASCADE DELETE──> LOG_AKTIVITAS
6.  USERS (1:M) ──CASCADE DELETE──> AUDIT_LOGS
7.  USERS (1:M) ──CASCADE DELETE──> TABUNG_SAMPAH
8.  USERS (1:M) ──CASCADE DELETE──> POIN_TRANSAKSIS
9.  USERS (1:M) ──CASCADE DELETE──> PENUKARAN_PRODUK
10. USERS (1:M) ──CASCADE DELETE──> TRANSAKSIS
11. USERS (1:M) ──CASCADE DELETE──> PENARIKAN_TUNAI
12. USERS (1:M) ──CASCADE DELETE──> USER_BADGES
13. USERS (1:M) ──CASCADE DELETE──> BADGE_PROGRESS
14. USERS (1:M) ──SET NULL──> (penarikan_tunai.processed_by)
15. KATEGORI_SAMPAH (1:M) ──RESTRICT──> JENIS_SAMPAH
16. JENIS_SAMPAH (M:1) ──SET NULL──> TABUNG_SAMPAH
17. KATEGORI_SAMPAH (M:1) ──SET NULL──> TABUNG_SAMPAH
18. JADWAL_PENYETORAN (M:1) ──SET NULL──> TABUNG_SAMPAH
19. TABUNG_SAMPAH (M:1) ──SET NULL──> POIN_TRANSAKSIS
20. KATEGORI_TRANSAKSI (1:M) ──RESTRICT──> TRANSAKSIS
21. TRANSAKSIS (M:1) ──CASCADE DELETE──> USERS (updated)
22. PRODUKS (1:M) ──CASCADE DELETE──> PENUKARAN_PRODUK
23. PRODUKS (1:M) ──CASCADE DELETE──> PENUKARAN_PRODUK_DETAIL
24. PENUKARAN_PRODUK (1:M) ──CASCADE DELETE──> PENUKARAN_PRODUK_DETAIL
25. PENUKARAN_PRODUK_DETAIL (M:1) ──RESTRICT──> PRODUKS
26. BADGES (M:M) ──CASCADE DELETE──> USER_BADGES
27. BADGES (1:M) ──CASCADE DELETE──> BADGE_PROGRESS
28. BANK_ACCOUNTS (M:1) ──SET NULL──> PENARIKAN_TUNAI
```

---

## 🎨 GROUPING BY DOMAIN & WARNA

### 🔵 BLUE - User Management (7 tabel)
- ROLES
- ROLE_PERMISSIONS  
- USERS
- SESSIONS
- NOTIFIKASI
- LOG_AKTIVITAS
- AUDIT_LOGS

### 🟢 GREEN - Waste System (4 tabel)
- KATEGORI_SAMPAH
- JENIS_SAMPAH
- JADWAL_PENYETORAN
- TABUNG_SAMPAH

### 🟡 YELLOW - Products & Cash (7 tabel)
- KATEGORI_TRANSAKSI
- PRODUKS
- PENUKARAN_PRODUK
- PENUKARAN_PRODUK_DETAIL
- TRANSAKSIS
- BANK_ACCOUNTS
- PENARIKAN_TUNAI

### 🟣 PURPLE - Gamification (3 tabel)
- BADGES
- USER_BADGES
- BADGE_PROGRESS

### ⚫ GRAY - Audit Trail (2 tabel)
- POIN_TRANSAKSIS
- POIN_LEDGER

---

## 📌 KOLOM PENTING DI USERS (bukan tabel terpisah)

```sql
-- Kolom yang ada di USERS table:
┌──────────────────────────────────────────────┐
│ USERS TABLE                                  │
├──────────────────────────────────────────────┤
│ id                    BIGINT (PK)             │
│ nama                  VARCHAR                 │
│ email                 VARCHAR (UNIQUE)        │
│ no_hp                 VARCHAR (UNIQUE)        │
│ password              VARCHAR                 │
│ alamat                TEXT                    │
│ foto_profil           VARCHAR                 │
│ total_poin            INT DEFAULT 0           │
│ total_setor_sampah    INT DEFAULT 0           │
│ level                 VARCHAR DEFAULT Pemula  │
│ role_id               BIGINT (FK → ROLES.id) │
│ tipe_nasabah          ENUM(konvensional/modern)
│ poin_tercatat         INT (untuk badges)      │
│ nama_bank             VARCHAR (modern only)   │
│ nomor_rekening        VARCHAR (modern only)   │
│ atas_nama_rekening    VARCHAR (modern only)   │
│ created_at            TIMESTAMP               │
│ updated_at            TIMESTAMP               │
└──────────────────────────────────────────────┘

✅ JADI TIDAK PERLU TABEL NASABAH_DETAILS!
   Semua data sudah ada di USERS.
```

---

## 🎯 URUTAN PEMBUATAN ERD (5 FASE - CORRECTED)

### **FASE 1** (5 min) - FOUNDATION
```
ROLES ←──FK──── USERS
           (role_id, RESTRICT)
           
Color: BLUE
Position: CENTER
```

### **FASE 2** (15 min) - WASTE SYSTEM
```
KATEGORI_SAMPAH ←──1:M───┐
                         │
JENIS_SAMPAH ←──FK───┐   │
                     │   │
JADWAL_PENYETORAN    │   │
         │           │   │
         └─→ TABUNG_SAMPAH ←── USERS
                (FK SET NULL)
                
Color: GREEN
Position: LEFT
```

### **FASE 3** (10 min) - AUTHENTICATION & LOGGING
```
USERS ─→ SESSIONS
      ─→ NOTIFIKASI
      ─→ LOG_AKTIVITAS
      ─→ AUDIT_LOGS
      
(All CASCADE DELETE)
Color: BLUE
Position: CENTER-RIGHT
```

### **FASE 4** (15 min) - PRODUCTS & REDEMPTIONS
```
PRODUKS ─→ PENUKARAN_PRODUK ─→ PENUKARAN_PRODUK_DETAIL
   │                (FK)              (FK, RESTRICT)
   └────────────────────→ FK ke PRODUKS

KATEGORI_TRANSAKSI ─→ TRANSAKSIS ←── USERS (FK)

Color: YELLOW
Position: TOP-RIGHT
```

### **FASE 5** (18 min) - POINTS, GAMIFICATION, CASH
```
TABUNG_SAMPAH → POIN_TRANSAKSIS ← USERS
                POIN_LEDGER

BADGES ←─M:M─→ USER_BADGES ← USERS
  ↓ (1:M)
BADGE_PROGRESS ← USERS

BANK_ACCOUNTS → PENARIKAN_TUNAI ← USERS

Color: GRAY (points), PURPLE (badges), YELLOW (cash)
Position: CENTER-BOTTOM
```

---

## ✅ CHECKLIST SEBELUM MENGGAMBAR

- [ ] Lihat file `ERD_RELATIONSHIP_LIST_DAN_URUTAN_PEMBUATAN.md` (untuk semua details)
- [ ] Baca file `FK_CONSTRAINTS_DETAILED_EXPLANATION.md` (untuk mengerti constraint types)
- [ ] Verifikasi 20 tabel sudah dilihat (di atas)
- [ ] Pahami 27+ relationships (di atas)
- [ ] Pahami warna grouping per domain
- [ ] Siap gunakan tools: Draw.io, DbDesigner, atau MySQL Workbench

---

## 🚀 LANGKAH SELANJUTNYA

1. **Buka Draw.io atau DbDesigner**
2. **Ikuti 5 FASE** dalam urutan di atas
3. **Gunakan warna konsisten** per domain
4. **Labeli setiap FK** dengan constraint type (CASCADE, SET NULL, RESTRICT)
5. **Mark cardinality** (1, M) di setiap relationship
6. **Export sebagai PNG 300 DPI** untuk academic report

**Estimasi total waktu**: ~60 menit

---

**Last Updated**: November 29, 2025  
**Status**: ✅ CORRECTED - NASABAH_DETAILS removed, actual tables listed  
**By**: GitHub Copilot
