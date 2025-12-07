# 📊 Database Entity Relationship Diagram (ERD) - MENDAUR API

## Complete Visual Overview - All 20 Tables

---

## 🎯 **System Architecture Diagram**

```
┌───────────────────────────────────────────────────────────────────────────────┐
│                        MENDAUR WASTE MANAGEMENT SYSTEM                        │
│                          20 Tables | 25+ Relationships                        │
└───────────────────────────────────────────────────────────────────────────────┘

                              🔑 CENTRAL HUB
                         ┌──────────────────┐
                         │     USERS        │
                         │   (PK: id)       │
              CASCADE DELETE CHAINS:
━━━━━━━━━━━━━━━━━━━━━━━━

When users.id is deleted:
  → tabung_sampah dele│  FOREIGN KEY CONSTRAINTS:                                   │
│  • 25+ foreign keys pointing to:                            │
│    - users.id (BIGINT - 9 tables)                           │
│    - badges.id (2 tables)                                   │
│    - produks.id (2 tables)                                  │
│    - kategori_sampah.id (1 table)                           │
│    - kategori_transaksi.id (1 table)                        │
│    - jadwal_penyetoran.id (1 table)                         │
│    - tabung_sampah.id (1 table)                             │→ poin_transaksis deleted
  → penukaran_produk deleted
  → transaksis deleted
  → penarikan_tunai deleted
  → notifikasi deleted
  → log_aktivitas deleted
  → user_badges deleted
  → badge_progress deleted  ~500 records   │
                         └──────────────────┘
                                 │
                ┌────────────────┼────────────────┐
                │                │                │
                ▼                ▼                ▼
        ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
        │   DEPOSITS   │ │  REDEMPTIONS │ │ TRANSACTIONS │
        │   SYSTEM     │ │    SYSTEM    │ │    SYSTEM    │
        └──────────────┘ └──────────────┘ └──────────────┘
                │                │                │
                ▼                ▼                ▼
        ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
        │WASTE TYPES   │ │   PRODUCTS   │ │  CATEGORIES  │
        │   SYSTEM     │ │   CATALOG    │ │    SYSTEM    │
        └──────────────┘ └──────────────┘ └──────────────┘
```

---

## 📐 **Complete ERD with All Relationships**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                      ROLE-BASED ACCESS CONTROL LAYER                       ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌──────────────────────────────────────────────────────────────┐
│  🆕 ROLES TABLE (User Role Definitions)      PK: id        │
├──────────────────────────────────────────────────────────────┤
│  Purpose: Define role types and their capabilities           │
│                                                              │
│  • id              BIGINT (PK)                               │
│  • nama_role       VARCHAR(50) UNIQUE                        │
│  • deskripsi       TEXT (nullable)                           │
│  • level_akses     INT (1=nasabah, 2=admin, 3=superadmin)   │
│  • created_at      TIMESTAMP                                 │
│  • updated_at      TIMESTAMP                                 │
│                                                              │
│  Predefined Roles (SEED DATA):                              │
│  ├─ id=1, nama_role='nasabah', level_akses=1               │
│  │  └─ Regular user (deposits waste, redeems poin)          │
│  ├─ id=2, nama_role='admin', level_akses=2                 │
│  │  └─ Bank staff (approves transactions, manage users)     │
│  └─ id=3, nama_role='superadmin', level_akses=3            │
│     └─ System manager (manage admins, settings)             │
│                                                              │
│  🔗 RELATIONSHIPS:                                          │
│  ← M:1 ← users (via role_id FK)                            │
│     Each user has exactly ONE role                          │
│                                                              │
│  📌 NOTE:                                                   │
│  • Cannot delete roles with existing users                  │
│  • role_id in users table is NOT NULL with FK constraint   │
│  • Default role_id = 1 (nasabah) for new registrations     │
└──────────────────────────────────────────────────────────────┘


┌──────────────────────────────────────────────────────────────┐
│  🆕 ROLE_PERMISSIONS TABLE (Permission Definitions)         │
├──────────────────────────────────────────────────────────────┤
│  Purpose: Define what each role can do                       │
│                                                              │
│  • id              BIGINT (PK)                               │
│  • role_id         BIGINT (FK) → roles.id (CASCADE DELETE)   │
│  • permission      VARCHAR(100) (permission code)            │
│  • deskripsi       TEXT (nullable)                           │
│  • created_at      TIMESTAMP                                 │
│  • updated_at      TIMESTAMP                                 │
│                                                              │
│  UNIQUE(role_id, permission) - No duplicate permissions     │
│                                                              │
│  Permission Codes (Examples):                               │
│  Role=NASABAH (level 1):                                    │
│  ├─ 'deposit_sampah'        - Can deposit waste             │
│  ├─ 'redeem_poin'           - Can redeem points             │
│  ├─ 'view_poin'             - Can view own poin             │
│  ├─ 'view_badges'           - Can view badges               │
│  ├─ 'view_leaderboard'      - Can view leaderboard          │
│  ├─ 'request_withdrawal'    - Can request cash withdrawal   │
│  ├─ 'view_own_history'      - Can view own activity         │
│  └─ 'update_own_profile'    - Can update own profile        │
│                                                              │
│  Role=ADMIN (level 2):                                      │
│  ├─ [all nasabah permissions]                               │
│  ├─ 'approve_deposit'       - Approve waste deposits        │
│  ├─ 'approve_withdrawal'    - Approve cash withdrawals      │
│  ├─ 'approve_redemption'    - Approve product redemptions   │
│  ├─ 'view_all_users'        - View all user data            │
│  ├─ 'view_all_transactions' - View all transactions         │
│  ├─ 'manual_poin_adjust'    - Adjust poin manually          │
│  ├─ 'send_notification'     - Send notifications            │
│  ├─ 'view_admin_dashboard'  - Access admin panel            │
│  └─ 'export_reports'        - Export data reports           │
│                                                              │
│  Role=SUPERADMIN (level 3):                                 │
│  ├─ [all admin permissions]                                 │
│  ├─ 'manage_admins'         - Create/edit/delete admins     │
│  ├─ 'manage_roles'          - Create/edit roles             │
│  ├─ 'manage_permissions'    - Assign permissions            │
│  ├─ 'system_settings'       - Change system config          │
│  ├─ 'audit_logs'            - View admin action logs        │
│  ├─ 'financial_reports'     - View financial data           │
│  └─ 'system_maintenance'    - Maintenance tasks             │
│                                                              │
│  🔗 RELATIONSHIPS:                                          │
│  ← M:1 ← roles (via role_id FK)                            │
│     Each permission belongs to one role                     │
│                                                              │
│  💻 LARAVEL GATE IMPLEMENTATION:                            │
│  Gate::define('deposit_sampah', function($user) {           │
│      return $user->hasPermission('deposit_sampah');         │
│  });                                                         │
│                                                              │
│  if (Gate::allows('deposit_sampah')) {                      │
│      // User can deposit waste                              │
│  }                                                           │
└──────────────────────────────────────────────────────────────┘


┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                         CORE ENTITIES LAYER                               ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌─────────────────────────────────────────────────────────────────────────────┐
│  USERS TABLE                                                    PK: id       │
├─────────────────────────────────────────────────────────────────────────────┤
│  • id             BIGINT UNSIGNED  ← PRIMARY KEY (Auto-increment)           │
│  • no_hp          VARCHAR(255)     ← UNIQUE (Phone Number - Business Key)   │
│  • nama           VARCHAR(255)     ← User Name                              │
│  • email          VARCHAR(255)     ← UNIQUE, User Email                     │
│  • password       VARCHAR(255)     ← Hashed Password                        │
│  • alamat         TEXT             ← User Address                           │
│  • foto_profil    VARCHAR(255)     ← Profile Photo URL                      │
│  • total_poin     INT (default: 0)     ← Total Points Balance               │
│  • total_setor_sampah INT (default: 0)     ← Total Waste Deposits          │
│  • level          VARCHAR(255)     ← User Level (Pemula, Bronze, Silver)    │
│  • created_at     TIMESTAMP        ← Created Date                           │
│  • updated_at     TIMESTAMP        ← Last Updated Date                      │
│                                                                              │
│  🆕 NEW COLUMNS FOR ROLE-BASED ACCESS CONTROL:                             │
│  ├─ role_id          BIGINT (FK) → roles.id (default: 1 = nasabah)        │
│  │  └─ Indicates user type: 1=nasabah, 2=admin, 3=superadmin              │
│  ├─ tipe_nasabah     ENUM('konvensional', 'modern') (default: konvensional)│
│  │  └─ Only applies if role_id=1 (nasabah)                                │
│  ├─ poin_tercatat    INT DEFAULT 0                                         │
│  │  └─ Audit poin (recorded for badges/leaderboard)                        │
│  ├─ nama_bank        VARCHAR(100) NULL                                      │
│  │  └─ Banking info: ONLY for modern nasabah (konvensional = NULL)         │
│  ├─ nomor_rekening   VARCHAR(50) NULL                                       │
│  │  └─ Account number: ONLY for modern nasabah (konvensional = NULL)       │
│  └─ atas_nama_rekening VARCHAR(255) NULL                                   │
│     └─ Account holder: ONLY for modern nasabah (konvensional = NULL)       │
│                                                                              │
│  RELATIONSHIPS (HUB):                                                        │
│  ➜ 1:M → roles               (User has one role)                             │
│  ➜ 1:M → tabung_sampah       (User deposits waste)                          │
│  ➜ 1:M → penukaran_produk    (User redeems products)                        │
│  ➜ 1:M → transaksis          (User transactions)                            │
│  ➜ 1:M → penarikan_tunai     (User cash withdrawals)                        │
│  ➜ 1:M → notifikasi          (User notifications)                           │
│  ➜ 1:M → log_aktivitas       (User activity logs)                           │
│  ➜ 1:M → badge_progress      (User badge progress)                          │
│  ➜ M:M → badges              (via user_badges junction)                     │
│  ➜ 1:M → poin_transaksis     (User point history)                           │
│  ➜ 1:M → sessions            (User sessions)                                │
│  ➜ 1:M → audit_logs          (Admin action logs - new)                      │
└─────────────────────────────────────────────────────────────────────────────┘


┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                    WASTE MANAGEMENT HIERARCHY                              ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌─────────────────────────────────┐
│  KATEGORI_SAMPAH (Categories)   │         PK: id
├─────────────────────────────────┤
│  • id               BIGINT (PK) │
│  • nama_kategori    VARCHAR(255)│  (5 categories)
│  • deskripsi        TEXT        │  Examples:
│  • icon             VARCHAR(255)│  - Plastik
│  • warna            VARCHAR(255)│  - Organik
│  • is_active        BOOLEAN     │  - Logam
│  • created_at       TIMESTAMP   │  - Kertas
│  • updated_at       TIMESTAMP   │  - Elektronik
│        │                        │
│        └─→ JENIS_SAMPAH ←───────┼─→ PK: id, FK: kategori_id
│           (Waste Types)         │
│                                 │   ~20+ waste types
│           ├─ Plastik Keras      │   ├─ Plastik Keras → kategori_sampah(1)
│           ├─ Plastik Lembut     │   ├─ Plastik Lembut → kategori_sampah(1)
│           ├─ Kertas             │   ├─ Kertas → kategori_sampah(4)
│           └─ ...                │   └─ ...
│
│        ↓ 1:M
│        │
│        └─→ TABUNG_SAMPAH ←──────┼─→ PK: id
│           (Waste Deposits)      │   FKs: user_id → users.id (BIGINT)
│                                 │        jadwal_id → jadwal_penyetoran.id
│           Tracks each user's    │
│           waste deposit         │   Additional Fields:
│                                 │   • nama_lengkap (STRING)
│                                 │   • no_hp (STRING)
│                                 │   • titik_lokasi (TEXT)
│                                 │   • jenis_sampah (STRING - not FK)
│                                 │   • berat_kg (DECIMAL)
│                                 │   • foto_sampah (TEXT, nullable)
│                                 │   • status (ENUM: pending/approved/rejected)
│                                 │   • poin_didapat (INT)
│                                 │
│        ↓ 1:M                    │
│        │                        │
│        └─→ POIN_TRANSAKSIS ←────┼─→ PK: id, FKs: user_id, tabung_sampah_id
│           (Point Records)       │   Records point allocation for deposits
│                                 │
│           Status tracking:      │
│           ├─ pending           │
│           ├─ approved          │
│           └─ rejected          │

┌──────────────────────────────────────────────┐
│  JADWAL_PENYETORAN (Deposit Schedules)      │  PK: id
├──────────────────────────────────────────────┤
│  • id              BIGINT (PK)               │
│  • tanggal         DATE                      │
│  • waktu_mulai     TIME                      │
│  • waktu_selesai   TIME                      │
│  • lokasi          VARCHAR(255)              │
│  • kapasitas       INT (default: 100)        │
│  • status          ENUM(aktif, penuh, ...)  │
│  • created_at      TIMESTAMP                 │
│                                               │
│  RELATIONSHIPS:                               │
│  ← M:1 ← tabung_sampah                       │
│         (Deposits follow schedule)            │
└──────────────────────────────────────────────┘


┌──────────────────────────────────────────────┐
│  JENIS_SAMPAH (Waste Types)       PK: id     │
├──────────────────────────────────────────────┤
│  • id                    BIGINT (PK)          │
│  • kategori_sampah_id    BIGINT (FK)          │
│  • nama_jenis            VARCHAR(100)         │
│  • harga_per_kg          DECIMAL(10, 2)       │
│  • satuan                VARCHAR(20) (default: kg) │
│  • kode                  VARCHAR(20) UNIQUE   │
│  • is_active             BOOLEAN (default: true) │
│  • created_at            TIMESTAMP             │
│  • updated_at            TIMESTAMP             │
│                                               │
│  FK Relationship:                              │
│  → kategori_sampah_id → kategori_sampah.id   │
│     (CASCADE DELETE)                           │
│                                               │
│  Examples:                                    │
│  ├─ Plastik Keras (kode: PK001, Rp 2,500/kg)│
│  ├─ Plastik Lembut (kode: PL001, Rp 1,500/kg)│
│  ├─ Kertas HVS (kode: KT001, Rp 500/kg)      │
│  └─ ...                                       │
└──────────────────────────────────────────────┘
```

---

## 📦 **Product & Redemption System**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                   PRODUCT CATALOG & REDEMPTION                             ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌──────────────────────────────────────────────────────┐
│  PRODUKS (Product Catalog)              PK: id       │
├──────────────────────────────────────────────────────┤
│  • id                BIGINT (PK)                      │
│  • nama              VARCHAR(255)                     │
│  • deskripsi         TEXT                            │
│  • harga             DECIMAL(15, 2)                  │
│  • poin_diperlukan   INT                             │
│  • stok              INT                             │
│  • kategori          VARCHAR(255)                    │
│  • foto              VARCHAR(255)                    │
│  • status            ENUM(tersedia, habis, nonaktif) │
│  • created_at        TIMESTAMP                       │
│                                                      │
│        ↓ 1:M                                        │
│        │                                             │
│        └─→ PENUKARAN_PRODUK ←─────→ users.id (BIGINT) │
│           (Product Redemptions)   (user redeems)     │
│                                                      │
│           • id                 BIGINT (PK)          │
│           • user_id            VARCHAR (FK)         │
│           • produk_id          BIGINT (FK)          │
│           • nama_produk        VARCHAR(255)         │
│           • poin_digunakan     INT                  │
│           • jumlah             INT (default: 1)     │
│           • status             ENUM(pending,        │
│           •                         approved,        │
│           •                         cancelled)       │
│           • metode_ambil       TEXT                 │
│           • catatan            TEXT (nullable)      │
│           • tanggal_penukaran  TIMESTAMP            │
│           • tanggal_diambil    TIMESTAMP (nullable) │
│           • created_at         TIMESTAMP            │
│           • updated_at         TIMESTAMP            │
│                                                      │
│           FK Relationships:                         │
│           ← user_id → users.id (BIGINT, CASCADE DELETE)  │
│           ← produk_id → produks.id (CASCADE DELETE) │
└──────────────────────────────────────────────────────┘
```

---

## 💰 **Transaction & Cash Management System**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                    TRANSACTION & CASH MANAGEMENT                           ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌──────────────────────────────────────────────────────────┐
│  KATEGORI_TRANSAKSI (Transaction Types)  PK: id         │
├──────────────────────────────────────────────────────────┤
│  • id            BIGINT (PK)                             │
│  • nama          VARCHAR(255)                            │
│  • deskripsi     TEXT (nullable)                         │
│  • created_at    TIMESTAMP                               │
│  • updated_at    TIMESTAMP                               │
│                                                          │
│  Examples: Penukaran Poin, Penyetoran Sampah, etc.       │
│                                                          │
│        ↓ 1:M                                            │
│        │                                                 │
│        └─→ TRANSAKSIS ←─→ users.id (BIGINT)            │
│           (Transactions)  (user transactions)            │
│                                                          │
│           • id              BIGINT (PK)                 │
│           • user_id         BIGINT (FK)                 │
│           • produk_id       BIGINT (FK)                 │
│           • kategori_id     BIGINT (FK)                 │
│           • jumlah          INT                         │
│           • total_poin      INT                         │
│           • status          ENUM(pending, diproses,     │
│           •                      dikirim, selesai,      │
│           •                      dibatalkan)            │
│           • metode_pengiriman VARCHAR(255) (nullable)   │
│           • alamat_pengiriman TEXT (nullable)           │
│           • created_at       TIMESTAMP                  │
│           • updated_at       TIMESTAMP                  │
│                                                          │
│           FK Relationships:                             │
│           ← user_id → users.id (BIGINT, CASCADE DELETE)  │
│           ← produk_id → produks.id (CASCADE DELETE)     │
│           ← kategori_id → kategori_transaksi (CASCADE)  │
└──────────────────────────────────────────────────────────┘


┌──────────────────────────────────────────────────────────┐
│  PENUKARAN_PRODUK (Product Redemptions - Point Exchange) PK: id │
├──────────────────────────────────────────────────────────┤
│  • id                 BIGINT (PK)                         │
│  • user_id            BIGINT (FK) ──→ users.id (CASCADE) │
│  • produk_id          BIGINT (FK) ──→ produks.id (CASCADE) │
│  • nama_produk        VARCHAR(255)                        │
│  • poin_digunakan     INT                                 │
│  • jumlah             INT (default: 1)                    │
│  • status             ENUM(pending, approved, cancelled)  │
│  • metode_ambil       TEXT                                │
│  • catatan            TEXT (nullable)                     │
│  • tanggal_penukaran  TIMESTAMP                           │
│  • tanggal_diambil    TIMESTAMP (nullable)                │
│  • created_at         TIMESTAMP                           │
│  • updated_at         TIMESTAMP                           │
│                                                           │
│  Note: User menggunakan POIN untuk menukar PRODUK        │
│  Setiap penukaran dicatat di poin_transaksis             │
│  dengan sumber='tukar_poin'                               │
│                                                           │
│  Workflow:                                                │
│  1. User memilih produk & jumlah poin tersedia?          │
│  2. Create penukaran_produk (pending)                    │
│  3. Admin approve atau reject                            │
│  4. Jika approved → poin_transaksis updated              │
│  5. User ambil produk → update tanggal_diambil           │
└──────────────────────────────────────────────────────────┘


┌──────────────────────────────────────────────────────────┐
│  PENARIKAN_TUNAI (Cash Withdrawals)      PK: id         │
├──────────────────────────────────────────────────────────┤
│  • id                  BIGINT (PK)                       │
│  • user_id             BIGINT (FK) ──→ users.id (CASCADE) │
│  • jumlah_poin         INT                              │
│  • jumlah_rupiah       DECIMAL(15, 2)                   │
│  • nomor_rekening      VARCHAR(50)                      │
│  • nama_bank           VARCHAR(100)                     │
│  • nama_penerima       VARCHAR(255)                     │
│  • status              ENUM(pending, approved, rejected)│
│  • catatan_admin       TEXT (nullable)                  │
│  • processed_by        BIGINT (FK) ──→ users.id        │
│  •                     (Admin who processed)            │
│  • processed_at        TIMESTAMP (nullable)             │
│  • created_at          TIMESTAMP                        │
│  • updated_at          TIMESTAMP                        │
│                                                          │
│  Workflow:                                              │
│  1. User requests withdrawal (pending)                  │
│  2. Admin approves/rejects (processed_by set)           │
│  3. Status changes to approved/rejected                 │
│  4. processed_at timestamp recorded                     │
│                                                          │
│  Conversion Rate:                                        │
│  User mengonversi POIN → Rupiah (cash)                  │
│  Setiap withdrawal dicatat di poin_transaksis           │
│  dengan sumber='manual' untuk tracking                  │
│                                                          │
│  Cascade Rules:                                         │
│  ← user_id → users.id (BIGINT, CASCADE DELETE)          │
│  ← processed_by → users.id (SET NULL on delete)         │
└──────────────────────────────────────────────────────────┘


┌──────────────────────────────────────────────────────────┐
│  RELATIONSHIP SUMMARY: Cash & Point Management System    │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Three main redemption/exchange flows:                  │
│                                                          │
│  1️⃣  TRANSAKSIS (General Transaction)                   │
│      User ← produk_id & kategori_id                     │
│      Umum untuk berbagai jenis transaksi                │
│      Status: pending → diproses → dikirim → selesai     │
│                                                          │
│  2️⃣  PENUKARAN_PRODUK (Point → Product)                │
│      User poin_digunakan ← PRODUK dari catalog          │
│      Status: pending → approved → diambil               │
│      Poin berkurang: dicatat di poin_transaksis         │
│                                                          │
│  3️⃣  PENARIKAN_TUNAI (Point → Rupiah/Cash)             │
│      User jumlah_poin ← cash_out (tunai)                │
│      Status: pending → approved/rejected                │
│      Poin berkurang: dicatat di poin_transaksis         │
│                                                          │
│  All three update poin_transaksis (audit trail)         │
└──────────────────────────────────────────────────────────┘
│                                                          │
│  Cascade Rules:                                         │
│  ← user_id → users.id (BIGINT, CASCADE DELETE)          │
│  ← processed_by → users.id (SET NULL on delete)         │
└──────────────────────────────────────────────────────────┘
```

---

## 🏆 **Gamification System**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                   BADGES & GAMIFICATION SYSTEM                             ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌──────────────────────────────────────────────────────────┐
│  BADGES (Achievement Definitions)        PK: id         │
├──────────────────────────────────────────────────────────┤
│  • id               BIGINT (PK)                          │
│  • nama             VARCHAR(255)                         │
│  • deskripsi        TEXT                                 │
│  • icon             VARCHAR(255)                         │
│  • syarat_poin      INT (default: 0)                     │
│  • syarat_setor     INT (default: 0)                     │
│  • reward_poin      INT (default: 0)  ← Bonus for unlock│
│  • tipe             ENUM(poin, setor, kombinasi,        │
│  •                       special, ranking)              │
│  • created_at       TIMESTAMP                            │
│  • updated_at       TIMESTAMP                            │
│                                                          │
│  Examples of Badge Types:                               │
│  ├─ tipe='poin'         (Unlock at X points)            │
│  ├─ tipe='setor'        (Unlock at X waste deposits)    │
│  ├─ tipe='kombinasi'    (Both poin AND setor)           │
│  ├─ tipe='special'      (Event/limited time)            │
│  └─ tipe='ranking'      (Based on leaderboard)          │
│                                                          │
│        ↙─── M:M ────→  USERS                           │
│       │                                                  │
│       │  (via junction table)                            │
│       ↓                                                  │
│                                                          │
│  USER_BADGES (User Achievement Awards)  PK: id         │
│  ├─ id                 BIGINT (PK)                       │
│  ├─ user_id            BIGINT (FK) ──→ users.id         │
│  ├─ badge_id           BIGINT (FK) ──→ badges.id        │
│  ├─ tanggal_dapat      TIMESTAMP                         │
│  ├─ reward_claimed     BOOLEAN (default: true)          │
│  ├─ created_at         TIMESTAMP                         │
│  ├─ updated_at         TIMESTAMP                         │
│  └─ UNIQUE(user_id, badge_id)                           │
│                                                          │
│  Cascade Rules:                                          │
│  ← user_id → users.id (BIGINT, CASCADE DELETE)           │
│  ← badge_id → badges.id (CASCADE DELETE)                 │
│                                                          │
│        ↓ 1:M                                            │
│        │                                                 │
│        └─→ BADGE_PROGRESS (Progress Tracking)           │
│                                                          │
│  BADGE_PROGRESS (Achievement Progress)  PK: id         │
│  ├─ id                   BIGINT (PK)                     │
│  ├─ user_id              BIGINT (FK) ──→ users.id       │
│  ├─ badge_id             BIGINT (FK) ──→ badges.id      │
│  ├─ current_value        INT (default: 0)               │
│  │  └─ Current progress tracking (by badge type):       │
│  │     • 'poin': user's total_poin accumulated          │
│  │     • 'setor': user's total_setor (tons/kg)          │
│  │     • 'kombinasi': MIN(poin%, setor%)                │
│  │     • 'special': 0 (not triggered) or 100 (yes)      │
│  │     • 'ranking': user's current rank (e.g., #2)      │
│  ├─ target_value         INT (default: 0)               │
│  │  └─ Target to reach:                                 │
│  │     • From badge.syarat_poin OR badge.syarat_setor   │
│  │     • For kombinasi: MIN(both targets)               │
│  │     • For special: 1 (just needs trigger event)      │
│  │     • For ranking: top N (e.g., 5 for top 5)        │
│  ├─ progress_percentage  DECIMAL(5, 2) (0-100)          │
│  │  └─ Auto-calculated: (current_value ÷ target_value) × 100 │
│  │     Example: 250÷1000 = 0.25 = 25.00%               │
│  ├─ is_unlocked          BOOLEAN (default: false)       │
│  │  └─ 0=in progress, 1=unlocked → creates user_badge  │
│  ├─ unlocked_at          TIMESTAMP (nullable)           │
│  │  └─ When is_unlocked became TRUE (badge completion)  │
│  ├─ created_at           TIMESTAMP                       │
│  │  └─ When progress record was first created           │
│  ├─ updated_at           TIMESTAMP (auto-update)        │
│  └─ UNIQUE(user_id, badge_id) - prevent duplicates      │
│                                                          │
│  📊 TABLE STATISTICS:                                   │
│  • Estimated rows: 1,000-5,000 (10-50 badges per user) │
│  • Row size: ~120 bytes per progress record             │
│  • Growth rate: 50-100 new rows/day (new user badges)   │
│                                                          │
│  🔗 Cascade Rules:                                      │
│  ← user_id → users.id (BIGINT, CASCADE DELETE)           │
│  ← badge_id → badges.id (CASCADE DELETE)                 │
│                                                          │
│  ⚡ AUTO-TRACKING TRIGGERS (Real-time updates):        │
│  ├─ On setor_sampah created → UpdateBadgeProgressOnTabungSampah │
│  │  └─ Updates 'setor' type badge progress              │
│  ├─ On poin_transaksis added → UpdateBadgeProgressOnPoinChange │
│  │  └─ Updates 'poin' type badge progress               │
│  ├─ On poin_transaksis subtracted → same listener       │
│  │  └─ May decrease progress (if points withdrawn)      │
│  ├─ On user created → InitializeBadges command          │
│  │  └─ Creates badge_progress for ALL badges            │
│  └─ Daily 01:00 AM → RecalculateBadgeProgress command   │
│     └─ Recalculates all users' all badges (edge cases)  │
│                                                          │
│  🎯 PROGRESS STATUS MAPPING (5 Levels):                │
│  ├─ 0-25%:       🔴 JUST STARTED                         │
│  │               └─ "Keep going, you're starting!"      │
│  ├─ 25-50%:      🟠 HALFWAY                              │
│  │               └─ "Good progress, halfway there!"     │
│  ├─ 50-75%:      🟡 ALMOST THERE                         │
│  │               └─ "You're doing great!"               │
│  ├─ 75-99%:      🟢 ALMOST THERE (Final push!)          │
│  │               └─ "Just a little more!"               │
│  └─ 100%:        🏆 COMPLETED ✅                        │
│                  └─ "Congratulations! Badge unlocked!"   │
│                                                          │
│  📈 SAMPLE DATA VISUALIZATION:                          │
│                                                          │
│  Row 1: User #5, "Eco Warrior" (poin type)              │
│  └─ 250/1000 pts → 25.00% → JUST STARTED                │
│                                                          │
│  Row 2: User #5, "Green Depositor" (setor type)         │
│  └─ 75/100 tons → 75.00% → ALMOST THERE!                │
│                                                          │
│  Row 3: User #5, "Eco Master" (kombinasi)               │
│  └─ 40/50 (MIN) → 80.00% → ALMOST THERE! (final!)       │
│                                                          │
│  Row 4: User #5, "Eco Warrior" ✅ COMPLETED             │
│  └─ 1000/1000 pts → 100.00% → COMPLETED                 │
│     ├─ unlocked_at: 2025-11-25 14:30:00                 │
│     ├─ User_badges record CREATED                       │
│     ├─ +100 reward_poin added to user.total_poin        │
│     └─ Poin_transaksis audit trail RECORDED             │
│                                                          │
│  Row 5: User #6, "Eco Warrior" (poin type)              │
│  └─ 500/1000 pts → 50.00% → HALFWAY                     │
│                                                          │
│  ❓ UNDERSTANDING badge_progress.id vs user_badges.id:  │
│                                                          │
│  IMPORTANT: These are TWO DIFFERENT tables with TWO      │
│  DIFFERENT purposes, hence TWO DIFFERENT `id` sequences  │
│                                                          │
│  ┌──────────────────────────────────────────────────────┐│
│  │ BADGE_PROGRESS Table                                ││
│  ├──────────────────────────────────────────────────────┤│
│  │ Purpose: Track progress (0-100%) for EACH badge     ││
│  │ Scope:   EVERYONE who is working on badges          ││
│  │ Rows:    ~1 per user per badge (during progress)    ││
│  │                                                      ││
│  │ Columns:                                             ││
│  │ • id (PK)           ← Auto-increment BIGINT          ││
│  │   Example: 1, 2, 3, 4, 5, ... (sequence grows)      ││
│  │                                                      ││
│  │ • user_id (FK)      ← Reference to users table       ││
│  │ • badge_id (FK)     ← Reference to badges table      ││
│  │ • current_value     ← Progress amount (0-100%)       ││
│  │ • progress_percent  ← Calculated percentage          ││
│  │ • is_unlocked       ← FALSE while in progress        ││
│  │ • unlocked_at       ← NULL until 100% complete       ││
│  │                                                      ││
│  │ KEY POINT: UNIQUE(user_id, badge_id)                ││
│  │ → Exactly ONE progress record per user per badge    ││
│  │ → Cannot have duplicates                             ││
│  │ → Auto-increment id is just internal row identifier  ││
│  │                                                      ││
│  │ Example Rows:                                        ││
│  │ ┌─────────────────────────────────────────────────┐ ││
│  │ │ id │ user │ badge │ current │ progress │ unlocked │ ││
│  │ ├─────────────────────────────────────────────────┤ ││
│  │ │ 1  │  5   │  1    │  250    │  25%     │ FALSE    │ ││
│  │ │ 2  │  5   │  2    │  75     │  75%     │ FALSE    │ ││
│  │ │ 3  │  5   │  3    │  800    │  80%     │ FALSE    │ ││
│  │ │ 4  │  6   │  1    │  500    │  50%     │ FALSE    │ ││
│  │ │ 5  │  7   │  2    │  100    │ 100%     │ FALSE    │ ││
│  │ └─────────────────────────────────────────────────┘ ││
│  │                                                      ││
│  │ Status: "IN PROGRESS" (user still working toward)   ││
│  └──────────────────────────────────────────────────────┘│
│                                                          │
│  vs                                                      │
│                                                          │
│  ┌──────────────────────────────────────────────────────┐│
│  │ USER_BADGES Table                                   ││
│  ├──────────────────────────────────────────────────────┤│
│  │ Purpose: Record EARNED badges (100% completed)      ││
│  │ Scope:   ONLY users who actually unlocked badges    ││
│  │ Rows:    ~1 per user per badge (after unlock)       ││
│  │                                                      ││
│  │ Columns:                                             ││
│  │ • id (PK)           ← Auto-increment BIGINT          ││
│  │   Example: 1, 2, 3, 4, 5, ... (different sequence!) ││
│  │                                                      ││
│  │ • user_id (FK)      ← Reference to users table       ││
│  │ • badge_id (FK)     ← Reference to badges table      ││
│  │ • tanggal_dapat     ← When badge was earned         ││
│  │ • reward_claimed    ← Whether reward was claimed    ││
│  │                                                      ││
│  │ KEY POINT: UNIQUE(user_id, badge_id)                ││
│  │ → Exactly ONE earned badge record per user per badge││
│  │ → Cannot have duplicates                             ││
│  │ → Auto-increment id is just internal row identifier  ││
│  │                                                      ││
│  │ Example Rows:                                        ││
│  │ ┌────────────────────────────────────────────────┐ ││
│  │ │ id │ user │ badge │ tanggal_dapat │ reward    │ ││
│  │ ├────────────────────────────────────────────────┤ ││
│  │ │ 1  │  5   │  3    │ 2025-11-24    │ claimed   │ ││
│  │ │ 2  │  6   │  1    │ 2025-11-23    │ claimed   │ ││
│  │ │ 3  │  7   │  2    │ 2025-11-22    │ claimed   │ ││
│  │ │ 4  │  8   │  1    │ 2025-11-21    │ claimed   │ ││
│  │ └────────────────────────────────────────────────┘ ││
│  │                                                      ││
│  │ Status: "EARNED & COMPLETED" (badge unlocked!)      ││
│  └──────────────────────────────────────────────────────┘│
│                                                          │
│  🔑 KEY DIFFERENCES SUMMARY:                            │
│                                                          │
│  ┌──────────────────────────────────────────────────────┐│
│  │ Aspect              │ badge_progress │ user_badges  ││
│  ├──────────────────────────────────────────────────────┤│
│  │ Purpose             │ Track progress │ Record earned││
│  │ When created        │ On user signup │ At 100% done ││
│  │ Status              │ 0-100% progress│ COMPLETED    ││
│  │ Rows per user/badge │ Exactly 1      │ Exactly 1    ││
│  │ id sequence         │ 1,2,3,4,5...   │ 1,2,3,4,5... ││
│  │ id purpose          │ Row identifier │ Row identifier││
│  │ UNIQUE constraint   │ (u_id,b_id)    │ (u_id,b_id)  ││
│  │ When deleted        │ When progress  │ When earned  ││
│  │                     │ starts over    │ badge taken? ││
│  │ Typical rows        │ 5,000 (active) │ 500 (total)  ││
│  └──────────────────────────────────────────────────────┘│
│                                                          │
│  📊 DATA FLOW EXAMPLE (User Gets Badge):               │
│                                                          │
│  1️⃣  User Registers                                    │
│     badge_progress created (all badges, is_unlocked=0)  │
│     Example: badge_progress.id=1, user=5, badge=1      │
│                                                          │
│  2️⃣  User Deposits Waste / Gets Points                 │
│     badge_progress UPDATED continuously                 │
│     current_value increases: 250 → 500 → 750 → 1000    │
│     progress_percentage increases: 25% → 50% → 100%    │
│                                                          │
│  3️⃣  User Reaches 100% (TRIGGER POINT)                │
│     badge_progress.is_unlocked = TRUE                  │
│     badge_progress.unlocked_at = NOW                    │
│     ↓                                                    │
│     CREATE NEW user_badges record!                      │
│     Example: user_badges.id=1, user=5, badge=1         │
│     user_badges.tanggal_dapat = NOW                     │
│     ↓                                                    │
│     Add reward_poin to user.total_poin                  │
│     Create audit entry in poin_transaksis               │
│                                                          │
│  4️⃣  Now Both Records Exist (Different Tables!)       │
│     ┌─ badge_progress.id=1 (row 1 in progress table)   │
│     │  ├─ user_id=5, badge_id=1                        │
│     │  ├─ current_value=1000                            │
│     │  ├─ progress_percentage=100%                      │
│     │  ├─ is_unlocked=TRUE                              │
│     │  └─ unlocked_at=2025-11-25 14:30:00               │
│     │                                                   │
│     └─ user_badges.id=1 (row 1 in earned table)         │
│        ├─ user_id=5, badge_id=1                         │
│        ├─ tanggal_dapat=2025-11-25 14:30:00             │
│        ├─ reward_claimed=TRUE                           │
│        └─ Points awarded: +100                          │
│                                                          │
│  🎯 WHY TWO DIFFERENT TABLES WITH UNIQUE CONSTRAINTS?  │
│                                                          │
│  1. SEPARATION OF CONCERNS                              │
│     • badge_progress: tracks PROGRESS (0-100%)          │
│     • user_badges: records ACHIEVEMENT (earned)         │
│                                                          │
│  2. DIFFERENT QUERIES                                   │
│     • Progress: "What's my % on each badge?"            │
│     • Earned: "What badges have I completed?"           │
│                                                          │
│  3. DIFFERENT LIFECYCLE                                 │
│     • Progress: exists from day 1 (signup)              │
│     • Earned: created only after 100% reached           │
│                                                          │
│  4. PREVENT DUPLICATES                                  │
│     • Each UNIQUE constraint ensures:                   │
│       → Can't have 2 progress records for same badge    │
│       → Can't have 2 earned records for same badge      │
│                                                          │
│  5. AUTO-INCREMENT ID NOT A BUSINESS KEY                │
│     • badge_progress.id=1 and user_badges.id=1         │
│       are in DIFFERENT TABLES                           │
│     • They don't conflict (different tables)            │
│     • Both are independent sequences                    │
│     • The REAL KEY is (user_id, badge_id)              │
│                                                          │
│  ✅ COMPOSITE KEY EXPLANATION:                         │
│                                                          │
│  The (user_id, badge_id) is what REALLY matters:        │
│                                                          │
│  badge_progress COMPOSITE UNIQUE KEY:                   │
│  • user=5, badge=1  ← Can only have 1 progress record  │
│  • user=5, badge=2  ← Can have 1 progress record       │
│  • user=6, badge=1  ← Different user, separate record  │
│                                                          │
│  user_badges COMPOSITE UNIQUE KEY:                      │
│  • user=5, badge=1  ← Can only have 1 earned record    │
│  • user=5, badge=2  ← Can have 1 earned record         │
│  • user=6, badge=1  ← Different user, separate record  │
│                                                          │
│  The auto-increment `id` (1, 2, 3...) is just for      │
│  internal database row identification. The BUSINESS     │
│  logic uses (user_id, badge_id) to ensure uniqueness.   │
│                                                          │
│  ⚙️ BUSINESS LOGIC IMPLEMENTATION:                      │
│                                                          │
│  Service: BadgeTrackingService.php                       │
│  ├─ updateUserBadgeProgress() - Main update method      │
│  ├─ calculateCurrentValue() - Calculate current value   │
│  ├─ shouldUnlock() - Check if should be unlocked        │
│  ├─ unlockBadge() - Create user_badges record           │
│  ├─ initializeUserBadges() - Setup for new user         │
│  ├─ recalculateAllUserProgress() - Daily cron job       │
│  ├─ getUserBadgeSummary() - Get all progress data       │
│  └─ getUserBadgeDetails() - Get detail for one badge    │
│                                                          │
│  Events: 2 listeners (app/Listeners/)                   │
│  ├─ UpdateBadgeProgressOnTabungSampah                    │
│  └─ UpdateBadgeProgressOnPoinChange                      │
│                                                          │
│  🔍 QUERY EXAMPLES (Performance-optimized):             │
│  1. Get user's all badge progress:                      │
│     SELECT * FROM badge_progress WHERE user_id = ?      │
│     → Uses index: (user_id)                              │
│                                                          │
│  2. Get completed badges for user:                      │
│     SELECT * FROM badge_progress                        │
│     WHERE user_id = ? AND is_unlocked = 1               │
│     → Uses index: (user_id, is_unlocked)                 │
│                                                          │
│  3. Get almost-complete badges (75%+):                  │
│     SELECT * FROM badge_progress                        │
│     WHERE user_id = ? AND progress_percentage >= 75     │
│     AND is_unlocked = 0                                 │
│     → Uses index: (user_id, progress_percentage)        │
│                                                          │
│  4. Count unlocked badges by user:                      │
│     SELECT user_id, COUNT(*) FROM badge_progress        │
│     WHERE is_unlocked = 1 GROUP BY user_id              │
│     ORDER BY 2 DESC LIMIT 10                            │
│     → Uses index: (badge_id, is_unlocked)               │
│                                                          │
│  5. Get badge popularity (total unlocks):               │
│     SELECT badge_id, COUNT(*) FROM badge_progress       │
│     WHERE is_unlocked = 1 GROUP BY badge_id             │
│     ORDER BY 2 DESC                                     │
│     → Uses index: (badge_id, is_unlocked)               │
│                                                          │
│  🚀 INDEXES FOR PERFORMANCE:                            │
│  ├─ INDEX (user_id) - Find user's progress              │
│  ├─ INDEX (user_id, is_unlocked) - Find unlocked ones   │
│  ├─ INDEX (user_id, progress_percentage) - Filter level │
│  ├─ INDEX (badge_id, is_unlocked) - Badge popularity    │
│  ├─ INDEX (is_unlocked, created_at) - Recent unlocks    │
│  └─ COMPOSITE (user_id, is_unlocked, progress_percentage) │
│                                                          │
│  📌 UNIQUE CONSTRAINT:                                  │
│  UNIQUE(user_id, badge_id)                              │
│  └─ Prevents: user tracking same badge multiple times   │
│                                                          │
│  ✅ API ENDPOINTS (5 endpoints, all working):           │
│  ├─ GET /api/user/badges/progress - My progress        │
│  ├─ GET /api/user/badges/completed - My earned badges  │
│  ├─ GET /api/badges/leaderboard - Top achievers        │
│  ├─ GET /api/badges/available - All badge definitions  │
│  └─ GET /api/admin/badges/analytics - Admin dashboard  │
│                                                          │
│  💾 DATABASE INTEGRATION:                               │
│  └─ Synced with: badges, user_badges, poin_transaksis  │
│     └─ When progress reaches 100%:                      │
│        ├─ Creates user_badges record                    │
│        ├─ Awards reward_poin from badges table          │
│        ├─ Records audit trail in poin_transaksis        │
│        └─ Updates user.total_poin                       │
└──────────────────────────────────────────────────────────┘
```

---

## 📊 **Point System & Audit**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                    POINT TRANSACTION AUDIT SYSTEM                          ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌──────────────────────────────────────────────────────────┐
│  POIN_TRANSAKSIS (Point Ledger - Complete Audit)       │
├──────────────────────────────────────────────────────────┤
│  PK: id                                                  │
│  FKs: user_id → users.id (BIGINT, CASCADE DELETE)        │
│       tabung_sampah_id → tabung_sampah.id (SET NULL)    │
│                                                          │
│  Columns:                                                │
│  • id                 BIGINT (PK)                        │
│  • user_id            BIGINT (FK)                        │
│  • tabung_sampah_id   BIGINT (FK, nullable)              │
│  • jenis_sampah       VARCHAR(255, nullable)             │
│  • berat_kg           DECIMAL(6, 2, nullable)            │
│  • poin_didapat       INT (can be negative!)             │
│  • sumber             VARCHAR(255)                       │
│  • keterangan         TEXT (nullable)                    │
│  • referensi_id       BIGINT (nullable)                  │
│  • referensi_tipe     VARCHAR(255, nullable)             │
│  • created_at         TIMESTAMP                          │
│  • updated_at         TIMESTAMP                          │
│                                                          │
│  Unique Constraint:                                      │
│  UNIQUE(user_id, tabung_sampah_id, sumber)               │
│                                                          │
│  Indexes:                                                │
│  • Index on user_id                                      │
│  • Index on sumber                                       │
│  • Index on created_at                                   │
│  • Composite Index (user_id, created_at)                 │
│  • Composite Index (user_id, sumber)                     │
│                                                          │
│  ❓ REFERENSI_ID & REFERENSI_TIPE (Polymorphic Reference):│
│                                                          │
│  These fields implement a POLYMORPHIC REFERENCE SYSTEM  │
│  to link poin_transaksis to its SOURCE/PROOF document   │
│                                                          │
│  📌 CONCEPT:                                            │
│  When poin changes, MUST record WHERE it came from      │
│  ├─ referensi_id: ID dari source document               │
│  ├─ referensi_tipe: Tabel mana yang di-reference        │
│  └─ Together: Point to bukti/evidence of transaction    │
│                                                          │
│  🔄 COMPLETE SOURCE TYPES MAPPING:                      │
│                                                          │
│  1️⃣  sumber='setor_sampah' (Waste Deposit Points)       │
│     ├─ poin_didapat: +10 to +500 (based on kg & type)  │
│     ├─ tabung_sampah_id: SET (direct FK to deposit)     │
│     ├─ referensi_id: tabung_sampah.id (same value)     │
│     ├─ referensi_tipe: 'setor_sampah' (table name)     │
│     ├─ keterangan: "Poin dari deposit 5kg plastik"     │
│     └─ 📊 Example:                                       │
│        User setor 5kg plastik (id 123)                   │
│        → poin_transaksis created:                        │
│           ├─ poin_didapat: +50                          │
│           ├─ tabung_sampah_id: 123                      │
│           ├─ referensi_id: 123                          │
│           ├─ referensi_tipe: 'setor_sampah'             │
│           └─ Verify: SELECT * FROM tabung_sampah id=123 │
│                                                          │
│  2️⃣  sumber='tukar_poin' (Product Redemption)          │
│     ├─ poin_didapat: NEGATIVE (points deducted!)        │
│     ├─ tabung_sampah_id: NULL (not related to deposit) │
│     ├─ referensi_id: penukaran_produk.id (redemption)  │
│     ├─ referensi_tipe: 'penukaran_produk' (table)      │
│     ├─ keterangan: "Tukar 2x Botol @ 50 poin each"    │
│     └─ 📊 Example:                                       │
│        User tukar produk (id 456)                        │
│        → poin_transaksis created:                        │
│           ├─ poin_didapat: -100 (cost 100 poin)        │
│           ├─ tabung_sampah_id: null                     │
│           ├─ referensi_id: 456                          │
│           ├─ referensi_tipe: 'penukaran_produk'         │
│           └─ Verify: SELECT * FROM penukaran_produk 456 │
│                                                          │
│  3️⃣  sumber='badge' (Badge Reward Points)              │
│     ├─ poin_didapat: reward_poin (from badge definition)│
│     ├─ tabung_sampah_id: NULL                           │
│     ├─ referensi_id: user_badges.id (earned badge rec) │
│     ├─ referensi_tipe: 'badge' (achievement)           │
│     ├─ keterangan: "Reward badge Eco Warrior"           │
│     └─ 📊 Example:                                       │
│        User unlock badge (id 789 in user_badges)        │
│        → poin_transaksis created:                        │
│           ├─ poin_didapat: +100 (reward_poin)          │
│           ├─ tabung_sampah_id: null                     │
│           ├─ referensi_id: 789                          │
│           ├─ referensi_tipe: 'badge'                    │
│           └─ Verify: SELECT * FROM user_badges id=789   │
│                                                          │
│  4️⃣  sumber='bonus' (System/Promo Bonus)               │
│     ├─ poin_didapat: +amount (promotional points)       │
│     ├─ tabung_sampah_id: NULL                           │
│     ├─ referensi_id: NULL (no specific record)          │
│     ├─ referensi_tipe: 'event' OR 'promotion'          │
│     ├─ keterangan: "Bonus November Campaign" / "Promo"  │
│     └─ 📊 Example:                                       │
│        System gives promotional bonus                    │
│        → poin_transaksis created:                        │
│           ├─ poin_didapat: +50                          │
│           ├─ tabung_sampah_id: null                     │
│           ├─ referensi_id: null (no proof needed)       │
│           ├─ referensi_tipe: 'event'                    │
│           └─ Reason: System promotional event           │
│                                                          │
│  5️⃣  sumber='manual' (Admin Adjustment)                │
│     ├─ poin_didapat: ±amount (can be +/-)              │
│     ├─ tabung_sampah_id: NULL                           │
│     ├─ referensi_id: NULL (admin decision)              │
│     ├─ referensi_tipe: 'admin_adjustment'              │
│     ├─ keterangan: "Kompensasi order salah kirim"      │
│     └─ 📊 Example:                                       │
│        Admin corrects poin error                         │
│        → poin_transaksis created:                        │
│           ├─ poin_didapat: +100 (correction)            │
│           ├─ tabung_sampah_id: null                     │
│           ├─ referensi_id: null (admin memo)            │
│           ├─ referensi_tipe: 'admin_adjustment'         │
│           └─ Reason: Manual admin correction            │
│                                                          │
│  🎯 WHY REFERENSI FIELDS MATTER:                        │
│                                                          │
│  ✅ AUDIT TRAIL - Can trace every poin to source       │
│  ✅ FRAUD DETECTION - Verify poin legitimacy            │
│  ✅ DISPUTE RESOLUTION - Prove what happened            │
│  ✅ COMPLIANCE - Regulatory documentation               │
│  ✅ POLYMORPHIC - One table references many tables      │
│                                                          │
│  🔍 QUERY EXAMPLES:                                     │
│                                                          │
│  1. Trace single poin record:                           │
│     $poin = PoinTransaksis::find(1001);                 │
│     $bukti = $poin->referensi(); // Polymorphic!        │
│     // Returns: TabungSampah object (if setor_sampah)   │
│     // Or: PenukAranProduk object (if tukar_poin)       │
│     // Or: UserBadges object (if badge reward)          │
│                                                          │
│  2. Get all deposit-based poin:                         │
│     SELECT * FROM poin_transaksis                       │
│     WHERE sumber='setor_sampah' AND user_id=?           │
│     ORDER BY created_at DESC;                           │
│                                                          │
│  3. Verify referensi exists:                            │
│     SELECT pt.*, ts.* FROM poin_transaksis pt          │
│     LEFT JOIN tabung_sampah ts ON                       │
│       pt.referensi_id=ts.id AND                         │
│       pt.referensi_tipe='setor_sampah'                  │
│     WHERE pt.user_id=? AND pt.sumber='setor_sampah';   │
│                                                          │
│  4. Find orphaned records (data integrity):             │
│     SELECT * FROM poin_transaksis                       │
│     WHERE referensi_id IS NOT NULL                      │
│     AND referensi_tipe='setor_sampah'                   │
│     AND NOT EXISTS (                                    │
│       SELECT 1 FROM tabung_sampah                       │
│       WHERE id=poin_transaksis.referensi_id             │
│     );                                                  │
│     // Lists deleted/broken references                  │
│                                                          │
│  💾 LARAVEL POLYMORPHIC RELATIONSHIP:                   │
│                                                          │
│  In PoinTransaksis Model:                               │
│                                                          │
│  public function referensi()                            │
│  {                                                      │
│      return $this->morphTo();                           │
│  }                                                      │
│                                                          │
│  Usage:                                                 │
│  $poin = PoinTransaksis::find(1001);                   │
│  $source = $poin->referensi;  // Auto-resolves!        │
│  // If referensi_tipe='setor_sampah'                    │
│  //   → Returns TabungSampah instance                   │
│  // If referensi_tipe='penukaran_produk'                │
│  //   → Returns PenukAranProduk instance                │
│  // If referensi_tipe='badge'                           │
│  //   → Returns UserBadges instance                     │
│                                                          │
│  ⚠️  IMPORTANT NOTES:                                   │
│                                                          │
│  • For setor_sampah: tabung_sampah_id == referensi_id  │
│    (both point to same tabung_sampah record)            │
│                                                          │
│  • For tukar_poin: referensi_id points to              │
│    penukaran_produk.id (NOT tabung_sampah)              │
│                                                          │
│  • For bonus/manual: referensi_id can be NULL           │
│    (no specific source document needed)                 │
│                                                          │
│  • referensi_tipe MUST match database value name!       │
│    (not display name, must match Laravel class mapping) │
│                                                          │
│  SOURCE TYPES & REFERENSI MAPPING (Quick Ref):        │
│                                                          │
│  Example Audit Trail for User "08123456789":             │
│  ┌────────────────────────────────────────────────────┐  │
│  │ ID │ Sumber  │ Poin │ Tanggal          │ Ref Type │  │
│  ├────────────────────────────────────────────────────┤  │
│  │ 1  │ setor   │ +50  │ 2025-11-20 10:00 │ setor    │  │
│  │ 2  │ setor   │ +75  │ 2025-11-21 14:30 │ setor    │  │
│  │ 3  │ bonus   │ +25  │ 2025-11-22 08:15 │ event    │  │
│  │ 4  │ tukar   │ -100 │ 2025-11-23 16:45 │ product  │  │
│  │ 5  │ badge   │ +30  │ 2025-11-24 11:20 │ badge    │  │
│  │ 6  │ manual  │ +50  │ 2025-11-25 09:00 │ admin    │  │
│  └────────────────────────────────────────────────────┘  │
│  Total Current Points: 230                               │
└──────────────────────────────────────────────────────────┘
```

---

## 🔔 **Notification & Logging System**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                  NOTIFICATIONS & ACTIVITY LOGGING                          ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌──────────────────────────────────────────────────────────┐
│  NOTIFIKASI (Notifications)              PK: id         │
├──────────────────────────────────────────────────────────┤
│  • id                 BIGINT (PK)                        │
│  • user_id            BIGINT (FK) ──→ users.id          │
│  • judul              VARCHAR(255)                       │
│  • pesan              TEXT                               │
│  • tipe               VARCHAR(255)  (info, warning, etc) │
│  • is_read            BOOLEAN (default: false)           │
│  • related_id         BIGINT (nullable)                  │
│  • related_type       VARCHAR(255) (nullable)            │
│  • created_at         TIMESTAMP                          │
│  • updated_at         TIMESTAMP                          │
│                                                          │
│  FK Cascade:                                             │
│  ← user_id → users.id (BIGINT, CASCADE DELETE)           │
│                                                          │
│  Example Notifications:                                 │
│  ├─ "Deposit Accepted" → related_type='tabung_sampah'   │
│  ├─ "Points Added" → related_type='poin_transaksis'     │
│  ├─ "Product Redeemed" → related_type='penukaran_produk'│
│  ├─ "Withdrawal Approved" → related_type='penarikan'    │
│  └─ "Badge Unlocked" → related_type='user_badges'       │
└──────────────────────────────────────────────────────────┘


┌──────────────────────────────────────────────────────────┐
│  LOG_AKTIVITAS (Activity Audit Log)      PK: id         │
├──────────────────────────────────────────────────────────┤
│  • id                 BIGINT (PK)                        │
│  • user_id            BIGINT (FK) ──→ users.id          │
│  • tipe_aktivitas     VARCHAR(50)                        │
│  • deskripsi          TEXT (nullable)                    │
│  • poin_perubahan     INT (default: 0)                   │
│  • tanggal            TIMESTAMP                          │
│  • created_at         TIMESTAMP                          │
│                                                          │
│  Indexes:                                                │
│  • Index on (user_id, tanggal) for fast queries          │
│                                                          │
│  FK Cascade:                                             │
│  ← user_id → users.id (BIGINT, CASCADE DELETE)           │
│                                                          │
│  Activity Types Tracked:                                 │
│  ├─ login               (User logged in)                 │
│  ├─ deposit_sampah      (Waste deposited)                │
│  ├─ tukar_poin          (Points redeemed)                │
│  ├─ terima_transaksi    (Transaction received)           │
│  ├─ penarikan_poin      (Cash withdrawal)                │
│  ├─ badge_unlock        (Badge unlocked)                 │
│  ├─ profile_update      (Profile changed)                │
│  └─ other               (Other activities)               │
│                                                          │
│  Audit Trail Example:                                   │
│  ┌────────────────────────────────────────────────┐     │
│  │ TipeAktivitas │ Deskripsi  │ PoinPerubahan    │     │
│  ├────────────────────────────────────────────────┤     │
│  │ login         │ Masuk syst │ 0                │     │
│  │ deposit       │ Setor 5kg  │ +50              │     │
│  │ tukar_poin    │ Tukar BOTOL│ -100             │     │
│  │ penarikan     │ Tarik Tunai│ -500             │     │
│  └────────────────────────────────────────────────┘     │
└──────────────────────────────────────────────────────────┘


┌──────────────────────────────────────────────────────────┐
│  🆕 AUDIT_LOGS (Admin Action Audit)        PK: id       │
├──────────────────────────────────────────────────────────┤
│  Purpose: Track all admin actions for compliance          │
│           and security monitoring                         │
│                                                          │
│  • id                 BIGINT (PK)                        │
│  • admin_id           BIGINT (FK) ──→ users.id          │
│  •                    (Admin who performed action)        │
│  • action_type        VARCHAR(100)                       │
│  •                    (approve_deposit, adjust_poin, etc)│
│  • resource_type      VARCHAR(100) (nullable)            │
│  •                    (users, tabung_sampah, etc)        │
│  • resource_id        BIGINT (nullable)                  │
│  •                    (ID of resource being modified)    │
│  • old_values         JSON (nullable)                    │
│  •                    (Previous state before action)     │
│  • new_values         JSON (nullable)                    │
│  •                    (New state after action)           │
│  • reason             TEXT (nullable)                    │
│  •                    (Why admin took this action)       │
│  • ip_address         VARCHAR(45) (nullable)             │
│  •                    (IPv4 or IPv6 address)            │
│  • user_agent         TEXT (nullable)                    │
│  •                    (Browser/device info)              │
│  • status             ENUM(success, failed)              │
│  • error_message      TEXT (nullable)                    │
│  •                    (Error details if action failed)   │
│  • created_at         TIMESTAMP                          │
│                                                          │
│  Indexes:                                                │
│  • INDEX (admin_id, created_at) - Find admin's actions  │
│  • INDEX (action_type, created_at) - Find action type   │
│  • INDEX (resource_type, resource_id) - Find by resource│
│  • INDEX (created_at) - Recent actions                  │
│                                                          │
│  FK Constraint:                                          │
│  admin_id → users.id (CASCADE DELETE, role_id=2 or 3)   │
│                                                          │
│  Audit Trail Example (Admin actions):                   │
│  ┌────────────────────────────────────────────────────┐ │
│  │ Action         │ Resource    │ OldValue │ NewValue │ │
│  ├────────────────────────────────────────────────────┤ │
│  │ approve_deposit│ tabung(123) │ pending  │ approved │ │
│  │ adjust_poin    │ user(5)     │ 500      │ 550      │ │
│  │ reject_withdraw│ penarikan(8)│ pending  │ rejected │ │
│  │ create_admin   │ user(99)    │ null     │ admin    │ │
│  └────────────────────────────────────────────────────┘ │
│                                                          │
│  📊 SAMPLE FULL AUDIT ENTRY (JSON):                     │
│                                                          │
│  {                                                      │
│    id: 1001,                                            │
│    admin_id: 10,  // Admin user                         │
│    action_type: 'approve_deposit',                      │
│    resource_type: 'tabung_sampah',                      │
│    resource_id: 123,                                    │
│    old_values: {status: 'pending'},                     │
│    new_values: {status: 'approved'},                    │
│    reason: 'Verified waste weight manually',            │
│    ip_address: '192.168.1.100',                         │
│    user_agent: 'Mozilla/5.0...',                        │
│    status: 'success',                                   │
│    created_at: '2025-11-27 14:30:00'                    │
│  }                                                      │
│                                                          │
│  🔒 SECURITY FEATURES:                                  │
│  ├─ Immutable records (no updates allowed)              │
│  ├─ Complete audit trail (before/after values)          │
│  ├─ IP address tracking for location verification       │
│  ├─ User agent for device identification                │
│  ├─ Reason field for accountability                     │
│  └─ Status tracking (success/failed attempts)           │
│                                                          │
│  ⚙️ LARAVEL IMPLEMENTATION:                             │
│  AuditLog::create([                                      │
│      'admin_id' => Auth::id(),                          │
│      'action_type' => 'approve_deposit',                │
│      'resource_type' => 'tabung_sampah',                │
│      'resource_id' => $deposit->id,                     │
│      'old_values' => ['status' => 'pending'],           │
│      'new_values' => ['status' => 'approved'],          │
│      'reason' => $request->reason,                      │
│      'ip_address' => $request->ip(),                    │
│      'user_agent' => $request->userAgent(),             │
│      'status' => 'success'                              │
│  ]);                                                     │
└──────────────────────────────────────────────────────────┘
```

---

## 📋 **Role-Based Permission Matrix**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                   PERMISSION MATRIX BY ROLE                                ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌─────────────────────────────────────────────────────────────────────────────┐
│                           ROLE HIERARCHY                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  Level 3: SUPERADMIN (System Manager)                                       │
│  ├─ Highest privileges                                                      │
│  ├─ Can manage admins and system settings                                   │
│  └─ Full access to all features and reports                                 │
│         ▲                                                                    │
│         │ INCLUDES ALL LEVEL 2 PERMISSIONS                                  │
│         │                                                                    │
│  Level 2: ADMIN (Bank Staff)                                                │
│  ├─ Medium privileges                                                       │
│  ├─ Can approve/reject user transactions                                    │
│  └─ Limited to user management and approvals                                │
│         ▲                                                                    │
│         │ INCLUDES ALL LEVEL 1 PERMISSIONS                                  │
│         │                                                                    │
│  Level 1: NASABAH (Regular User)                                            │
│  ├─ Lowest privileges                                                       │
│  ├─ Can deposit waste, redeem poin, view own data                           │
│  └─ Limited to personal activities only                                     │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────┐
│  DETAILED PERMISSION MATRIX                                                │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  Permission Code               │ Nasabah │ Admin │ Superadmin │ Description │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┷━━━━━━━┷━━━━━━┷━━━━━━━━━━┷━━━━━━━━━━━━  │
│                                                                             │
│  === DEPOSIT & COLLECTION ===                                              │
│  deposit_sampah                │   ✅   │  ✅  │    ✅     │ Deposit waste  │
│  view_own_deposits             │   ✅   │  ✅  │    ✅     │ View history   │
│  cancel_own_deposit            │   ✅   │  ✅  │    ✅     │ Cancel (< 1hr) │
│  approve_deposit               │   ❌   │  ✅  │    ✅     │ Admin approve  │
│  reject_deposit                │   ❌   │  ✅  │    ✅     │ Admin reject   │
│  view_all_deposits             │   ❌   │  ✅  │    ✅     │ List all       │
│                                                                             │
│  === POINT REDEMPTION & WITHDRAWAL ===                                     │
│  redeem_poin                   │   ✅   │  ✅  │    ✅     │ Exchange poin  │
│  request_withdrawal            │   ✅   │  ✅  │    ✅     │ Ask for cash   │
│  view_own_poin                 │   ✅   │  ✅  │    ✅     │ View balance   │
│  view_poin_history             │   ✅   │  ✅  │    ✅     │ View ledger    │
│  approve_withdrawal            │   ❌   │  ✅  │    ✅     │ Process cash   │
│  reject_withdrawal             │   ❌   │  ✅  │    ✅     │ Deny request   │
│  view_all_withdrawals          │   ❌   │  ✅  │    ✅     │ List all       │
│  manual_poin_adjust            │   ❌   │  ✅  │    ✅     │ Adjust poin    │
│  poin_adjust_reason_required   │   ❌   │  ✅  │    ✅     │ Requires note  │
│                                                                             │
│  === PRODUCT REDEMPTION ===                                                │
│  view_products                 │   ✅   │  ✅  │    ✅     │ Browse catalog │
│  redeem_product                │   ✅   │  ✅  │    ✅     │ Exchange item  │
│  cancel_redemption             │   ✅   │  ✅  │    ✅     │ Cancel (< 1hr) │
│  approve_redemption            │   ❌   │  ✅  │    ✅     │ Approve claim  │
│  reject_redemption             │   ❌   │  ✅  │    ✅     │ Reject claim   │
│  view_all_redemptions          │   ❌   │  ✅  │    ✅     │ List all       │
│                                                                             │
│  === GAMIFICATION & LEADERBOARD ===                                        │
│  view_own_badges               │   ✅   │  ✅  │    ✅     │ My progress    │
│  view_own_leaderboard          │   ✅   │  ✅  │    ✅     │ My rank        │
│  view_all_leaderboard          │   ✅   │  ✅  │    ✅     │ Global ranking │
│  view_all_badge_progress       │   ❌   │  ✅  │    ✅     │ All badges     │
│  reset_user_badge              │   ❌   │  ❌  │    ✅     │ Reset progress │
│                                                                             │
│  === USER MANAGEMENT ===                                                   │
│  view_own_profile              │   ✅   │  ✅  │    ✅     │ My profile     │
│  update_own_profile            │   ✅   │  ✅  │    ✅     │ Edit my data   │
│  view_all_users                │   ❌   │  ✅  │    ✅     │ List all       │
│  view_user_details             │   ❌   │  ✅  │    ✅     │ User info      │
│  deactivate_user               │   ❌   │  ❌  │    ✅     │ Disable account│
│  reactivate_user               │   ❌   │  ❌  │    ✅     │ Re-enable      │
│                                                                             │
│  === ADMIN MANAGEMENT (Superadmin only) ===                                │
│  create_admin                  │   ❌   │  ❌  │    ✅     │ Add new admin  │
│  edit_admin                    │   ❌   │  ❌  │    ✅     │ Modify admin   │
│  delete_admin                  │   ❌   │  ❌  │    ✅     │ Remove admin   │
│  assign_admin_role             │   ❌   │  ❌  │    ✅     │ Change role    │
│  view_all_admins               │   ❌   │  ❌  │    ✅     │ List admins    │
│  audit_admin_actions           │   ❌   │  ❌  │    ✅     │ View audit log │
│                                                                             │
│  === NOTIFICATIONS & COMMUNICATION ===                                     │
│  view_own_notifications        │   ✅   │  ✅  │    ✅     │ My messages    │
│  send_notification             │   ❌   │  ✅  │    ✅     │ Notify users   │
│  send_bulk_notification        │   ❌   │  ❌  │    ✅     │ Mass notify    │
│                                                                             │
│  === REPORTING & ANALYTICS ===                                             │
│  view_own_analytics            │   ✅   │  ✅  │    ✅     │ My stats       │
│  export_own_data               │   ✅   │  ✅  │    ✅     │ Download mine  │
│  view_admin_dashboard          │   ❌   │  ✅  │    ✅     │ Admin panel    │
│  export_user_reports           │   ❌   │  ✅  │    ✅     │ User data      │
│  view_financial_reports        │   ❌   │  ❌  │    ✅     │ Money reports  │
│  export_financial_data         │   ❌   │  ❌  │    ✅     │ Download $$$   │
│  view_system_analytics         │   ❌   │  ❌  │    ✅     │ System stats   │
│                                                                             │
│  === SYSTEM MAINTENANCE ===                                                │
│  view_system_settings          │   ❌   │  ❌  │    ✅     │ Config page    │
│  update_system_settings        │   ❌   │  ❌  │    ✅     │ Change config  │
│  manage_roles_permissions      │   ❌   │  ❌  │    ✅     │ Role settings  │
│  system_maintenance            │   ❌   │  ❌  │    ✅     │ Maintenance    │
│  view_system_logs              │   ❌   │  ❌  │    ✅     │ Error logs     │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────┐
│  ACCESS CONTROL DECISION FLOW                                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  When user tries to access feature:                                        │
│                                                                             │
│  1️⃣  GET USER ROLE                                                        │
│     SELECT users.role_id FROM users WHERE id = ?                          │
│     Example: role_id = 1 (nasabah)                                        │
│                                                                             │
│  2️⃣  CHECK PERMISSION MATRIX                                              │
│     SELECT 1 FROM role_permissions                                         │
│     WHERE role_id = ? AND permission = ?                                   │
│     Example: role_id=1, permission='deposit_sampah'                       │
│     Result: ✅ ALLOWED                                                     │
│                                                                             │
│  3️⃣  APPLY ADDITIONAL CHECKS                                              │
│     ├─ For nasabah: Only own data accessible                              │
│     │  Example: user_id != requested_user_id → 403 Forbidden              │
│     ├─ For admin: Access control by resource                              │
│     │  Example: can view any user, but create limited to superadmin       │
│     └─ For superadmin: Full access                                        │
│        Example: Can access everything                                      │
│                                                                             │
│  4️⃣  EXECUTE OPERATION                                                    │
│     ├─ Success → Return data with 200 OK                                  │
│     ├─ Unauthorized → Return 401 Unauthorized                             │
│     └─ Forbidden → Return 403 Forbidden                                   │
│                                                                             │
│  📊 DECISION TREE EXAMPLE (Deposit Feature):                              │
│                                                                             │
│  Request: POST /api/deposits (User wants to deposit waste)                │
│                 │                                                          │
│                 ▼                                                          │
│     ┌─────────────────────┐                                               │
│     │ User authenticated? │                                                │
│     └──────────┬──────────┘                                                │
│               /  \\                                                         │
│             NO    YES                                                      │
│             │      └──────────────┐                                        │
│             │                     ▼                                        │
│             │          ┌──────────────────┐                               │
│             │          │ Has permission   │                               │
│             │          │ 'deposit_sampah' │                               │
│             │          └────────┬─────────┘                               │
│             │                  /  \\                                       │
│             │                NO    YES                                    │
│             │                │      ▼                                     │
│             │                │   ┌────────────────┐                      │
│             │                │   │ Is nasabah     │                      │
│             │                │   │ KONVENSIONAL?  │                      │
│             │                │   └────────┬───────┘                      │
│             │                │          /  \\                            │
│             │                │        NO    YES → ✅ ALLOW               │
│             │                │        │                                  │
│             │                │        ▼                                  │
│             │                │   ┌──────────────┐                        │
│             │                │   │ Is nasabah   │                        │
│             │                │   │ MODERN?      │                        │
│             │                │   └──────┬───────┘                        │
│             │                │         /  \\                             │
│             │                │       NO    YES → ✅ ALLOW (poin tracked) │
│             │                │       │                                   │
│             │                └────────┘                                  │
│             │                │                                           │
│             └────────┬────────┘                                           │
│                      │                                                    │
│                      ▼                                                    │
│              ❌ RETURN ERROR                                             │
│              (401, 403, or other)                                        │
│                                                                             │
│  ⚙️ LARAVEL MIDDLEWARE IMPLEMENTATION:                                    │
│                                                                             │
│  // In middleware (CheckPermission.php):                                  │
│  public function handle($request, Closure $next, $permission)            │
│  {                                                                         │
│      $user = Auth::user();                                                │
│      if (!$user) return response('Unauthorized', 401);                   │
│      if (!$user->hasPermission($permission))                              │
│          return response('Forbidden', 403);                              │
│      return $next($request);                                              │
│  }                                                                         │
│                                                                             │
│  // In route (web.php):                                                   │
│  Route::post('/deposits', [DepositController::class, 'store'])            │
│      ->middleware('auth')                                                 │
│      ->middleware('permission:deposit_sampah');                           │
│                                                                             │
│  // In controller:                                                         │
│  public function store(Request $request)                                  │
│  {                                                                         │
│      // User already validated by middleware                              │
│      // Now check specific business logic                                 │
│      $user = Auth::user();                                                │
│      if ($user->role_id == 1 && $user->tipe_nasabah == 'modern') {      │
│          // Modern nasabah: poin will be tracked but not usable          │
│      }                                                                     │
│      // Process deposit...                                                │
│  }                                                                         │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 📰 **Content Management System**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                      CONTENT & SESSIONS                                    ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌──────────────────────────────────────────────────────────┐
│  ARTIKELS (Articles/Content)             PK: id         │
├──────────────────────────────────────────────────────────┤
│  • id                BIGINT (PK)                          │
│  • judul             VARCHAR(255)                         │
│  • slug              VARCHAR(255) UNIQUE                  │
│  • konten            LONGTEXT                             │
│  • foto_cover        VARCHAR(255) (nullable)              │
│  • penulis           VARCHAR(255)                         │
│  • kategori          VARCHAR(255)                         │
│  • tanggal_publikasi DATE                                 │
│  • views             INT (default: 0)                     │
│  • created_at        TIMESTAMP                            │
│  • updated_at        TIMESTAMP                            │
│                                                           │
│  No Foreign Keys (standalone content table)               │
│  ~50-100 articles typical                                 │
│                                                           │
│  ❓ WHAT IS SLUG? (URL-Friendly Identifier)             │
│                                                           │
│  📌 CONCEPT:                                             │
│  Slug adalah versi URL-friendly dari judul artikel       │
│  ├─ Dihapus spasi & karakter khusus                     │
│  ├─ Dikonversi menjadi lowercase                         │
│  ├─ UNIQUE untuk setiap artikel (no duplicates)         │
│  └─ Digunakan untuk SEO-friendly URLs                    │
│                                                           │
│  🔄 JUDUL vs SLUG COMPARISON:                           │
│                                                           │
│  Judul (Human Readable):                                 │
│  ├─ "Manfaat Daur Ulang Sampah Plastik"                 │
│  ├─ "Cara Memulai Bisnis Sampah Organik"                │
│  └─ "Tips & Trik Menabung Poin di MENDAUR"              │
│                                                           │
│  Slug (URL Safe):                                        │
│  ├─ "manfaat-daur-ulang-sampah-plastik"                 │
│  ├─ "cara-memulai-bisnis-sampah-organik"                │
│  └─ "tips-trik-menabung-poin-di-mendaur"                │
│                                                           │
│  ✨ WHY SLUG MATTERS:                                    │
│                                                           │
│  1️⃣  SEO OPTIMIZATION                                   │
│     └─ URLs with keywords rank better in search         │
│        Example: /artikel/manfaat-daur-ulang (better)     │
│        vs:      /artikel/123 (not SEO-friendly)          │
│                                                           │
│  2️⃣  USER-FRIENDLY URLs                                 │
│     └─ Easy to read, understand, and share              │
│        Memorable: manfaat-daur-ulang-sampah-plastik      │
│        vs remembering: article ID 47?                     │
│                                                           │
│  3️⃣  ACCESSIBILITY & SHARING                            │
│     └─ Can see what article is about from URL           │
│        Share on social media with descriptive link       │
│                                                           │
│  4️⃣  PREVENT URL MANIPULATION                           │
│     └─ Can't directly query by ID from URL              │
│        UNIQUE constraint prevents duplicate slugs        │
│                                                           │
│  5️⃣  PERMANENT URL STRUCTURE                            │
│     └─ Even if title changes, slug stays same (best)    │
│        Or update slug & set up redirects                │
│                                                           │
│  🛠️  SLUG GENERATION RULES:                             │
│                                                           │
│  Original Judul:      "Manfaat Daur Ulang Sampah!"      │
│                                                           │
│  Step 1: Lowercase    "manfaat daur ulang sampah!"      │
│  Step 2: Remove ! &   "manfaat daur ulang sampah"       │
│  Step 3: Trim spaces  "manfaat daur ulang sampah"       │
│  Step 4: Replace sp → "manfaat-daur-ulang-sampah"       │
│  Result Slug:         "manfaat-daur-ulang-sampah"       │
│                                                           │
│  💻 LARAVEL IMPLEMENTATION - AUTO-GENERATION (Standard):  │
│                                                           │
│  ❓ APAKAH SLUG OTOMATIS DI-GENERATE DARI JUDUL?        │
│                                                           │
│  JAWAB: YA! Slug HARUS di-generate otomatis dari judul! │
│  ├─ Ini adalah BEST PRACTICE di web development          │
│  ├─ User tidak perlu input slug secara manual            │
│  ├─ Otomatis dibuat saat artikel dibuat/diupdate         │
│  ├─ Menghindari kesalahan input manual                   │
│  └─ Memastikan konsistensi format                        │
│                                                           │
│  🔄 IMPLEMENTATION OPTIONS:                              │
│                                                           │
│  OPTION 1: Backend Auto-Generate (RECOMMENDED) ✅       │
│  └─ Backend membuat slug otomatis saat create/update    │
│     ├─ Dihitung dari judul dengan Str::slug()           │
│     ├─ User hanya input judul, slug auto-terisi         │
│     ├─ Lebih aman (user tidak bisa manipulasi)          │
│     ├─ Konsisten setiap waktu                           │
│     └─ Best practice untuk production                   │
│                                                           │
│  OPTION 2: Frontend Suggestion (Hybrid)                 │
│  └─ Frontend suggest slug dari judul, user bisa edit    │
│     ├─ User lihat preview slug saat mengetik judul      │
│     ├─ User bisa customize jika diperlukan              │
│     ├─ Tapi backend TETAP regenerate otomatis           │
│     └─ User input diabaikan jika ada duplikasi          │
│                                                           │
│  OPTION 3: Manual Input (NOT RECOMMENDED) ❌            │
│  └─ User input slug sendiri saat membuat artikel        │
│     ├─ Sering kesalahan format                          │
│     ├─ Tidak konsisten                                  │
│     ├─ Bisa duplikasi jika user lupa                    │
│     └─ JANGAN gunakan ini!                              │
│                                                           │
│  🎯 MENDAUR RECOMMENDED APPROACH (OPTION 1):            │
│                                                           │
│  Model Hook (Eloquent Observer atau Mutator):           │
│                                                           │
│  // app/Models/Artikel.php                               │
│  class Artikel extends Model {                           │
│      protected static function boot() {                  │
│          parent::boot();                                 │
│                                                           │
│          // Create: Auto-generate slug from judul       │
│          static::creating(function ($artikel) {          │
│              $artikel->slug = Str::slug(                 │
│                  $artikel->judul                         │
│              );                                          │
│          });                                             │
│                                                           │
│          // Update: Regenerate slug if judul changed    │
│          static::updating(function ($artikel) {          │
│              if ($artikel->isDirty('judul')) {           │
│                  $artikel->slug = Str::slug(             │
│                      $artikel->judul                     │
│                  );                                      │
│              }                                           │
│          });                                             │
│      }                                                   │
│  }                                                       │
│                                                           │
│  Atau menggunakan Mutator (Automatic):                  │
│                                                           │
│  // Langsung mutate saat judul di-set                   │
│  protected function judul(): Attribute {                 │
│      return Attribute::make(                             │
│          set: function ($value) {                        │
│              return $value;                              │
│          },                                              │
│      )->shouldBeEncrypted();                             │
│  }                                                       │
│                                                           │
│  // Slug otomatis dari judul                            │
│  #[Computed]                                             │
│  public function slug(): string {                        │
│      return Str::slug($this->judul);                     │
│  }                                                       │
│                                                           │
│  📊 FLOW EXAMPLE - AUTO-GENERATION:                     │
│                                                           │
│  User Input: Judul artikel                               │
│  ├─ "Manfaat Daur Ulang Sampah Plastik"                 │
│                                                           │
│  Backend Processing (Automatic):                         │
│  ├─ 1. Terima judul: "Manfaat Daur Ulang Sampah P..."   │
│  ├─ 2. Generate slug otomatis:                          │
│  │   └─ Str::slug("Manfaat Daur Ulang Sampah P...")    │
│  ├─ 3. Cek duplicate:                                   │
│  │   └─ Artikel::where('slug', 'manfaat-..')->exists()  │
│  ├─ 4. Jika duplicate → tambah suffix:                  │
│  │   └─ "manfaat-...-2" atau "-" + timestamp            │
│  ├─ 5. Simpan ke database:                              │
│  │   ├─ judul: "Manfaat Daur Ulang Sampah P..."        │
│  │   └─ slug: "manfaat-daur-ulang-sampah-plastik"      │
│  └─ 6. Return ke frontend dengan slug terisi            │
│                                                           │
│  Result di Database:                                     │
│  ├─ judul: "Manfaat Daur Ulang Sampah Plastik"         │
│  ├─ slug: "manfaat-daur-ulang-sampah-plastik"          │
│  └─ URL: /artikel/manfaat-daur-ulang-sampah-plastik    │
│                                                           │
│  ✅ ADVANTAGES OF AUTO-GENERATION:                      │
│                                                           │
│  1. Konsistensi Format ✓                                │
│     └─ Semua slug punya format sama (kebab-case)        │
│                                                           │
│  2. Prevent User Error ✓                                │
│     └─ User tidak bisa input slug salah format          │
│                                                           │
│  3. Prevent Duplicates ✓                                │
│     └─ Backend cek & tambah suffix otomatis jika ada    │
│                                                           │
│  4. Sync Judul & Slug ✓                                 │
│     └─ Update judul → slug otomatis update              │
│                                                           │
│  5. Security ✓                                          │
│     └─ User tidak bisa manipulasi slug                  │
│                                                           │
│  6. Time Saving ✓                                       │
│     └─ User fokus ke konten, sistem handle slug         │
│                                                           │
│  ⚠️  IMPORTANT CONSIDERATION:                           │
│                                                           │
│  SHOULD SLUG CHANGE WHEN JUDUL CHANGES?                │
│                                                           │
│  Scenario 1: Judul berubah → Slug tetap (RECOMMENDED)  │
│  ├─ Pro: Existing links tetap valid                    │
│  ├─ Pro: SEO links tetap works                         │
│  ├─ Pro: Sharing links tetap valid                     │
│  ├─ Con: Slug tidak akurat lagi                        │
│  └─ Solution: Setup redirect old_slug → new_slug       │
│                                                           │
│  Scenario 2: Judul berubah → Slug regenerate (RISKY)   │
│  ├─ Pro: Slug selalu akurat dengan judul               │
│  ├─ Con: Existing links BREAK! (404 error)             │
│  ├─ Con: SEO value lost                                │
│  ├─ Con: Social media shares broken                    │
│  └─ Solution: Must setup redirect besar-besaran        │
│                                                           │
│  🏆 BEST PRACTICE FOR MENDAUR:                          │
│                                                           │
│  ├─ Generate slug otomatis saat CREATE                  │
│  ├─ JANGAN auto-regenerate saat UPDATE judul           │
│  ├─ Keep slug tetap (immutable) setelah create          │
│  ├─ Jika perlu ganti slug → manual override             │
│  └─ Setup redirect jika ada perubahan slug              │
│                                                           │
│  💾 IMPLEMENTATION PATTERN (For MENDAUR):               │
│                                                           │
│  Step 1: Creating Article                               │
│  └─ POST /api/articles                                  │
│     ├─ Input: { judul, konten, ... }                    │
│     ├─ Backend: Generate slug = Str::slug($judul)       │
│     ├─ Backend: Check duplicate & add suffix if needed  │
│     ├─ Backend: Save with slug                          │
│     └─ Response: { id, judul, slug, konten, ... }       │
│                                                           │
│  Step 2: Updating Article (Title Change)                │
│  └─ PUT /api/articles/{id}                              │
│     ├─ Input: { judul: "New Title", konten, ... }       │
│     ├─ Option A: Keep old slug (RECOMMENDED)            │
│     │  └─ Response: { slug unchanged, judul updated }   │
│     │                                                   │
│     └─ Option B: Manual slug override                   │
│        └─ Input: { judul, slug: "custom-slug", ... }    │
│           (only if explicitly provided)                 │
│                                                           │
│  Step 3: Getting Article                                │
│  ├─ GET /api/articles/{id}          (by ID)            │
│  ├─ GET /api/articles/{slug}        (by slug)           │
│  └─ Both work, prefer slug for public API               │
│                                                           │
│  🔍 DUPLICATE SLUG HANDLING:                            │
│                                                           │
│  Scenario: User buat 2 artikel dengan judul mirip       │
│                                                           │
│  Article 1:                                              │
│  ├─ judul: "Tips Daur Ulang Sampah Plastik"            │
│  └─ slug: "tips-daur-ulang-sampah-plastik"              │
│                                                           │
│  Article 2:                                              │
│  ├─ judul: "Tips & Trik Daur Ulang Sampah Plastik"     │
│  ├─ Generated slug: "tips-trik-daur-ulang-sampah-p..." │
│  ├─ Check: Not duplicate ✓                             │
│  └─ slug: "tips-trik-daur-ulang-sampah-plastik"        │
│                                                           │
│  Article 3 (Exact duplicate):                           │
│  ├─ judul: "Tips Daur Ulang Sampah Plastik" (same!)    │
│  ├─ Generated slug: "tips-daur-ulang-sampah-plastik"    │
│  ├─ Check: DUPLICATE! ❌                               │
│  ├─ Solution options:                                   │
│  │  ├─ Add suffix: "tips-daur-ulang-sampah-plastik-2"  │
│  │  ├─ Add timestamp: "tips-daur-ulang-sampah-p-123456"│
│  │  ├─ Add ID: "tips-daur-ulang-sampah-plastik-id-3"   │
│  │  └─ Reject & ask user to change judul               │
│  └─ MENDAUR Choice: Add "-2", "-3", etc suffix          │
│                                                           │
│  💻 PSEUDO CODE (Duplicate Handling):                   │
│                                                           │
│  baseSlug = Str::slug($artikel->judul)                  │
│  slug = baseSlug                                        │
│  counter = 2                                            │
│                                                           │
│  while (Artikel::where('slug', slug)->exists()) {       │
│      slug = baseSlug . '-' . counter                     │
│      counter++                                          │
│  }                                                       │
│                                                           │
│  artikel->slug = slug  // Finally, save unique slug      │
│                                                           │
│  📋 DATABASE CONSTRAINTS FOR SLUG:                      │
│                                                           │
│  1. UNIQUE Constraint                                   │
│     └─ Prevent duplicate slugs                          │
│     └─ Built-in INDEX for fast lookups                  │
│                                                           │
│  2. IMMUTABLE (After create)                            │
│     └─ Don't regenerate on judul change                │
│     └─ Protect existing URLs from breaking              │
│                                                           │
│  3. Index Optimization                                  │
│     └─ INDEX (slug) for lookups                         │
│     └─ Index on slug + kategori for filtered queries    │
│                                                           │
│  ✨ MENDAUR SLUG STRATEGY (FINAL):                      │
│                                                           │
│  ├─ ✅ AUTO-GENERATE from judul (backend)              │
│  ├─ ✅ UNIQUE constraint on DB                         │
│  ├─ ✅ Handle duplicates with suffix                   │
│  ├─ ✅ NEVER change slug after creation                │
│  ├─ ✅ Use slug for public APIs (SEO-friendly)         │
│  ├─ ✅ Immutable after first create                    │
│  └─ ✅ KEBAB-CASE format (lowercase with dashes)       │
│                                                           │
│  On Create (Automatic):                                  │
│  $artikel->slug = Str::slug($artikel->judul);            │
│                                                           │
│  On Update (Keep slug, don't regenerate):               │
│  // $artikel->slug = Str::slug($artikel->judul);        │
│  // ❌ DON'T regenerate!                                │
│  // Just update konten, judul tetap bisa berubah        │
│  // tapi slug tetap untuk backward compatibility        │
│                                                           │
│  📊 REAL-WORLD EXAMPLES (MENDAUR Use Case):            │
│                                                           │
│  Artikel #1:                                             │
│  ├─ id: 1                                                │
│  ├─ judul: "Panduan Lengkap Daur Ulang Plastik"        │
│  ├─ slug: "panduan-lengkap-daur-ulang-plastik"          │
│  └─ URL: /artikel/panduan-lengkap-daur-ulang-plastik    │
│     Note: User membaca dari URL apa tentang artikel      │
│                                                           │
│  Artikel #2:                                             │
│  ├─ id: 2                                                │
│  ├─ judul: "Mengapa Program Daur Ulang Penting?"        │
│  ├─ slug: "mengapa-program-daur-ulang-penting"          │
│  └─ URL: /artikel/mengapa-program-daur-ulang-penting    │
│                                                           │
│  Artikel #3:                                             │
│  ├─ id: 3                                                │
│  ├─ judul: "Tips & Trik Menabung Poin MENDAUR 2025"    │
│  ├─ slug: "tips-trik-menabung-poin-mendaur-2025"        │
│  └─ URL: /artikel/tips-trik-menabung-poin-mendaur-2025  │
│                                                           │
│  🔍 QUERY PATTERNS (Using Slug):                        │
│                                                           │
│  1. Get article by slug (COMMON):                        │
│     SELECT * FROM artikels WHERE slug = 'panduan-...'    │
│     // More user-friendly than ID lookup                 │
│                                                           │
│  2. Get article by ID (Admin/Internal):                  │
│     SELECT * FROM artikels WHERE id = 1                  │
│     // Still available for admin operations              │
│                                                           │
│  3. Check slug existence (prevent duplicates):           │
│     SELECT COUNT(*) FROM artikels                        │
│     WHERE slug = ? AND id != ?                           │
│     // Validates UNIQUE constraint on update             │
│                                                           │
│  🎯 API ENDPOINT EXAMPLES:                              │
│                                                           │
│  Using ID (Internal):                                    │
│  GET /api/articles/1                                     │
│  └─ Faster (direct ID lookup) but not user-friendly     │
│                                                           │
│  Using Slug (Public API):                                │
│  GET /api/articles/panduan-lengkap-daur-ulang-plastik   │
│  └─ Slower (string search) but SEO-friendly             │
│                                                           │
│  Best Practice: Support BOTH!                            │
│  GET /api/articles/1                 (by ID)            │
│  GET /api/articles/panduan-lengkap... (by slug)         │
│                                                           │
│  ⚙️  LARAVEL ROUTE BINDING (Model Resolution):           │
│                                                           │
│  In Routes:                                              │
│  Route::get('/artikel/{artikel}', 'show');              │
│                                                           │
│  By default: Resolves to ID                             │
│  URL: /artikel/1                                         │
│                                                           │
│  Custom Binding: Resolves to slug                        │
│  Route::bind('artikel', function($value) {              │
│      return Artikel::where('slug', $value)->first();    │
│  });                                                     │
│                                                           │
│  URL: /artikel/panduan-lengkap-daur-ulang-plastik       │
│  └─ Route automatically finds article by slug!           │
│                                                           │
│  ⚡ PERFORMANCE OPTIMIZATION:                           │
│                                                           │
│  Slug Lookup Performance:                                │
│  ├─ Without index: O(n) - scan entire table             │
│  ├─ With UNIQUE: Built-in index - O(log n)             │
│  ├─ Direct: String comparison fast                       │
│  └─ Typical response: < 1ms for 1000s of articles       │
│                                                           │
│  UNIQUE Constraint Benefits:                             │
│  ├─ Index created automatically                          │
│  ├─ Prevents duplicate slugs                            │
│  ├─ Fast lookups                                         │
│  └─ Database enforces integrity                          │
│                                                           │
│  📋 SLUG GENERATION EDGE CASES:                         │
│                                                           │
│  Problem: What if two titles generate same slug?        │
│  ├─ Title 1: "Tips & Trik Poin"                         │
│  ├─ Title 2: "Tips - Trik Poin"                         │
│  └─ Both become: "tips-trik-poin" (DUPLICATE!)          │
│                                                           │
│  Solution 1: Add suffix                                  │
│  ├─ "tips-trik-poin"                                     │
│  ├─ "tips-trik-poin-2"                                  │
│  └─ "tips-trik-poin-3"                                  │
│                                                           │
│  Solution 2: Add unique identifier                       │
│  ├─ "tips-trik-poin-1"                                  │
│  ├─ "tips-trik-poin-2"                                  │
│  └─ "tips-trik-poin-3"                                  │
│                                                           │
│  Solution 3: Use Str::slug() + unique check              │
│  $slug = Str::slug($judul);                              │
│  if (Artikel::where('slug', $slug)->exists()) {          │
│      $slug .= '-' . time();                              │
│  }                                                       │
│                                                           │
│  ✅ BEST PRACTICES:                                      │
│                                                           │
│  1. ALWAYS auto-generate from judul                      │
│     └─ No manual slug input needed                       │
│                                                           │
│  2. NEVER expose ID in URL                               │
│     └─ Always use slug for public URLs                   │
│                                                           │
│  3. KEEP slug consistent                                 │
│     └─ Don't change unless necessary (breaks links)      │
│                                                           │
│  4. ADD INDEX on slug                                    │
│     └─ UNIQUE constraint creates index automatically     │
│                                                           │
│  5. HANDLE DUPLICATES gracefully                         │
│     └─ Add suffix or timestamp if conflict               │
│                                                           │
│  6. DOCUMENT slug format                                 │
│     └─ Help frontend developers use correct URL format   │
│                                                           │
│  📚 COMMON SLUG FORMATS:                                 │
│                                                           │
│  Kebab-case (Recommended - Most Common):                 │
│  └─ "panduan-daur-ulang-sampah" ✅                       │
│                                                           │
│  Snake_case (Less Common):                               │
│  └─ "panduan_daur_ulang_sampah" ⚠️                       │
│                                                           │
│  Mixed (Avoid):                                          │
│  └─ "panduan-daur_ulang-sampah" ❌                       │
│                                                           │
│  MENDAUR Standard: kebab-case (dashes)                   │
│  └─ Follows web standard conventions                     │
└──────────────────────────────────────────────────────────┘

---

## 🔗 **COMPREHENSIVE TABLE RELATIONSHIPS**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃          COMPLETE DATA FLOW: tabung_sampah → poin_transaksis               ┃
┃         → penukaran_produk → penarikan_tunai → transaksis                  ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

🎯 CENTRAL CONCEPT: FIVE TABLES, ONE POIN SYSTEM
────────────────────────────────────────────────

POIN_TRANSAKSIS adalah JANTUNG dari seluruh sistem:
├─ Source #1: tabung_sampah (waste deposits) → +poin
├─ Source #2: penukaran_produk (redemptions) → -poin
├─ Source #3: penarikan_tunai (withdrawals) → -poin
├─ Source #4: badge (achievements) → +poin
├─ Source #5: bonus & manual (admin/promo) → ±poin
└─ ALL diaudit & ditrack di poin_transaksis table!

TRANSAKSIS adalah TERPISAH (general transactions system):
└─ Tidak langsung related ke poin_transaksis
└─ Bisa untuk order dengan uang, poin, atau hybrid


📊 TABLE ROLES & RELATIONSHIPS:

┌─────────────────────────────────────────────────────────┐
│ 1️⃣  TABUNG_SAMPAH (Waste Deposits)                     │
│     Question: "Berapa kg sampah yang user setor?"        │
├─────────────────────────────────────────────────────────┤
│ Columns: id, user_id, berat_kg, jenis_sampah, status,  │
│          poin_didapat, created_at                        │
│                                                          │
│ Flow:                                                    │
│ 1. User deposits 5kg waste                              │
│ 2. tabung_sampah record created (status: pending)       │
│ 3. Admin approves                                        │
│ 4. System triggers: PoinTransaksis created              │
│    └─ sumber='setor_sampah', poin_didapat=+50           │
│    └─ referensi_id=tabung_sampah.id (link to proof)    │
│ 5. user.total_poin += 50                                │
│                                                          │
│ Next Step: User can now:                                │
│ ├─ tukar_poin (penukaran_produk)                        │
│ ├─ tarik tunai (penarikan_tunai)                        │
│ └─ work toward badges (badge_progress)                  │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ 2️⃣  POIN_TRANSAKSIS (Point Audit Ledger) ⭐ CRITICAL │
│     Question: "Bagaimana poin user berubah?"            │
├─────────────────────────────────────────────────────────┤
│ Columns: id, user_id, poin_didapat (±), sumber,         │
│          referensi_id, referensi_tipe, created_at       │
│                                                          │
│ Purpose: SINGLE SOURCE OF TRUTH for all poin changes   │
│                                                          │
│ Links to Multiple Sources (POLYMORPHIC):                │
│ ├─ sumber='setor_sampah' → referensi_id→tabung_sampah  │
│ ├─ sumber='tukar_poin' → referensi_id→penukaran_produk │
│ ├─ sumber='badge' → referensi_id→user_badges           │
│ ├─ sumber='manual' → referensi_id→penarikan_tunai      │
│ └─ sumber='bonus' → referensi_id=NULL (no proof needed)│
│                                                          │
│ Audit Examples:                                         │
│ ├─ +50 from setor_sampah #123                          │
│ ├─ +30 from setor_sampah #124                          │
│ ├─ -80 from tukar_poin #456 (penukaran_produk)         │
│ ├─ +25 from bonus (promo event)                        │
│ └─ -25 from manual #111 (penarikan_tunai)              │
│                                                          │
│ Total Current Poin = SUM(poin_didapat) for user        │
│ └─ Should match users.total_poin (if data clean!)       │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ 3️⃣  PENUKARAN_PRODUK (Point → Product Redemption)      │
│     Question: "User mau tukar poin jadi produk?"        │
├─────────────────────────────────────────────────────────┤
│ Columns: id, user_id, produk_id, poin_digunakan,        │
│          jumlah, status, metode_ambil, tanggal_diambil  │
│                                                          │
│ Flow:                                                    │
│ 1. User selects product & quantity                      │
│ 2. penukaran_produk created (status: pending)           │
│ 3. Admin approves (status: approved)                    │
│ 4. System triggers: PoinTransaksis created              │
│    └─ sumber='tukar_poin', poin_didapat=-100 (NEGATIVE!)│
│    └─ referensi_id=penukaran_produk.id                  │
│ 5. user.total_poin -= 100                               │
│ 6. User picks up product (tanggal_diambil set)          │
│ 7. Status: taken/completed                              │
│                                                          │
│ KEY: NOT same as transaksis!                            │
│ └─ penukaran_produk = poin-only (no money)              │
│ └─ Always reduces poin by poin_digunakan amount         │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ 4️⃣  PENARIKAN_TUNAI (Point → Cash Withdrawal)          │
│     Question: "User mau tarik poin jadi uang?"          │
├─────────────────────────────────────────────────────────┤
│ Columns: id, user_id, jumlah_poin, jumlah_rupiah,       │
│          nomor_rekening, nama_bank, status,             │
│          processed_by, processed_at                      │
│                                                          │
│ Flow:                                                    │
│ 1. User requests withdrawal (e.g., 500 poin → Rp50k)   │
│ 2. penarikan_tunai created (status: pending)            │
│ 3. Admin reviews & approves (status: approved)          │
│ 4. System transfers cash to bank account                │
│ 5. System triggers: PoinTransaksis created              │
│    └─ sumber='manual', poin_didapat=-500 (NEGATIVE!)    │
│    └─ referensi_id=penarikan_tunai.id                   │
│ 6. user.total_poin -= 500                               │
│ 7. Status: completed (user got cash)                    │
│                                                          │
│ KEY: Different from penukaran_produk                    │
│ └─ penukaran_produk = poin → product (goods)            │
│ └─ penarikan_tunai = poin → money (cash out)            │
│ └─ Both permanently reduce poin balance                 │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ 5️⃣  TRANSAKSIS (General Transaction System)            │
│     Question: "User membuat transaksi tipe apa?"        │
├─────────────────────────────────────────────────────────┤
│ Columns: id, user_id, produk_id, kategori_id, jumlah,   │
│          total_poin, status, metode_pengiriman,         │
│          alamat_pengiriman                               │
│                                                          │
│ Purpose: GENERAL PURPOSE transaction (not point-specific)│
│                                                          │
│ ⚠️  KEY DIFFERENCE:                                     │
│ ├─ NOT directly for point redemption                    │
│ ├─ Can involve money, poin, or hybrid                   │
│ ├─ Includes delivery/shipping workflow                  │
│ ├─ Status workflow: pending→diproses→dikirim→selesai    │
│ └─ Can be SEPARATE from point system                    │
│                                                          │
│ Common Uses:                                            │
│ ├─ User buys product with MONEY (not poin)             │
│ ├─ Order placed + shipping required                    │
│ ├─ Payment gateway integration                         │
│ └─ May award bonus poin after completion               │
│                                                          │
│ Example Flow:                                           │
│ 1. User orders product for Rp100,000 (money, not poin) │
│ 2. transaksis created (status: pending)                │
│ 3. Payment confirmed (status: diproses)                │
│ 4. Order shipped (status: dikirim)                      │
│ 5. User receives (status: selesai)                      │
│ 6. System may award bonus poin (+10) → PoinTransaksis   │
│                                                          │
│ ⚠️  NOT related to:                                     │
│ └─ penukaran_produk (different workflow)                │
│ └─ penarikan_tunai (different workflow)                 │
│ └─ Directly to poin_transaksis (unless bonus awarded)   │
└─────────────────────────────────────────────────────────┘

```

---

## 🌐 **COMPLETE EXAMPLE: ONE USER'S FULL JOURNEY**

```
USER "ADI" (ID: 5) - COMPLETE TRANSACTION HISTORY
══════════════════════════════════════════════════

📍 DAY 1: DEPOSIT WASTE → EARN POIN
──────────────────────────────────
Action: Adi deposits 5kg plastik & 3kg kertas (8kg total)

1. tabung_sampah #123 created
   ├─ berat_kg: 5
   ├─ jenis_sampah: "Plastik Keras"
   ├─ status: pending → approved
   └─ poin_didapat: 50

2. System creates: poin_transaksis #1001
   ├─ sumber: 'setor_sampah'
   ├─ poin_didapat: +50
   ├─ referensi_id: 123
   └─ referensi_tipe: 'setor_sampah'

3. users table updated
   └─ total_poin: 0 → 50

4. tabung_sampah #124 created
   ├─ berat_kg: 3
   ├─ jenis_sampah: "Kertas"
   ├─ status: pending → approved
   └─ poin_didapat: 30

5. System creates: poin_transaksis #1002
   ├─ sumber: 'setor_sampah'
   ├─ poin_didapat: +30
   ├─ referensi_id: 124
   └─ referensi_tipe: 'setor_sampah'

6. users table updated
   └─ total_poin: 50 → 80

CHECKPOINT: Adi has 80 poin

───────────────────────────────────────────────

📍 DAY 2: REDEEM PRODUCT
────────────────────────
Action: Adi redeems 2x botol @ 40 poin each (80 poin total)

1. penukaran_produk #456 created
   ├─ produk_id: 10 (botol)
   ├─ poin_digunakan: 80
   ├─ jumlah: 2
   ├─ status: pending → approved
   └─ tanggal_diambil: NOW (picked up)

2. System creates: poin_transaksis #1003
   ├─ sumber: 'tukar_poin'
   ├─ poin_didapat: -80 (NEGATIVE!)
   ├─ referensi_id: 456
   └─ referensi_tipe: 'penukaran_produk'

3. users table updated
   └─ total_poin: 80 → 0

CHECKPOINT: Adi has 0 poin, holds 2x botol

───────────────────────────────────────────────

📍 DAY 3: SYSTEM BONUS
──────────────────────
Action: System awards 25 poin bonus (referral promo)

1. System creates: poin_transaksis #1004
   ├─ sumber: 'bonus'
   ├─ poin_didapat: +25
   ├─ referensi_id: NULL
   └─ referensi_tipe: 'event'

2. users table updated
   └─ total_poin: 0 → 25

CHECKPOINT: Adi has 25 poin

───────────────────────────────────────────────

📍 DAY 4: WITHDRAW CASH
───────────────────────
Action: Adi withdraws 25 poin as cash (Rp 2,500)

1. penarikan_tunai #111 created
   ├─ user_id: 5
   ├─ jumlah_poin: 25
   ├─ jumlah_rupiah: 2500 (@ 1 poin = Rp 100)
   ├─ nomor_rekening: "123456789"
   ├─ nama_bank: "BCA"
   ├─ status: pending → approved
   ├─ processed_by: admin#1
   └─ processed_at: NOW

2. System transfers Rp 2,500 to BCA #123456789

3. System creates: poin_transaksis #1005
   ├─ sumber: 'manual'
   ├─ poin_didapat: -25 (NEGATIVE!)
   ├─ referensi_id: 111
   └─ referensi_tipe: 'penarikan_tunai'

4. users table updated
   └─ total_poin: 25 → 0

CHECKPOINT: Adi has 0 poin, got Rp 2,500 cash

───────────────────────────────────────────────

📋 ADI'S COMPLETE AUDIT TRAIL:

┌─────┬──────────────┬──────┬────────────────────┬──────────┐
│ ID  │ Sumber       │ Poin │ Referensi          │ Tanggal  │
├─────┼──────────────┼──────┼────────────────────┼──────────┤
│1001 │setor_sampah  │ +50  │tabung_sampah#123   │ Day 1    │
│1002 │setor_sampah  │ +30  │tabung_sampah#124   │ Day 1    │
│1003 │tukar_poin    │ -80  │penukaran_produk#456│ Day 2    │
│1004 │bonus         │ +25  │event/promo         │ Day 3    │
│1005 │manual        │ -25  │penarikan_tunai#111 │ Day 4    │
├─────┼──────────────┼──────┼────────────────────┼──────────┤
│TOTAL│              │  -0  │                    │          │
└─────┴──────────────┴──────┴────────────────────┴──────────┘

Current users.total_poin = 0

Query verification:
SELECT SUM(poin_didapat) FROM poin_transaksis WHERE user_id=5;
└─ Result: 0 ✅ (matches users.total_poin)

```

---

## 📋 **QUICK DECISION TREE**

```
❓ When to use WHICH TABLE?
════════════════════════════════════════════════════════════

User deposits waste?
└─ YES → tabung_sampah + poin_transaksis (auto)
         └─ Result: total_poin INCREASES

User redeems poin for PRODUCT?
└─ YES → penukaran_produk + poin_transaksis (auto)
         └─ Result: total_poin DECREASES

User withdraws poin as CASH?
└─ YES → penarikan_tunai + poin_transaksis (auto)
         └─ Result: total_poin DECREASES

User buys with MONEY (general order)?
└─ YES → transaksis
         └─ Result: may or may not affect total_poin

Need to AUDIT poin movements?
└─ Always use poin_transaksis
   └─ Shows sumber + referensi_id
   └─ Links to proof documents

Need to find proof for a transaction?
└─ Use poin_transaksis.referensi_id + referensi_tipe
   ├─ 'setor_sampah' → SELECT * FROM tabung_sampah WHERE id=?
   ├─ 'tukar_poin' → SELECT * FROM penukaran_produk WHERE id=?
   ├─ 'manual' → SELECT * FROM penarikan_tunai WHERE id=?
   ├─ 'badge' → SELECT * FROM user_badges WHERE id=?
   └─ 'bonus' → no proof needed (system award)

```

---

┌──────────────────────────────────────────────────────────┐
│  SESSIONS (User Sessions)                PK: id         │
├──────────────────────────────────────────────────────────┤
│  • id              VARCHAR(255) (PK)                     │
│  • user_id         BIGINT (FK) ──→ users.id             │
│  • ip_address      VARCHAR(45) (nullable)                │
│  • user_agent      TEXT (nullable)                       │
│  • payload         LONGTEXT                              │
│  • last_activity   INT (Unix timestamp)                  │
│  • created_at      TIMESTAMP                             │
│  • updated_at      TIMESTAMP                             │
│                                                          │
│  FK Cascade:                                             │
│  ← user_id → users.id (BIGINT, CASCADE DELETE)           │
│                                                          │
│  ~100 active sessions typical                            │
└──────────────────────────────────────────────────────────┘
```

---

## ⚙️ **System Support Tables**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                    SYSTEM SUPPORT INFRASTRUCTURE                           ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

┌──────────────────────────────────────────────────────────┐
│  PERSONAL_ACCESS_TOKENS (API Auth)       PK: id         │
├──────────────────────────────────────────────────────────┤
│  • id              BIGINT (PK)                           │
│  • tokenable_type  VARCHAR(255) (usually 'App\\Models\\User') │
│  • tokenable_id    BIGINT (user id - now mapped to FK)   │
│  • name            VARCHAR(255)                          │
│  • token           VARCHAR(64) (hashed token)            │
│  • abilities       JSON (permissions)                    │
│  • last_used_at    TIMESTAMP (nullable)                  │
│  • created_at      TIMESTAMP                             │
│  • updated_at      TIMESTAMP                             │
│                                                          │
│  Used for:                                               │
│  • Bearer token authentication                           │
│  • API access for mobile/web clients                     │
│  • Sanctum authentication system                         │
└──────────────────────────────────────────────────────────┘


┌──────────────────────────────────────────────────────────┐
│  CACHE (Key-Value Cache)                 PK: key        │
├──────────────────────────────────────────────────────────┤
│  • key             VARCHAR(255) (PK)                     │
│  • value           LONGTEXT                              │
│  • expiration      INT (Unix timestamp)                  │
│  • created_at      TIMESTAMP (nullable)                  │
│                                                          │
│  Used for:                                               │
│  • Session data caching                                  │
│  • Query result caching                                  │
│  • Temporary data storage                                │
│  ~1K typical entries                                     │
└──────────────────────────────────────────────────────────┘


┌──────────────────────────────────────────────────────────┐
│  CACHE_LOCKS (Cache Locking)             PK: key        │
├──────────────────────────────────────────────────────────┤
│  • key             VARCHAR(255) (PK)                     │
│  • owner           VARCHAR(255)                          │
│  • expiration      INT (Unix timestamp)                  │
│  • created_at      TIMESTAMP (nullable)                  │
│                                                          │
│  Used for:                                               │
│  • Distributed cache locking                             │
│  • Prevent race conditions                               │
│  • Concurrent request handling                           │
│  ~100 typical entries (short-lived)                      │
└──────────────────────────────────────────────────────────┘
```

---

## 🔄 **Complete Relationship Summary**

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃                    MASTER RELATIONSHIP MAP                                 ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

USERS (Central Hub - PK: no_hp - VARCHAR)
│
├─1:M─ tabung_sampah          (User deposits waste)
│      ├─1:M─ poin_transaksis (Point records for each deposit)
│      └─FK─ jadwal_penyetoran (Schedule for deposit)
│
├─1:M─ penukaran_produk       (User redeems products)
│      └─FK─ produks (Product being redeemed)
│
├─1:M─ transaksis             (User transactions)
│      ├─FK─ produks (Product in transaction)
│      └─FK─ kategori_transaksi (Type of transaction)
│
├─1:M─ penarikan_tunai        (User cash withdrawals)
│      └─FK─ users (processed_by = admin user)
│
├─1:M─ notifikasi             (Notifications for user)
│
├─1:M─ log_aktivitas          (Activity audit trail)
│
├─1:M─ badge_progress         (Progress toward badges)
│      └─FK─ badges (Badge being progressed toward)
│
├─M:M─ badges                 (Via user_badges junction)
│      └─ user_badges         (When badge was earned)
│
├─1:M─ poin_transaksis        (Point transaction ledger)
│      └─FK─ tabung_sampah (Optional - if point source is deposit)
│
├─1:M─ sessions               (User login sessions)
│
└─... (Connected to many tables via user_id foreign key)


KATEGORI_SAMPAH (Waste Categories)
│
└─1:M─ jenis_sampah           (Waste types within category)
       │
       └─1:M─ tabung_sampah   (Deposits of specific waste type)


JADWAL_PENYETORAN (Deposit Schedules)
│
└─1:M─ tabung_sampah          (Deposits follow schedule)


PRODUKS (Product Catalog)
│
├─1:M─ penukaran_produk       (Product redemptions)
│
└─1:M─ transaksis             (Product in transactions)


KATEGORI_TRANSAKSI (Transaction Types)
│
└─1:M─ transaksis             (Transactions of this type)


BADGES (Achievement Definitions)
│
├─1:M─ user_badges            (Users who earned this badge)
│
└─1:M─ badge_progress         (Progress records for this badge)


CASCADE DELETE CHAINS:
━━━━━━━━━━━━━━━━━━━━━━━

When a user is deleted (users.id deleted):
  → tabung_sampah deleted
     → poin_transaksis deleted
  → penukaran_produk deleted
  → transaksis deleted
  → penarikan_tunai deleted
  → notifikasi deleted
  → log_aktivitas deleted
  → user_badges deleted
  → badge_progress deleted
  → sessions deleted

When tabung_sampah is deleted:
  → poin_transaksis deleted

When produks is deleted:
  → penukaran_produk deleted
  → transaksis deleted

When badges is deleted:
  → user_badges deleted
  → badge_progress deleted

When kategori_transaksi is deleted:
  → transaksis deleted
```

---

## 📈 **Data Volume Estimates**

```
┌─────────────────────────────────────────────────────────────┐
│                    TYPICAL DATA VOLUMES                     │
├─────────────────────────────────────────────────────────────┤
│  users                    ~500-1000      (grows monthly)    │
│  tabung_sampah            ~5,000         (high frequency)   │
│  poin_transaksis          ~15,000        (audit trail)      │
│  transaksis               ~10,000        (transaction log)   │
│  penukaran_produk         ~2,000         (redemptions)      │
│  penarikan_tunai          ~1,000         (withdrawals)      │
│  notifikasi               ~3,000         (push notifications)│
│  log_aktivitas            ~5,000         (activity log)     │
│  user_badges              ~500           (achievement track)│
│  badge_progress           ~1,000         (progress tracking)│
│  badges                   ~20            (static definitions)│
│  produks                  ~50-100        (catalog)          │
│  jenis_sampah             ~20-50         (waste types)      │
│  kategori_sampah          ~5             (static)           │
│  kategori_transaksi       ~10            (static)           │
│  jadwal_penyetoran        ~100-200       (schedules)        │
│  artikels                 ~50-100        (content)          │
│  sessions                 ~100-500       (active)           │
│  personal_access_tokens   ~100-500       (API tokens)       │
│  cache                    ~1,000         (transient)        │
│  cache_locks              ~100           (transient)        │
│                                                             │
│  TOTAL ESTIMATED ROWS:    ~65,000-80,000                   │
│  Database Size:           ~50-100 MB typical                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 **Key Constraints & Indexes**

```
┌─────────────────────────────────────────────────────────────┐
│              CRITICAL CONSTRAINTS & INDEXES                 │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  PRIMARY KEYS:                                              │
│  • users.id (BIGINT UNSIGNED AUTO_INCREMENT)                │
│  • users.no_hp (VARCHAR UNIQUE - Business Key)              │
│  • All other tables use auto-increment BIGINT               │
│                                                             │
│  UNIQUE CONSTRAINTS:                                        │
│  • users.email (unique email per user)                      │
│  • user_badges (user_id, badge_id) - prevent duplicates    │
│  • badge_progress (user_id, badge_id) - one per user       │
│  • poin_transaksis (user_id, tabung_sampah_id, sumber)     │
│                                                             │
│  FOREIGN KEY CONSTRAINTS:                                   │
│  • 25+ foreign keys pointing to:                            │
│    - users.no_hp (9 tables)                                 │
│    - badges.id (2 tables)                                   │
│    - produks.id (2 tables)                                  │
│    - kategori_sampah.id (1 table)                           │
│    - kategori_transaksi.id (1 table)                        │
│    - jadwal_penyetoran.id (1 table)                         │
│    - tabung_sampah.id (1 table)                             │
│                                                             │
│  IMPORTANT INDEXES:                                         │
│  • users(email) - for login queries                         │
│  • tabung_sampah(user_id) - for user deposits              │
│  • transaksis(user_id, status) - for transaction queries    │
│  • poin_transaksis(user_id, created_at) - for audit trail  │
│  • poin_transaksis(user_id, sumber) - for source filtering │
│  • log_aktivitas(user_id, tanggal) - for activity queries  │
│  • badge_progress(user_id, is_unlocked) - for achievements │
│  • penukaran_produk(user_id, status) - for redemptions     │
│  • penarikan_tunai(user_id, status) - for withdrawals      │
│  • notifikasi(user_id, is_read) - for unread count         │
│                                                             │
│  CASCADE RULES:                                             │
│  • ON DELETE CASCADE (9 tables reference users)             │
│  • ON DELETE CASCADE (badges relationships)                 │
│  • ON DELETE SET NULL (penarikan_tunai.processed_by)        │
│  • ON DELETE SET NULL (poin_transaksis.tabung_sampah_id)    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔍 **Query Patterns & Performance**

```
┌─────────────────────────────────────────────────────────────┐
│           COMMON QUERY PATTERNS & OPTIMIZATION               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  FIND USER BY PHONE (Login):                                │
│  SELECT * FROM users WHERE no_hp = ?                        │
│  → PRIMARY KEY lookup (fastest)                             │
│                                                             │
│  GET USER'S TOTAL POINTS:                                   │
│  SELECT SUM(poin_didapat) FROM poin_transaksis              │
│  WHERE user_id = ? AND created_at BETWEEN ? AND ?           │
│  → Uses index on (user_id, created_at)                      │
│                                                             │
│  GET USER'S RECENT DEPOSITS:                                │
│  SELECT * FROM tabung_sampah                                │
│  WHERE user_id = ? ORDER BY created_at DESC LIMIT 10        │
│  → Uses index on user_id                                    │
│                                                             │
│  GET USER'S TRANSACTIONS WITH STATUS:                       │
│  SELECT * FROM transaksis                                   │
│  WHERE user_id = ? AND status = 'pending'                   │
│  → Uses composite index on (user_id, status)                │
│                                                             │
│  GET USER'S ACHIEVEMENT PROGRESS:                           │
│  SELECT bp.*, b.nama FROM badge_progress bp                 │
│  JOIN badges b ON bp.badge_id = b.id                        │
│  WHERE bp.user_id = ? AND bp.is_unlocked = false            │
│  → Uses index on (user_id, is_unlocked)                     │
│                                                             │
│  GET COMPLETE USER PROFILE:                                 │
│  SELECT u.*, COUNT(DISTINCT tb.id) as deposits,             │
│         COUNT(DISTINCT pp.id) as redemptions,               │
│         SUM(pt.poin_didapat) as total_points                │
│  FROM users u                                               │
│  LEFT JOIN tabung_sampah tb ON u.no_hp = tb.user_id         │
│  LEFT JOIN penukaran_produk pp ON u.no_hp = pp.user_id      │
│  LEFT JOIN poin_transaksis pt ON u.no_hp = pt.user_id       │
│  WHERE u.no_hp = ?                                           │
│  → Uses multiple indexes for joins                          │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## ✅ **ERD Summary Statistics**

```
┌──────────────────────────────────────────────────────────────┐
│                   COMPLETE ERD STATISTICS                    │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Total Tables:                          20                   │
│  Total Columns:                         ~200                 │
│  Total Foreign Keys:                    25+                  │
│  Total Relationships:                   50+                  │
│  Primary Key Types:                     3 (BIGINT, VARCHAR)  │
│  Cascade Delete Chains:                 8 major             │
│  Unique Constraints:                    12+                  │
│  Composite Indexes:                     15+                  │
│                                                              │
│  Database Normalization:                3NF (Optimized)      │
│  Average Table Size:                    3,000-10,000 rows    │
│  Estimated Total Rows:                  65,000-80,000        │
│  Estimated DB Size:                     50-100 MB            │
│                                                              │
│  Most Connected Entity:                 users (9 relations)  │
│  Most Referenced Table:                 users.id (BIGINT PK) │
│  Deepest Relationship Chain:            5 levels             │
│  Most Complex Query:                    Full user profile    │
│                                                              │
│  System Ready For:                      ✅ Production        │
│  Data Integrity:                        ✅ Strong (Cascades) │
│  Performance Optimization:              ✅ Indexed           │
│  Audit Trail:                           ✅ Complete          │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

---

## 📞 **Legend & Symbols**

```
Symbol Key:
━━━━━━━━━━

PK          = Primary Key (unique identifier)
FK          = Foreign Key (reference to another table)
1:M         = One-to-Many relationship
M:M         = Many-to-Many relationship (via junction table)
CASCADE     = Delete cascade (deletes related records)
SET NULL    = Delete sets FK to null (orphans related records)
→           = Points to / References
←           = Referenced from
UNIQUE()    = Uniqueness constraint (prevents duplicates)
INDEX()     = Database index (performance optimization)
ENUM        = Enumerated type (predefined values only)
DECIMAL     = Numeric type with fixed decimal places
VARCHAR     = Variable character string (max length)
TEXT        = Large text field (up to 65KB)
LONGTEXT    = Very large text field (up to 4GB)
TIMESTAMP   = Date and time with timezone
BOOLEAN     = True/False value
INT         = Integer number
BIGINT      = Large integer number
``
```

---

## 🎓 **How to Read This ERD**

1. **Start with USERS table** - It's the central hub of the system
2. **Follow the arrows (→)** - Shows foreign key relationships
3. **Look at CASCADE rules** - Shows what happens when data is deleted
4. **Check UNIQUE constraints** - Prevents duplicate data
5. **Review INDEXES** - Optimization for common queries
6. **Study example workflows** - See how data flows through system

---

**Generated**: November 25, 2025  
**Database**: mendaur_api  
**Status**: ✅ Production Ready  
**Last Updated**: All 20 migrations verified
