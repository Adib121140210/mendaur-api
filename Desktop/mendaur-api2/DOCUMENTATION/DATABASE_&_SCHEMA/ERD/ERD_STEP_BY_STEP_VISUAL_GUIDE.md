# 🎨 VISUAL GUIDE - STEP-BY-STEP ERD DRAWING

**Panduan Visual Pembuatan ERD Bertahap**  
**Format**: Diagram ASCII untuk preview sebelum drawing di tools

---

## 📐 FASE 1 - FOUNDATION (5 menit)

### Hasil Akhir Fase 1:
```
                       ┌──────────────┐
                       │    USERS     │
                       │ (PK: id)     │
                       │ • id         │
                       │ • name       │
                       │ • email      │
                       │ • role_id    │
                       │ • total_poin │
                       └────────┬─────┘
                                │ 1:1
                                │ CASCADE DELETE
                                │
                       ┌────────▼──────────────────┐
                       │ NASABAH_DETAILS          │
                       │ (PK: id)                 │
                       │ • id                     │
                       │ • user_id (FK)           │
                       │ • tipe_nasabah           │
                       │ • alamat                 │
                       └──────────────────────────┘

Position: CENTER
Status: Paling dasar, semua tabel lain refer ke sini
```

---

## 📐 FASE 2 - WASTE MANAGEMENT (10 menit)

### Hasil Akhir Fase 2:
```
┌──────────────────┐
│ WASTE_CATEGORIES │  ← Independent (no FK out)
│ (PK: id)         │
│ • id             │
│ • nama           │
│ • deskripsi      │
└────────┬─────────┘
         │ 1:M
         │ RESTRICT
         │
    ┌────▼──────────────────┐
    │   WASTE_TYPES         │
    │  (PK: id)             │
    │  • id                 │
    │  • waste_category_id  │
    │  • nama_jenis         │
    │  • harga_per_unit     │
    └────────┬──────────────┘
             │ M:1
             │ SET NULL
             │
             ├──────────────────────────┐
             │                          │
             │                  ┌───────▼───────────────────────┐
             │                  │     USERS                     │
             │                  │    (from Phase 1)             │
             │                  └──────────────────────────────┘
             │                          ▲
             │                          │
             │                  1:M     │
             │           CASCADE DELETE │
             │                          │
    ┌────────▼────────────────────────────┘
    │   TABUNG_SAMPAH (Deposits)
    │  (PK: id)
    │  • id
    │  • user_id (FK) ──────────┐
    │  • waste_type_id (FK) ────┘
    │  • waste_category_id (FK) (SET NULL)
    │  • berat_kg
    │  • status
    │  • created_at
    └─────────────────────────────────────

Position: LEFT SIDE
Status: Waste hierarchy complete
Total connections: 3 FK (users, categories, types)
```

---

## 📐 FASE 3 - POINTS AUDIT SYSTEM (10 menit)

### Hasil Akhir Fase 3:
```
                       ┌──────────────┐
                       │    USERS     │ (From Phase 1)
                       └────────┬─────┘
                                │ 1:M
                                │ CASCADE DELETE
                                │
                    ┌───────────▼──────────────┐
                    │ POIN_TRANSAKSIS          │ ← CENTRAL AUDIT
                    │ (PK: id)                 │
                    │ • id                     │
                    │ • user_id (FK) ────────┐ │
                    │ • tabung_sampah_id (FK) │ │ SET NULL
                    │ • jenis_sampah          │ │
                    │ • poin_didapat          │ │
                    │ • sumber                │ │
                    │ • referensi_id          │ │
                    │ • referensi_tipe        │ │
                    │ • created_at            │ │
                    └───────────┬──────────────┘ │
                                │                │
                  ┌─────────────▼────────────────┘
                  │
        ┌─────────▼──────────────────────┐
        │    TABUNG_SAMPAH               │ (From Phase 2)
        │  (M:1 ref from POIN_TRANSAKSIS)│
        │  SET NULL means poin stays     │
        │  even if deposit deleted       │
        └────────────────────────────────┘

        M:1 relationship allows:
        • Multiple poin records → Same deposit
        • But each poin references ONE deposit (or null)

        ┌──────────────────┐
        │  POIN_LEDGER     │ ← Backup/Mirror
        │  (PK: id)        │
        │  • id            │
        │  • user_id (FK)  │
        │  • poin_amount   │
        │  • tanggal       │
        └──────────────────┘

Position: CENTER-BOTTOM
Status: Point audit system central hub
Cardinality: 1 user → M poin records
Polymorphic: referensi_id + referensi_tipe points to various sources
```

---

## 📐 FASE 4A - PRODUCTS SYSTEM (12 menit)

### Hasil Akhir Fase 4A:
```
┌──────────────────────┐
│  ASSET_UPLOADS       │ ← Shared resource
│  (PK: id)            │
│  • id                │
│  • file_path         │
│  • file_type         │
│  • created_at        │
└────────┬─────────────┘
         │ 1:M
         │ SET NULL
         │
    ┌────▼────────────────────────┐
    │   PRODUCTS                  │
    │  (PK: id)                   │
    │  • id                       │
    │  • nama_produk              │
    │  • harga_poin               │
    │  • stok_tersedia            │
    │  • gambar_id (FK) ◄─────────┘ (SET NULL)
    │  • deskripsi                │
    │  • created_at               │
    └────────┬───────────┬────────┘
             │           │
             │ 1:M       │ M:1
             │           │ CASCADE DELETE
             │           │
    ┌────────▼─────────┐ └────────┐
    │ PENUKARAN_PRODUK │          │
    │ (Redemption HDR) │          │
    │ (PK: id)         │          │ (To USERS)
    │ • id             │          │
    │ • user_id (FK)   │──────────┘
    │ • product_id (FK)  (M:1, SET NULL)
    │ • tanggal_tukar    │
    │ • status           │
    └────────┬──────────┘
             │ 1:M
             │ CASCADE DELETE
             │
    ┌────────▼────────────────────────┐
    │ PENUKARAN_PRODUK_DETAIL         │
    │ (Redemption Items - Junction)   │
    │ (PK: id)                        │
    │ • id                            │
    │ • penukaran_id (FK) ────────────┘
    │ • product_id (FK) (M:1, RESTRICT)
    │ • qty                           │
    │ • harga_poin_satuan             │
    └─────────────────────────────────┘

Position: RIGHT SIDE (TOP to MIDDLE)
Status: Product redemption flow complete
Key concept: M:M relationship between USERS and PRODUCTS
             via junction table PENUKARAN_PRODUK_DETAIL
```

---

## 📐 FASE 4B - GAMIFICATION SYSTEM (12 menit)

### Hasil Akhir Fase 4B:
```
┌─────────────────────────────────┐
│     BADGES                      │ ← Badge definitions
│    (PK: id)                     │
│    • id                         │
│    • nama_badge                 │
│    • deskripsi                  │
│    • syarat_poin                │
│    • syarat_setor               │
│    • reward_poin                │
│    • gambar (reference?)        │
│    • tipe                       │
│    • created_at                 │
└──────┬──────────────┬───────────┘
       │ M:M junction │ 1:M
       │ CASCADE      │ CASCADE
       │              │
   ┌───▼──────────────▼──────────┐
   │   USER_BADGES               │
   │  (M:M Junction Table)        │
   │  (PK: id)                    │
   │  • id                        │
   │  • user_id (FK) ──┐          │
   │  • badge_id (FK) ─┘          │
   │  • tanggal_dapat             │
   │  • reward_claimed            │
   │  • created_at                │
   └────────────┬────────────────┘
                │ (USERS reference)
                │
   ┌────────────▼──────────────┐
   │   BADGE_PROGRESS          │ ← Progress tracking
   │  (PK: id)                 │
   │  • id                     │
   │  • user_id (FK) ─────────┐│
   │  • badge_id (FK) ────────┼─→ to BADGES
   │  • current_value         │ │
   │  • target_value          │ │
   │  • progress_percentage   │ │
   │  • is_unlocked           │ │
   │  • unlocked_at           │ │
   │  • created_at            │ │
   │  • updated_at            │ │
   └─────────────────────────┬┴─┘
                             │
                             └──→ Triggers USER_BADGES creation
                                  when is_unlocked = TRUE

Position: FAR RIGHT (TOP to MIDDLE)
Status: Complete gamification system
Key concept: 1:M progress tracking for each badge
             M:M earned badges via junction
             When progress = 100% → create user_badges record
```

---

## 📐 FASE 5A - WITHDRAWAL SYSTEM (8 menit)

### Hasil Akhir Fase 5A:
```
┌──────────────────────────┐
│  BANK_ACCOUNTS           │ ← Independent
│  (PK: id)                │
│  • id                    │
│  • nama_bank             │
│  • nomor_rekening        │
│  • atas_nama             │
│  • created_at            │
└────────┬─────────────────┘
         │ 1:M
         │ SET NULL
         │
    ┌────▼────────────────────────┐
    │  PENARIKAN_TUNAI             │
    │  (Cash Withdrawal Requests)  │
    │  (PK: id)                    │
    │  • id                        │
    │  • user_id (FK) ─────────┐   │
    │  • bank_account_id (FK) ─┤   │
    │  • jumlah_poin           │   │
    │  • jumlah_rupiah         │   │
    │  • status                │   │
    │  • tanggal_request       │   │
    │  • tanggal_diproses      │   │
    │  • created_at            │   │
    └──────────────┬───────────┘   │
                   │               │
                   └──→ (to USERS) │
                   └──→ (to BANK_ACCOUNTS)

Position: LEFT SIDE (BOTTOM)
Status: Withdrawal system isolated
Key flow: User requests → Bank destination assigned → Processed
CASCADE DELETE on user: deletes all pending withdrawals
SET NULL on bank: keeps record even if bank deleted
```

---

## 📐 FASE 5B - NOTIFICATIONS & AUDIT (8 menit)

### Hasil Akhir Fase 5B:
```
                    ┌─────────────────┐
                    │     USERS       │ (From Phase 1)
                    └────────┬────────┘
                             │ 1:M CASCADE DELETE
                             │
        ┌────────────────────┼────────────────┐
        │                    │                │
   ┌────▼──────────────┐ ┌──▼────────────┐ ┌─▼───────────────────┐
   │ NOTIFIKASI       │ │LOG_AKTIVITAS  │ │ADMIN_ACTIVITY_LOGS  │
   │ (PK: id)         │ │ (PK: id)      │ │ (PK: id)            │
   │ • id             │ │ • id          │ │ • id                │
   │ • user_id (FK)   │ │ • user_id(FK) │ │ • admin_id (FK)     │
   │ • judul          │ │ • tipe_akti   │ │ • action_type       │
   │ • pesan          │ │ • deskripsi   │ │ • resource_type     │
   │ • tipe           │ │ • poin_ubah   │ │ • resource_id       │
   │ • is_read        │ │ • tanggal     │ │ • old_values (JSON) │
   │ • created_at     │ │ • created_at  │ │ • new_values (JSON) │
   │                  │ │               │ │ • ip_address        │
   └──────────────────┘ └───────────────┘ │ • user_agent        │
                                          │ • status            │
                                          │ • created_at        │
                                          └─────────────────────┘

   NOTIFIKASI:      Real-time user messages
                    When: User actions trigger notifications

   LOG_AKTIVITAS:   User behavior tracking
                    When: Every significant user action

   ADMIN_LOGS:      Admin action audit trail (COMPLIANCE)
                    When: Admin approves/rejects anything
                    WHY: For governance & auditing

Position: CENTER-LEFT (NOTIFIKASI/LOG) and FAR RIGHT (ADMIN_LOGS)
Status: Complete notification & audit systems
Key point: ADMIN_LOGS is immutable (for compliance)
```

---

## 📐 FASE 5C - CONTENT MANAGEMENT (5 menit)

### Hasil Akhir Fase 5C:
```
┌──────────────────────────┐
│  ASSET_UPLOADS           │ (Shared from Phase 4)
│  (PK: id)                │
│  • id                    │
│  • file_path             │
│  • file_type             │
│  • created_at            │
└──────┬──────────────────┬┘
       │ 1:M              │ 1:M
       │ SET NULL         │ SET NULL
       │                  │
   ┌───▼──────────────┐  ┌▼──────────────┐
   │  ARTIKEL         │  │  BANNERS      │
   │  (PK: id)        │  │  (PK: id)     │
   │  • id            │  │  • id         │
   │  • judul         │  │  • title      │
   │  • slug          │  │  • image_id   │
   │  • konten        │  │  • target_url │
   │  • foto_cover_id │  │  • is_active  │
   │  • penulis       │  │  • created_at │
   │  • kategori      │  │               │
   │  • views         │  └───────────────┘
   │  • created_at    │
   └──────────────────┘

   SLUG EXPLANATION:
   • Human: "Tips Menabung Poin"
   • Slug:  "tips-menabung-poin" (URL-friendly)
   • Used in URLs like: /artikel/tips-menabung-poin

Position: RIGHT SIDE (CONTENT AREA)
Status: Content management system complete
Key point: ASSET_UPLOADS shared resource for articles, banners, products
           One image can be referenced by multiple content items
```

---

## 🎯 FULL DIAGRAM - ALL PHASES COMBINED

### Final Complete ERD Layout:
```
╔════════════════════════════════════════════════════════════════════╗
║                    MENDAUR API - COMPLETE ERD                     ║
╚════════════════════════════════════════════════════════════════════╝

TOP ROW (Independent & Group Leaders):
┌─────────────────┐  ┌──────────────────┐  ┌────────────┐  ┌────────────┐
│ WASTE_CATEGORIES│  │  PRODUCTS        │  │ BADGES     │  │ASSET_UPL...│
└────────┬────────┘  └────────┬─────────┘  └────┬───────┘  └─────┬──────┘
         │                    │                 │                │
         ▼                    ▼                 ▼                ▼
    WASTE_TYPES        (connections          USER_BADGES    ARTIKEL
         │              below)                   │            │
         │                                       ▼            ▼
         ▼                                   BADGE_PROG...   BANNERS
    TABUNG_SAMPAH
         │


MIDDLE ROW (Central Hub & Main Flows):
         ┌────────────────────────────────────────────────────────┐
         │                    USERS (CENTER HUB)                  │
         │                      (PK: id)                          │
         └─────────┬──────────────┬──────────────┬──────────────┬─┘
                   │              │              │              │
        ┌──────────┼──────────────┼──────────────┼──────────────┼───────┐
        │          │              │              │              │       │
        ▼          ▼              ▼              ▼              ▼       ▼
   NASABAH_   NOTIFIKASI    LOG_AKTIVITAS  PENARIKAN_   PENUKARAN_  USERS_
   DETAILS                                  TUNAI        PRODUK      BADGES


BOTTOM ROW (Supporting Tables & Details):
   BANK_         POIN_         PENUKARAN_    ADMIN_       POIN_
  ACCOUNTS    TRANSAKSIS     PRODUK_DETAIL  ACTIVITY     LEDGER
              (CENTRAL               LOGS
               AUDIT)


KEY CONNECTIONS (Relationship Flows):

User Core:
USERS ←──1:1───→ NASABAH_DETAILS

User Actions:
USERS ←──1:M───→ {NOTIFIKASI, LOG_AKTIVITAS, PENARIKAN_TUNAI, ...}

Waste Flow:
WASTE_CATEGORIES → WASTE_TYPES → TABUNG_SAMPAH ← USERS
                                       ↓
                           POIN_TRANSAKSIS (logs points earned)

Point System:
USERS ←──1:M───→ POIN_TRANSAKSIS ←──M:1───→ TABUNG_SAMPAH (source)
          │
          └──1:M───→ POIN_LEDGER

Product Redemption:
PRODUCTS ←──M:1─── PENUKARAN_PRODUK ← USERS
             ↑
             └──M:1─── PENUKARAN_PRODUK_DETAIL

Gamification:
BADGES ←──M:M(via USER_BADGES)──→ USERS
   ↑
   └──1:M─── BADGE_PROGRESS ← USERS

Cash Withdrawal:
BANK_ACCOUNTS ←──M:1─── PENARIKAN_TUNAI ← USERS

Content:
ASSET_UPLOADS → {ARTIKEL, BANNERS, PRODUCTS}
```

---

## ✅ DRAWING CHECKLIST PER FASE

### Fase 1 Checklist:
- [ ] Draw USERS box (12cm x 6cm)
- [ ] Draw NASABAH_DETAILS box (12cm x 6cm)
- [ ] Add 1:1 line with CASCADE DELETE label
- [ ] Color both BLUE

### Fase 2 Checklist:
- [ ] Draw WASTE_CATEGORIES box
- [ ] Draw WASTE_TYPES box
- [ ] Draw TABUNG_SAMPAH box
- [ ] Add 1:M line: WASTE_CATEGORIES → WASTE_TYPES (RESTRICT)
- [ ] Add M:1 line: WASTE_TYPES → TABUNG_SAMPAH (SET NULL)
- [ ] Add 1:M line: USERS → TABUNG_SAMPAH (CASCADE DELETE)
- [ ] Color all GREEN

### Fase 3 Checklist:
- [ ] Draw POIN_TRANSAKSIS box
- [ ] Draw POIN_LEDGER box
- [ ] Add 1:M line: USERS → POIN_TRANSAKSIS (CASCADE DELETE)
- [ ] Add M:1 line: TABUNG_SAMPAH → POIN_TRANSAKSIS (SET NULL)
- [ ] Add 1:M line: POIN_TRANSAKSIS → POIN_LEDGER
- [ ] Color both GRAY (Audit)

### Fase 4A Checklist:
- [ ] Draw ASSET_UPLOADS, PRODUCTS, PENUKARAN_PRODUK, DETAIL boxes
- [ ] Add 1:M line: ASSET_UPLOADS → PRODUCTS (SET NULL)
- [ ] Add 1:M line: PRODUCTS → PENUKARAN_PRODUK (SET NULL)
- [ ] Add M:1 line: PRODUCTS ← PENUKARAN_PRODUK_DETAIL (RESTRICT)
- [ ] Add 1:M line: PENUKARAN_PRODUK → DETAIL (CASCADE DELETE)
- [ ] Add 1:M line: USERS → PENUKARAN_PRODUK (CASCADE DELETE)
- [ ] Color all YELLOW

### Fase 4B Checklist:
- [ ] Draw BADGES, USER_BADGES, BADGE_PROGRESS boxes
- [ ] Add M:M lines: USERS ↔ BADGES (via USER_BADGES)
- [ ] Add 1:M line: BADGES → USER_BADGES (CASCADE DELETE)
- [ ] Add 1:M line: BADGES → BADGE_PROGRESS (CASCADE DELETE)
- [ ] Add 1:M line: USERS → BADGE_PROGRESS (CASCADE DELETE)
- [ ] Color all PURPLE

### Fase 5A Checklist:
- [ ] Draw BANK_ACCOUNTS, PENARIKAN_TUNAI boxes
- [ ] Add 1:M line: BANK_ACCOUNTS → PENARIKAN_TUNAI (SET NULL)
- [ ] Add 1:M line: USERS → PENARIKAN_TUNAI (CASCADE DELETE)
- [ ] Color YELLOW

### Fase 5B Checklist:
- [ ] Draw NOTIFIKASI, LOG_AKTIVITAS, ADMIN_ACTIVITY_LOGS boxes
- [ ] Add 1:M lines from USERS to each
- [ ] Color all BLUE

### Fase 5C Checklist:
- [ ] Draw ARTIKEL, BANNERS boxes
- [ ] Add 1:M lines: ASSET_UPLOADS → ARTIKEL (SET NULL)
- [ ] Add 1:M lines: ASSET_UPLOADS → BANNERS (SET NULL)
- [ ] Color BROWN

---

**Total Time**: ~60-75 minutes untuk semua fase  
**Result**: Professional-grade ERD ready for academic report  
**Format Export**: PNG 300 DPI for high-quality printing
