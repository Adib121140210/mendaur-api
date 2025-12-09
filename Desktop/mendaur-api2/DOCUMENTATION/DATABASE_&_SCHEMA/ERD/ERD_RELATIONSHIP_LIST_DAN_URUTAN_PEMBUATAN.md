# 📊 DAFTAR LENGKAP RELATIONSHIP TABEL & URUTAN PEMBUATAN ERD

**File Sumber**: DATABASE_ERD_VISUAL_DETAILED.md  
**Tanggal**: November 29, 2025  
**Tujuan**: Panduan pembuatan ERD bertahap dengan susunan rapi

---

## 📋 DAFTAR SEMUA RELATIONSHIP TABEL

### 1️⃣ RELATIONSHIP LIST (Semua Koneksi)

```
Total Relationships: 25+ relationships
Total Tables: 20 tables

FORMAT: Source Table --[FK Type]--> Target Table
        (Foreign Key Type: CASCADE DELETE, SET NULL, or RESTRICT)
```

#### **A. USER-CENTERED RELATIONSHIPS (9 relationships)**

| # | Source Table | Target Table | FK Field | Constraint Type | Cardinality | Notes |
|---|---|---|---|---|---|---|
| 1 | nasabah_details | users | user_id | CASCADE DELETE | 1:1 | Personal details |
| 2 | tabung_sampah | users | user_id | CASCADE DELETE | 1:M | User deposits waste |
| 3 | poin_transaksis | users | user_id | CASCADE DELETE | 1:M | Point transactions |
| 4 | penukaran_produk | users | user_id | CASCADE DELETE | 1:M | Product redemption |
| 5 | penarikan_tunai | users | user_id | CASCADE DELETE | 1:M | Cash withdrawal |
| 6 | notifikasi | users | user_id | CASCADE DELETE | 1:M | User notifications |
| 7 | log_aktivitas | users | user_id | CASCADE DELETE | 1:M | Activity logging |
| 8 | user_badges | users | user_id | CASCADE DELETE | M:M | Earned badges |
| 9 | badge_progress | users | user_id | CASCADE DELETE | 1:M | Badge tracking |

#### **B. WASTE MANAGEMENT RELATIONSHIPS (4 relationships)**

| # | Source Table | Target Table | FK Field | Constraint Type | Cardinality | Notes |
|---|---|---|---|---|---|---|
| 10 | tabung_sampah | waste_categories | waste_category_id | SET NULL | M:1 | Category reference |
| 11 | tabung_sampah | waste_types | waste_type_id | SET NULL | M:1 | Waste type detail |
| 12 | poin_transaksis | tabung_sampah | tabung_sampah_id | SET NULL | M:1 | Deposit reference |
| 13 | waste_types | waste_categories | waste_category_id | RESTRICT | M:1 | Type belongs to category |

#### **C. PRODUCT & REDEMPTION RELATIONSHIPS (4 relationships)**

| # | Source Table | Target Table | FK Field | Constraint Type | Cardinality | Notes |
|---|---|---|---|---|---|---|
| 14 | penukaran_produk | products | product_id | SET NULL | M:1 | Product redeemed |
| 15 | penukaran_produk_detail | penukaran_produk | penukaran_id | CASCADE DELETE | 1:M | Redemption items |
| 16 | penukaran_produk_detail | products | product_id | RESTRICT | M:1 | Item reference |
| 17 | products | asset_uploads | gambar_id | SET NULL | M:1 | Product image |

#### **D. BADGE & GAMIFICATION RELATIONSHIPS (3 relationships)**

| # | Source Table | Target Table | FK Field | Constraint Type | Cardinality | Notes |
|---|---|---|---|---|---|---|
| 18 | user_badges | badges | badge_id | CASCADE DELETE | M:M | Badge achievement |
| 19 | badge_progress | badges | badge_id | CASCADE DELETE | 1:M | Progress tracking |
| 20 | user_badges | badges | badge_id | CASCADE DELETE | M:1 | Earned badge detail |

#### **E. CASH WITHDRAWAL RELATIONSHIPS (2 relationships)**

| # | Source Table | Target Table | FK Field | Constraint Type | Cardinality | Notes |
|---|---|---|---|---|---|---|
| 21 | penarikan_tunai | bank_accounts | bank_account_id | SET NULL | M:1 | Bank destination |
| 22 | poin_transaksis | penarikan_tunai | withdrawal_id | SET NULL | M:1 | Audit trail |

#### **F. CONTENT & SYSTEM RELATIONSHIPS (3+ relationships)**

| # | Source Table | Target Table | FK Field | Constraint Type | Cardinality | Notes |
|---|---|---|---|---|---|---|
| 23 | artikel | asset_uploads | foto_cover_id | SET NULL | M:1 | Article cover |
| 24 | banners | asset_uploads | image_id | SET NULL | M:1 | Banner image |
| 25 | admin_activity_logs | users | admin_id | CASCADE DELETE | 1:M | Audit trail |

---

## 🎯 URUTAN PEMBUATAN ERD YANG OPTIMAL

### **Filosofi Pembuatan**: Layer-by-Layer, Dari Central ke Peripheral

```
PRINSIP:
1. Mulai dari tabel PUSAT (users)
2. Tambah tabel DEPENDENT langsung
3. Lanjut ke GROUP yang terpisah
4. Hubungkan antar-group dengan hati-hati
5. Akhiri dengan SUPPORT tables
```

---

## 📐 URUTAN REKOMENDASI (5 FASE)

### **FASE 1: FOUNDATION LAYER (Tabel Inti)**
**Tujuan**: Buat struktur dasar sistem  
**Waktu**: 10 menit  
**Tampilan**: Rapi, jelas, terfokus

#### Tabel yang dibuat:
1. **USERS** (pusat, warna BIRU)
   - Role hierarchy: nasabah (1), admin (2), superadmin (3)
   - All systems revolve around this

2. **NASABAH_DETAILS** (detail user)
   - 1:1 relationship dengan users
   - Simpan di KANAN atas users

#### Output Fase 1:
```
┌─────────────┐
│    USERS    │
│  (PK: id)   │
└──────┬──────┘
       │ 1:1
       │
       ▼
┌──────────────────┐
│ NASABAH_DETAILS  │
│   (FK: user_id)  │
└──────────────────┘
```

---

### **FASE 2: WASTE MANAGEMENT GROUP (Kelompok Sampah)**
**Tujuan**: Lengkapi sistem pengelolaan sampah  
**Waktu**: 10 menit  
**Tampilan**: Hierarki jelas dari top ke bottom

#### Tabel yang ditambah:
1. **WASTE_CATEGORIES** (kategori sampah - INDEPENDENT)
   - Tidak ada FK keluar
   - Letakkan di ATAS

2. **WASTE_TYPES** (jenis sampah)
   - FK → waste_categories
   - Letakkan di BAWAH waste_categories

3. **TABUNG_SAMPAH** (deposit sampah)
   - FK → users, waste_categories, waste_types
   - Letakkan di KANAN (tengah-bawah)

#### Output Fase 2:
```
┌──────────────────┐
│ WASTE_CATEGORIES │ (Independent)
└────────┬─────────┘
         │ 1:M
         │
    ┌────▼──────┐
    │WASTE_TYPES│ (Child)
    └────┬──────┘
         │ M:1
         │
    ┌────┴──────────────────┐
    │  TABUNG_SAMPAH        │ (Connects to Users)
    │ (deposit records)      │
    └───────────────────────┘
```

---

### **FASE 3: POINTS & AUDIT SYSTEM (Kelompok Poin)**
**Tujuan**: Tambah sistem point tracking & audit  
**Waktu**: 10 menit  
**Tampilan**: Trace dari users ke poin_transaksis

#### Tabel yang ditambah:
1. **POIN_TRANSAKSIS** (ledger poin - IMPORTANT)
   - FK → users, tabung_sampah (SET NULL)
   - Letakkan di BAWAH users (CENTER)
   - Ini adalah AUDIT CENTRAL

2. **POIN_LEDGER** (backup ledger)
   - Mirror dari poin_transaksis
   - Letakkan di KANAN poin_transaksis

#### Output Fase 3:
```
        ┌─────────────┐
        │    USERS    │
        └──────┬──────┘
               │ 1:M
               │
         ┌─────▼──────────┐
         │POIN_TRANSAKSIS │◄─── Central Audit
         │  (M records)   │
         └─────┬──────────┘
               │ 1:M
               │
         ┌─────▼────────────┐
         │  POIN_LEDGER     │ (Backup)
         └──────────────────┘
```

---

### **FASE 4: PRODUCT & GAMIFICATION GROUP**
**Tujuan**: Tambah sistem produk dan badge  
**Waktu**: 15 menit  
**Tampilan**: Dua sub-group terpisah yang cantik

#### SUB-GROUP A: PRODUCTS & REDEMPTION
Tabel yang ditambah:
1. **PRODUCTS** (katalog produk - INDEPENDENT)
   - Letakkan di KANAN ATAS (sejajar dengan waste_categories)

2. **ASSET_UPLOADS** (gambar/file)
   - FK references: products, artikel, banners
   - Letakkan di ATAS KANAN (shared resource)

3. **PENUKARAN_PRODUK** (redemption header)
   - FK → users, products
   - Letakkan di BAWAH products

4. **PENUKARAN_PRODUK_DETAIL** (redemption items)
   - FK → penukaran_produk, products
   - Letakkan di BAWAH penukaran_produk

#### SUB-GROUP B: BADGES & ACHIEVEMENTS
Tabel yang ditambah:
1. **BADGES** (badge definition - INDEPENDENT)
   - Letakkan di FAR RIGHT TOP

2. **USER_BADGES** (earned badges)
   - FK → users, badges (M:M junction)
   - Letakkan di BAWAH badges

3. **BADGE_PROGRESS** (tracking progress)
   - FK → users, badges
   - Letakkan di KANAN user_badges

#### Output Fase 4:
```
ATAS:
┌───────────┐  ┌─────────────────┐  ┌──────────┐
│ PRODUCTS  │  │ ASSET_UPLOADS   │  │ BADGES   │
└────┬──────┘  └────────┬────────┘  └────┬─────┘
     │ 1:M              │ 1:M             │ 1:M
     │                  │                 │
┌────▼──────────────────┴─────────────────▼────┐
│         REDEMPTION GROUP            │ BADGE GROUP
├────────────────────┬─────────────────┤
│                    │                 │
▼                    ▼                 ▼
PENUKARAN_PRODUK  USER_BADGES   BADGE_PROGRESS
      │
      ▼
PENUKARAN_PRODUK_DETAIL
```

---

### **FASE 5: NOTIFICATIONS, CASH & FINALIZATION**
**Tujuan**: Lengkapi system dengan support tables  
**Waktu**: 10 menit  
**Tampilan**: Connect everything logically

#### Tabel yang ditambah:
1. **PENARIKAN_TUNAI** (cash withdrawal)
   - FK → users, bank_accounts
   - Letakkan di BAWAH kiri (dengan poin_transaksis)

2. **BANK_ACCOUNTS** (bank destination)
   - INDEPENDENT
   - Letakkan di BAWAH KIRI

3. **NOTIFIKASI** (notifications)
   - FK → users
   - Letakkan di SAMPING users (notification panel)

4. **LOG_AKTIVITAS** (activity log)
   - FK → users
   - Letakkan di BAWAH notifikasi

5. **ADMIN_ACTIVITY_LOGS** (admin audit)
   - FK → users (admin_id)
   - Letakkan di FAR RIGHT BOTTOM

6. **ARTIKEL** (articles content)
   - FK → asset_uploads
   - Letakkan di RIGHT (dengan content)

7. **BANNERS** (promotional banners)
   - FK → asset_uploads
   - Letakkan di BAWAH artikel

#### Output Fase 5:
```
COMPLETION:

SUPPORT TABLES (RIGHT SIDE):
┌──────────────┐
│ NOTIFIKASI   │◄─── User notifications
└──────────────┘
         │
         ▼
┌──────────────────────┐
│  LOG_AKTIVITAS       │◄─── Activity tracking
└──────────────────────┘

WITHDRAWAL SYSTEM (BOTTOM LEFT):
┌──────────────┐
│BANK_ACCOUNTS │
└────────┬─────┘
         │ M:1
         │
┌────────▼──────────┐
│ PENARIKAN_TUNAI   │◄─── Cash requests
└───────────────────┘

CONTENT SYSTEM (RIGHT):
┌──────────────┐
│ ASSET_UPLOADS│
└────────┬─────┘
    ┌────▼────────┐
    │             │
   ▼             ▼
ARTIKEL       BANNERS

ADMIN AUDIT (FAR RIGHT):
┌──────────────────────┐
│ADMIN_ACTIVITY_LOGS   │◄─── Complete admin audit
└──────────────────────┘
```

---

## 📍 LAYOUT REKOMENDASI UNTUK ERD FINAL

### **Grid Layout (Optimal Visual)**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                     ERD MENDAUR - LAYOUT RECOMMENDED       ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌─────────────────────────────────────────────────────────────┐
│ TOP ROW (Core Categories)                                   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  WASTE_CATEGORIES    PRODUCTS         BADGES               │
│         ↓                 ↓               ↓                 │
│    WASTE_TYPES    ASSET_UPLOADS   (Middle space)           │
│                                                             │
│  (Left Group)      (Center-Top)     (Right Group)          │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ MIDDLE ROW (Central Hub)                                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  TABUNG_SAMPAH      ┌─────────┐      PENUKARAN_PRODUK     │
│       ↓             │  USERS  │            ↓              │
│  (Deposits)         │ (CENTER)│      USER_BADGES          │
│                     └────┬────┘            ↓              │
│                          │        BADGE_PROGRESS         │
│                          │                                │
│   PENARIKAN_TUNAI ←──────┼──────→ NOTIFIKASI             │
│       ↓                  │            ↓                   │
│  BANK_ACCOUNTS           │      LOG_AKTIVITAS            │
│                          │                                │
│                 POIN_TRANSAKSIS                           │
│                    (Audit Center)                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ BOTTOM ROW (Support & Details)                              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  NASABAH_DETAILS   PENUKARAN_DETAIL    ARTIKEL            │
│  (User Details)    (Redemption Items)  (Content)          │
│                                            ↓              │
│                                        BANNERS            │
│                                                             │
│  POIN_LEDGER   (spacer)          ADMIN_ACTIVITY_LOGS       │
│  (Backup)                        (Admin Audit Trail)       │
│                                                             │
└─────────────────────────────────────────────────────────────┘

LEGEND:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
┌────────────┐
│ BLUE TABLE │ = Primary Entity (Core table)
└────────────┘

┌────────────┐
│ GREEN TABLE│ = Junction/Bridge (connects entities)
└────────────┘

┌────────────┐
│ YELLOW TABLE│ = Support/Detail (supporting data)
└────────────┘

┌────────────┐
│ GRAY TABLE │ = Audit/Log (system records)
└────────────┘
```

---

## 🎨 WARNA CODING UNTUK CLARITY

```
GROUP COLORS (For visual separation):

🔵 BLUE GROUP (User Management)
   ├─ USERS
   ├─ NASABAH_DETAILS
   ├─ NOTIFIKASI
   ├─ LOG_AKTIVITAS
   └─ AUDIT_LOGS

🟢 GREEN GROUP (Waste Management)
   ├─ WASTE_CATEGORIES
   ├─ WASTE_TYPES
   ├─ TABUNG_SAMPAH
   └─ (poin_transaksis references this)

🟡 YELLOW GROUP (Product System)
   ├─ PRODUCTS
   ├─ ASSET_UPLOADS
   ├─ PENUKARAN_PRODUK
   ├─ PENUKARAN_PRODUK_DETAIL
   └─ PENARIKAN_TUNAI

🟣 PURPLE GROUP (Gamification)
   ├─ BADGES
   ├─ USER_BADGES
   └─ BADGE_PROGRESS

🟤 BROWN GROUP (Content & Support)
   ├─ ARTIKEL
   ├─ BANNERS
   ├─ BANK_ACCOUNTS
   ├─ POIN_TRANSAKSIS (Central Audit)
   └─ POIN_LEDGER
```

---

## 🎯 QUICK REFERENCE - STEP BY STEP

### **Draw.io or DbDesigner Implementation**

#### **Step 1: Draw Base Tables (No Relationships Yet)**
```
1. Create 20 rectangles for 20 tables
2. Position them according to grid layout above
3. DON'T draw any lines yet
4. Just arrange boxes in grid
```

#### **Step 2: Add Primary Keys & Basic Structure**
```
1. Add PK fields to each table
2. Add 3-5 sample columns per table
3. Still no relationships drawn
```

#### **Step 3: Connect PHASE 1 Relationships**
```
USERS ←→ NASABAH_DETAILS (1:1)
✓ Draw this first (simplest)
```

#### **Step 4: Connect PHASE 2 Relationships**
```
WASTE_CATEGORIES → WASTE_TYPES (1:M)
WASTE_TYPES → TABUNG_SAMPAH (M:1)
USERS → TABUNG_SAMPAH (1:M)
✓ Complete waste hierarchy
```

#### **Step 5: Connect PHASE 3 Relationships**
```
USERS → POIN_TRANSAKSIS (1:M)
TABUNG_SAMPAH → POIN_TRANSAKSIS (M:1, SET NULL)
POIN_TRANSAKSIS → POIN_LEDGER (1:M)
✓ Point audit system ready
```

#### **Step 6: Connect PHASE 4 Relationships**
```
PRODUCTS (independent)
ASSET_UPLOADS ← PRODUCTS (1:M)
USERS → PENUKARAN_PRODUK (1:M)
PENUKARAN_PRODUK → PENUKARAN_PRODUK_DETAIL (1:M)
PENUKARAN_PRODUK_DETAIL → PRODUCTS (M:1)

BADGES (independent)
USERS → USER_BADGES (M:M)
USERS → BADGE_PROGRESS (1:M)
BADGES → USER_BADGES (M:M)
BADGES → BADGE_PROGRESS (1:M)
✓ Products & Gamification complete
```

#### **Step 7: Connect PHASE 5 Relationships**
```
USERS → NOTIFIKASI (1:M)
USERS → LOG_AKTIVITAS (1:M)
USERS → ADMIN_ACTIVITY_LOGS (1:M)
USERS → PENARIKAN_TUNAI (1:M)
BANK_ACCOUNTS → PENARIKAN_TUNAI (1:M)
ASSET_UPLOADS → ARTIKEL (1:M)
ASSET_UPLOADS → BANNERS (1:M)
✓ ALL RELATIONSHIPS COMPLETE
```

#### **Step 8: Visual Polish**
```
1. Adjust line routing (no crossing if possible)
2. Resize boxes for readability
3. Add colors by group
4. Add legends/notes
5. Export as PNG/PDF
```

---

## 📊 DETAILED PHASE-BY-PHASE CHECKLIST

### **✅ FASE 1: Foundation (5 menit)**

- [ ] Create USERS table
  - Columns: id, name, email, role_id, etc.
  - Color: BLUE
  - Position: CENTER

- [ ] Create NASABAH_DETAILS table
  - FK: user_id → users.id (1:1, CASCADE DELETE)
  - Color: BLUE
  - Position: RIGHT of USERS

- [ ] Draw relationship line (1:1)

**Expected**: Simple 2-table diagram, crystal clear

---

### **✅ FASE 2: Waste Management (10 menit)**

- [ ] Create WASTE_CATEGORIES
  - No FK (independent)
  - Color: GREEN
  - Position: LEFT side, TOP

- [ ] Create WASTE_TYPES
  - FK: waste_category_id → waste_categories.id (M:1, RESTRICT)
  - Color: GREEN
  - Position: BELOW waste_categories

- [ ] Create TABUNG_SAMPAH
  - FK 1: user_id → users.id (1:M, CASCADE DELETE)
  - FK 2: waste_category_id → waste_categories.id (M:1, SET NULL)
  - FK 3: waste_type_id → waste_types.id (M:1, SET NULL)
  - Color: GREEN
  - Position: MIDDLE-LEFT, below waste_types

- [ ] Draw all relationship lines (3 lines for TABUNG_SAMPAH)

**Expected**: Waste hierarchy complete, shows tabung_sampah connects users + categories + types

---

### **✅ FASE 3: Points System (10 menit)**

- [ ] Create POIN_TRANSAKSIS
  - FK 1: user_id → users.id (1:M, CASCADE DELETE)
  - FK 2: tabung_sampah_id → tabung_sampah.id (M:1, SET NULL)
  - Color: GRAY (Audit central)
  - Position: CENTER-BOTTOM (below users)
  - **NOTE**: This is CRITICAL junction table

- [ ] Create POIN_LEDGER
  - FK: user_id → users.id (backup reference)
  - Color: GRAY
  - Position: RIGHT of poin_transaksis

- [ ] Draw relationship lines

**Expected**: Point audit system visible, polymorphic nature clear

---

### **✅ FASE 4A: Products (12 menit)**

- [ ] Create PRODUCTS
  - No FK initially (independent)
  - Color: YELLOW
  - Position: RIGHT-TOP (sejajar waste_categories)

- [ ] Create ASSET_UPLOADS
  - No FK yet (shared reference)
  - Color: YELLOW
  - Position: FAR RIGHT TOP

- [ ] Create PENUKARAN_PRODUK
  - FK 1: user_id → users.id (1:M, CASCADE DELETE)
  - FK 2: product_id → products.id (M:1, SET NULL)
  - Color: YELLOW
  - Position: BELOW products

- [ ] Create PENUKARAN_PRODUK_DETAIL
  - FK 1: penukaran_id → penukaran_produk.id (1:M, CASCADE DELETE)
  - FK 2: product_id → products.id (M:1, RESTRICT)
  - Color: YELLOW
  - Position: BELOW penukaran_produk

- [ ] Add FK to PRODUCTS: image_id → asset_uploads.id (SET NULL)

- [ ] Draw all relationship lines

**Expected**: Product redemption flow clear, from user → redemption → items

---

### **✅ FASE 4B: Gamification (12 menit)**

- [ ] Create BADGES
  - No FK (independent)
  - Color: PURPLE
  - Position: FAR RIGHT (sejajar products)

- [ ] Create USER_BADGES
  - FK 1: user_id → users.id (M:M junction, CASCADE DELETE)
  - FK 2: badge_id → badges.id (M:M junction, CASCADE DELETE)
  - Color: PURPLE
  - Position: BELOW badges

- [ ] Create BADGE_PROGRESS
  - FK 1: user_id → users.id (1:M, CASCADE DELETE)
  - FK 2: badge_id → badges.id (1:M, CASCADE DELETE)
  - Color: PURPLE
  - Position: RIGHT of user_badges

- [ ] Draw all relationship lines

**Expected**: Badge system clear, progression visible, M:M relationship evident

---

### **✅ FASE 5A: Withdrawal & Cash (8 menit)**

- [ ] Create BANK_ACCOUNTS
  - No FK (independent)
  - Color: YELLOW
  - Position: LEFT-BOTTOM

- [ ] Create PENARIKAN_TUNAI
  - FK 1: user_id → users.id (1:M, CASCADE DELETE)
  - FK 2: bank_account_id → bank_accounts.id (M:1, SET NULL)
  - Color: YELLOW
  - Position: BELOW bank_accounts

- [ ] Draw relationship lines

**Expected**: Withdrawal system isolated but connected, clear flow

---

### **✅ FASE 5B: Notifications & Audit (8 menit)**

- [ ] Create NOTIFIKASI
  - FK: user_id → users.id (1:M, CASCADE DELETE)
  - Color: BLUE
  - Position: BOTTOM-LEFT, near users

- [ ] Create LOG_AKTIVITAS
  - FK: user_id → users.id (1:M, CASCADE DELETE)
  - Color: BLUE
  - Position: BELOW notifikasi

- [ ] Create ADMIN_ACTIVITY_LOGS
  - FK: admin_id → users.id (1:M, CASCADE DELETE)
  - Color: BLUE
  - Position: FAR RIGHT-BOTTOM

- [ ] Draw relationship lines

**Expected**: All user tracking complete, audit trail visible

---

### **✅ FASE 5C: Content (5 menit)**

- [ ] Create ARTIKEL
  - FK: foto_cover_id → asset_uploads.id (M:1, SET NULL)
  - Color: BROWN
  - Position: RIGHT-MIDDLE

- [ ] Create BANNERS
  - FK: image_id → asset_uploads.id (M:1, SET NULL)
  - Color: BROWN
  - Position: BELOW artikel

- [ ] Draw relationship lines

- [ ] Update ASSET_UPLOADS FK references to article/banner tables

**Expected**: Content management system visible, image sharing clear

---

### **✅ FASE 6: Polish & Export (5 menit)**

- [ ] Verify all 25+ relationships drawn
- [ ] Check all cardinality marks (1, M, etc)
- [ ] Verify all constraint types (CASCADE, SET NULL)
- [ ] Color code by group
- [ ] Add legend/notes
- [ ] Adjust layout for minimum line crossing
- [ ] Export as high-quality PNG/PDF
- [ ] Save in project folder

**Expected**: Professional-quality ERD, all information clear

---

## 📝 TOTAL CHECKLIST - ALL 20 TABLES

```
TABEL (Urutan Pembuatan)    │ Phase │ Status │ Position
────────────────────────────┼───────┼────────┼─────────────────
1. USERS                    │  1    │ ✓      │ CENTER
2. NASABAH_DETAILS          │  1    │ ✓      │ RIGHT (top)
3. WASTE_CATEGORIES         │  2    │ ✓      │ LEFT (top)
4. WASTE_TYPES              │  2    │ ✓      │ LEFT (below cats)
5. TABUNG_SAMPAH            │  2    │ ✓      │ LEFT (middle)
6. POIN_TRANSAKSIS          │  3    │ ✓      │ CENTER (bottom)
7. POIN_LEDGER              │  3    │ ✓      │ CENTER-RIGHT
8. PRODUCTS                 │  4    │ ✓      │ RIGHT (top)
9. ASSET_UPLOADS            │  4    │ ✓      │ FAR RIGHT (top)
10. PENUKARAN_PRODUK        │  4    │ ✓      │ RIGHT (middle)
11. PENUKARAN_PRODUK_DETAIL │  4    │ ✓      │ RIGHT (middle-b)
12. BADGES                  │  4    │ ✓      │ FAR RIGHT (top)
13. USER_BADGES             │  4    │ ✓      │ FAR RIGHT (mid)
14. BADGE_PROGRESS          │  4    │ ✓      │ FAR RIGHT (mid-r)
15. BANK_ACCOUNTS           │  5    │ ✓      │ LEFT (bottom)
16. PENARIKAN_TUNAI         │  5    │ ✓      │ LEFT (bottom-mid)
17. NOTIFIKASI              │  5    │ ✓      │ CENTER-LEFT
18. LOG_AKTIVITAS           │  5    │ ✓      │ CENTER-LEFT
19. ADMIN_ACTIVITY_LOGS     │  5    │ ✓      │ FAR RIGHT (bottom)
20. ARTIKEL                 │  5    │ ✓      │ RIGHT (content)
21. BANNERS                 │  5    │ ✓      │ RIGHT (below arti)
```

---

## 🎓 TIPS UNTUK HASIL TERBAIK

### **Saat Menggambar ERD:**

1. **Gunakan Tools yang Tepat**
   - Draw.io: Mudah, intuitif, free
   - DbDesigner: Profesional, database-focused
   - Lucidchart: Bagus, tapi berbayar
   - MySQL Workbench: Free, powerful, bisa generate dari DB

2. **Cardinality Notations**
   ```
   1      = Exactly one
   M      = Many (zero or more)
   1..M   = One to many
   0..1   = Zero or one
   
   Contoh: User 1 ----< M TabungSampah
           (1 user bisa punya banyak deposits)
   ```

3. **Constraint Types di Garis**
   ```
   CASCADE DELETE    = ━━━━━ (solid line)
                       Jika parent dihapus, child ikut dihapus
   
   SET NULL          = ╌╌╌╌╌ (dashed line)
                       Jika parent dihapus, FK child jadi NULL
   
   RESTRICT          = ═════ (thick line)
                       Tidak boleh dihapus jika ada child
   ```

4. **Warna Coding**
   - Blue: User/Authentication related
   - Green: Waste management related
   - Yellow: Product/Commerce related
   - Purple: Gamification/Badge related
   - Gray: Audit/Logging related
   - Brown: Content/Support related

5. **Layout Principles**
   - Independent tables (no FK out) → TOP
   - Central hubs (many connections) → MIDDLE
   - Detail/Support tables → BOTTOM
   - Minimize line crossing
   - Group related tables together

---

## ✅ FINAL VALIDATION CHECKLIST

Sebelum export, pastikan:

- [ ] Semua 20 tabel ada
- [ ] Semua 25+ relationships ada
- [ ] Tidak ada FK yang "floating" (pointing nowhere)
- [ ] Semua cardinality marks benar
- [ ] Constraint types jelas (CASCADE, SET NULL, RESTRICT)
- [ ] Warna coding konsisten
- [ ] Layout terorganisir dan rapi
- [ ] Legend/notes present
- [ ] No overlapping boxes
- [ ] Font readable (size 10-12pt minimum)

---

**Created**: November 29, 2025  
**For**: Pembuatan ERD Bertahap Mendaur API  
**Total Tables**: 20  
**Total Relationships**: 25+  
**Recommended Time**: 1 jam (5 fase @ ~10-15 menit each)  
**Output**: Professional ERD ready for academic report
