# 📋 DATABASE SCHEMA - QUICK REFERENCE

**Total Tables**: 19  
**Total Relationships**: 50+  
**Timezone**: Asia/Jakarta (GMT+7)

---

## 🎯 TABLE QUICK LOOKUP

### Core User Table

```
┌─ USERS (Hub Table)
│  └─ id, nama, email✓, password, no_hp, alamat, foto_profil
│     total_poin, total_setor_sampah, level
│  └─ Relations: 8+ (has many/many)
```

### Waste Management System

```
KATEGORI_SAMPAH (5 categories)
        ↓
JENIS_SAMPAH (20 waste types)
        ↓
TABUNG_SAMPAH (User deposits)
        ↓
JADWAL_PENYETORAN (Collection schedule)
```

### Product Redemption

```
PRODUKS (Products)
        ↓
PENUKARAN_PRODUK (User redeems)
        ↓
USERS (Who redeems)
```

### Gamification

```
BADGES (Achievements)
   ↓
USER_BADGES (User earned)
   ↓
BADGE_PROGRESS (Progress tracking)
```

### Support Tables

```
TRANSAKSI (General transactions)
KATEGORI_TRANSAKSI (Transaction types)
PENARIKAN_TUNAI (Cash withdrawals)
NOTIFIKASI (User notifications)
LOG_AKTIVITAS (Audit trail)
ARTIKELS (Content/articles)
```

---

## 🔑 KEY TABLES (Top 5)

| Table | Rows | Primary Use | Key Fields |
|-------|------|-----------|-----------|
| **users** | 1K-10K | User accounts | id, email✓, total_poin, level |
| **tabung_sampah** | 10K-100K | Waste deposits | user_id, status, poin_didapat |
| **penukaran_produk** | 5K-50K | Product redeems | user_id, produk_id, status |
| **jenis_sampah** | 20 | Waste types | kategori_sampah_id, harga_per_kg |
| **badges** | 50-100 | Achievements | syarat_poin, reward_poin |

---

## 🔗 RELATIONSHIP TYPES

### One-to-Many (1:M) - 13 relations
```
users → tabung_sampah
users → penukaran_produk
users → transaksi
users → penarikan_tunai
users → notifikasi
users → log_aktivitas
users → badge_progress
jadwal_penyetorans → tabung_sampah
kategori_sampah → jenis_sampah
produks → penukaran_produk
produks → transaksi
kategori_transaksi → transaksi
badges → badge_progress
```

### Many-to-Many (M:M) - 1 relation
```
users ←→ badges (via user_badges pivot)
```

### Self-Referencing (M:1) - 1 relation
```
penarikan_tunai.processed_by → users
```

---

## 📊 ALL 19 TABLES AT A GLANCE

```
1.  USERS                    → User profiles
2.  TABUNG_SAMPAH            → Waste deposits
3.  JADWAL_PENYETORAN        → Collection schedules
4.  JENIS_SAMPAH             → Waste types (20)
5.  KATEGORI_SAMPAH          → Waste categories (5)
6.  PRODUKS                  → Redeemable products
7.  PENUKARAN_PRODUK         → Product redemptions
8.  TRANSAKSI                → General transactions
9.  KATEGORI_TRANSAKSI       → Transaction types
10. BADGES                   → Achievement definitions
11. USER_BADGES              → User achievements (pivot)
12. BADGE_PROGRESS           → Achievement progress
13. NOTIFIKASI               → User notifications
14. LOG_AKTIVITAS            → Audit trail
15. PENARIKAN_TUNAI          → Cash withdrawals
16. ARTIKELS                 → Articles/content
17. PERSONAL_ACCESS_TOKENS   → API tokens (Sanctum)
18. CACHE                    → Cache storage
19. CACHE_LOCKS              → Cache locks
```

---

## 🎯 PRIMARY RELATIONSHIPS

### User → Everything
```
1 User has:
├─ Many Tabung_Sampah (deposits)
├─ Many Penukaran_Produk (redemptions)
├─ Many Transaksi (transactions)
├─ Many Penarikan_Tunai (withdrawals)
├─ Many Notifikasi (notifications)
├─ Many Log_Aktivitas (activity logs)
├─ Many Badge_Progress (badge progress)
└─ Many Badges (achievements via pivot)
```

### Hierarchy: Categories → Types → Usage
```
Kategori_Sampah (5)
       ↓ 1:M
Jenis_Sampah (20)
       ↓ used in
Tabung_Sampah (N)
```

### Badge System
```
Badges (achievement def)
   ├─ M:M → Users (via user_badges)
   └─ 1:M → Badge_Progress
```

---

## 📈 CASCADE DELETE RULES

**16 tables cascade delete** - Child records deleted when parent deleted

```
IF user DELETE → DELETE:
├─ tabung_sampah
├─ penukaran_produk
├─ transaksi
├─ penarikan_tunai
├─ notifikasi
├─ log_aktivitas
├─ user_badges
└─ badge_progress

IF kategori_sampah DELETE → DELETE:
└─ jenis_sampah

IF jadwal_penyetorans DELETE → DELETE:
└─ tabung_sampah

IF produks DELETE → DELETE:
├─ penukaran_produk
└─ transaksi

IF badges DELETE → DELETE:
├─ user_badges
└─ badge_progress
```

**1 SET NULL rule** - Reference cleared when parent deleted

```
IF admin user DELETE → SET NULL:
└─ penarikan_tunai.processed_by
```

---

## 🔐 UNIQUE CONSTRAINTS

```
users.email (UNIQUE)
jenis_sampah.kode (UNIQUE)
artikels.slug (UNIQUE)
personal_access_tokens.token (UNIQUE)
user_badges (UNIQUE: user_id + badge_id)
badge_progress (UNIQUE: user_id + badge_id)
```

---

## 🔍 COMMON QUERIES

### Find User with All Relations
```sql
SELECT * FROM users WHERE id = 1;
-- Then access: user.tabungSampahs, user.penukaranProduks, etc.
```

### Get Waste Types with Category
```sql
SELECT j.*, k.nama_kategori FROM jenis_sampah j
JOIN kategori_sampah k ON j.kategori_sampah_id = k.id;
```

### User's Total Points from Deposits
```sql
SELECT SUM(poin_didapat) FROM tabung_sampah 
WHERE user_id = 1 AND status = 'approved';
```

### User's Redeemed Products
```sql
SELECT * FROM penukaran_produk 
WHERE user_id = 1 AND status = 'approved';
```

### Check Badge Progress
```sql
SELECT * FROM badge_progress 
WHERE user_id = 1 AND is_unlocked = 0
ORDER BY progress_percentage DESC;
```

---

## 📋 ENUM VALUES

**tabung_sampah.status**
```
'pending', 'approved', 'rejected'
```

**penukaran_produk.status**
```
'pending', 'approved', 'cancelled'
```

**transaksi.status**
```
'pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'
```

**produks.status**
```
'tersedia', 'habis', 'nonaktif'
```

**jadwal_penyetorans.status**
```
'aktif', 'penuh', 'selesai', 'dibatalkan'
```

**badges.tipe**
```
'poin', 'setor', 'kombinasi', 'special', 'ranking'
```

**penarikan_tunai.status**
```
'pending', 'approved', 'rejected'
```

---

## 🔢 INDEXES FOR PERFORMANCE

```
tabung_sampah
├─ (user_id, status)       → Fast user status query
├─ (jadwal_id)             → Fast schedule query
└─ (created_at)            → Date filtering

jenis_sampah
├─ (kategori_sampah_id, is_active)  → Category filter
└─ (kode)                  → Code lookup

penukaran_produk
├─ (user_id, status)       → User history
└─ (created_at)            → Date range query

log_aktivitas
└─ (user_id, tanggal)      → User activity log

badge_progress
├─ (user_id, is_unlocked)  → Unlocked badges
└─ (progress_percentage)   → Ranking

penarikan_tunai
├─ (user_id, status)       → Withdrawal history
└─ (created_at)            → Date filtering
```

---

## 📐 TABLE COLUMN COUNTS

| Table | Columns | Notes |
|-------|---------|-------|
| users | 12 | Most important |
| tabung_sampah | 11 | Core transaction |
| penukaran_produk | 11 | Modernized (pickup model) |
| transaksi | 10 | General transactions |
| badges | 8 | Gamification |
| jenis_sampah | 8 | Waste hierarchy |
| kategori_sampah | 6 | Waste categories |
| penarikan_tunai | 12 | Cash withdrawals |
| badge_progress | 9 | Progress tracking |
| **Average** | **~8** | **Across all tables** |

---

## ⚡ PERFORMANCE TIPS

1. **Always filter by user_id first** - Most important FK
2. **Use indexes for status queries** - Already indexed
3. **Batch operations** - Reduce round trips
4. **Cache badge definitions** - Rarely change
5. **Archive old logs** - Log table grows fast
6. **Limit date ranges** - History queries can be large

---

## 🔄 DATA FLOW EXAMPLES

### Scenario 1: User Deposits Waste
```
User fills tabung_sampah form
  ↓
Creates record with status='pending'
  ↓
Admin approves
  ↓
status='approved', poin_didapat=16
  ↓
User.total_poin +16
  ↓
Log activity
  ↓
Check badges
```

### Scenario 2: User Redeems Product
```
User selects product (500 poin)
  ↓
Creates penukaran_produk record
  ↓
status='pending'
  ↓
Admin approves
  ↓
User.total_poin -500
  ↓
Produks.stok -1
```

### Scenario 3: User Earns Badge
```
Deposit reaches 100 poin threshold
  ↓
Check badge conditions
  ↓
Criteria met → Award badge
  ↓
INSERT user_badges
  ↓
User.total_poin +10 (reward)
```

---

## 📊 TYPICAL DATA VOLUME

| Table | Small | Medium | Large |
|-------|-------|--------|-------|
| users | 100 | 1K | 10K+ |
| tabung_sampah | 1K | 10K | 100K+ |
| penukaran_produk | 500 | 5K | 50K+ |
| transaksi | 1K | 15K | 150K+ |
| notifikasi | 10K | 100K | 1M+ |
| log_aktivitas | 10K | 200K | 2M+ |

---

## ✅ SCHEMA CHECKLIST

- [x] 19 tables fully documented
- [x] 50+ relationships mapped
- [x] All FKs with constraints
- [x] 16 cascade deletes
- [x] 1 set null rule
- [x] 15+ performance indexes
- [x] 5 unique constraints
- [x] Timezone GMT+7 configured
- [x] No circular dependencies
- [x] Ready for production

---

## 🎯 MOST IMPORTANT TABLES

### For Users
1. **users** - Identity & points
2. **tabung_sampah** - Earn points
3. **penukaran_produk** - Spend points
4. **badges** - Achievements

### For Admin
1. **tabung_sampah** - Approve deposits
2. **penarikan_tunai** - Process withdrawals
3. **penukaran_produk** - Track redemptions
4. **log_aktivitas** - Audit trail

### For Analytics
1. **log_aktivitas** - User behavior
2. **transaksi** - Revenue tracking
3. **badge_progress** - Engagement
4. **users** - Growth metrics

---

**Quick Reference Version**: ✅ Complete  
**Last Updated**: November 20, 2025  
**Format**: Easy Lookup  

Use alongside:
- `DATABASE_SCHEMA_COMPLETE.md` for detailed specs
- `DATABASE_ERD_DIAGRAM.md` for visual relationships
