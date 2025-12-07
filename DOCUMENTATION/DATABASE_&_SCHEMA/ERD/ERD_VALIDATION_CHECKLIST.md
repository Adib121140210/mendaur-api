# 📋 ERD VALIDATION CHECKLIST - MENDAUR API
## Cross-Reference: draw.io Diagram vs DATABASE_ERD_VISUAL_DETAILED.md

**Purpose**: Validate your draw.io ERD diagram matches the official ERD documentation

**Date**: November 26, 2025  
**Status**: ✅ Ready for Validation

---

## 🎯 QUICK VALIDATION SUMMARY

Use this checklist to verify your draw.io diagram has all required:
- ✅ 20 Tables (entities)
- ✅ 25+ Foreign Key Relationships
- ✅ 12+ Unique Constraints
- ✅ Correct Primary Keys
- ✅ CASCADE/SET NULL rules
- ✅ Correct data types

---

## 📊 TABLE-BY-TABLE VALIDATION

### ✅ CORE ENTITIES (2 tables)

#### 1️⃣ USERS
- [ ] **PK**: id (BIGINT UNSIGNED AUTO_INCREMENT)
- [ ] **UNIQUE**: no_hp (VARCHAR 255) - Phone Number
- [ ] **UNIQUE**: email (VARCHAR 255) - Email
- [ ] **Fields**: 
  - [ ] id
  - [ ] no_hp ⭐ UNIQUE
  - [ ] nama (VARCHAR 255)
  - [ ] email ⭐ UNIQUE
  - [ ] password (VARCHAR 255)
  - [ ] alamat (TEXT)
  - [ ] foto_profil (VARCHAR 255)
  - [ ] total_poin (INT, default: 0)
  - [ ] total_setor_sampah (INT, default: 0)
  - [ ] level (VARCHAR 255)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)
- [ ] **Relationships** (HUB - 9 outgoing FK):
  - [ ] 1:M → tabung_sampah (CASCADE)
  - [ ] 1:M → penukaran_produk (CASCADE)
  - [ ] 1:M → transaksis (CASCADE)
  - [ ] 1:M → penarikan_tunai (CASCADE)
  - [ ] 1:M → notifikasi (CASCADE)
  - [ ] 1:M → log_aktivitas (CASCADE)
  - [ ] 1:M → badge_progress (CASCADE)
  - [ ] M:M → badges (via user_badges)
  - [ ] 1:M → poin_transaksis (CASCADE)
  - [ ] 1:M → sessions (CASCADE)

#### 2️⃣ ARTIKELS
- [ ] **PK**: id (BIGINT)
- [ ] **UNIQUE**: slug (VARCHAR 255)
- [ ] **Fields**:
  - [ ] id
  - [ ] judul (VARCHAR 255)
  - [ ] slug ⭐ UNIQUE
  - [ ] konten (LONGTEXT)
  - [ ] foto_cover (VARCHAR 255, nullable)
  - [ ] penulis (VARCHAR 255)
  - [ ] kategori (VARCHAR 255)
  - [ ] tanggal_publikasi (DATE)
  - [ ] views (INT, default: 0)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)
- [ ] **Relationships**: None (Standalone)

---

### ✅ WASTE MANAGEMENT SYSTEM (4 tables)

#### 3️⃣ KATEGORI_SAMPAH
- [ ] **PK**: id (BIGINT)
- [ ] **Fields**:
  - [ ] id
  - [ ] nama_kategori (VARCHAR 255)
  - [ ] deskripsi (TEXT)
  - [ ] icon (VARCHAR 255)
  - [ ] warna (VARCHAR 255)
  - [ ] is_active (BOOLEAN)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)
- [ ] **Relationships**:
  - [ ] 1:M → jenis_sampah (CASCADE)

#### 4️⃣ JENIS_SAMPAH
- [ ] **PK**: id (BIGINT)
- [ ] **FK**: kategori_sampah_id → kategori_sampah.id (CASCADE)
- [ ] **Fields**:
  - [ ] id
  - [ ] kategori_sampah_id ⭐ FK (CASCADE)
  - [ ] nama_jenis (VARCHAR 100)
  - [ ] harga_per_kg (DECIMAL 10,2)
  - [ ] satuan (VARCHAR 20, default: 'kg')
  - [ ] kode (VARCHAR 20, UNIQUE)
  - [ ] is_active (BOOLEAN, default: true)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)

#### 5️⃣ JADWAL_PENYETORAN
- [ ] **PK**: id (BIGINT)
- [ ] **Fields**:
  - [ ] id
  - [ ] tanggal (DATE)
  - [ ] waktu_mulai (TIME)
  - [ ] waktu_selesai (TIME)
  - [ ] lokasi (VARCHAR 255)
  - [ ] kapasitas (INT, default: 100)
  - [ ] status (ENUM)
  - [ ] created_at (TIMESTAMP)
- [ ] **Relationships**:
  - [ ] 1:M ← tabung_sampah

#### 6️⃣ TABUNG_SAMPAH
- [ ] **PK**: id (BIGINT)
- [ ] **FK**: user_id → users.id (CASCADE)
- [ ] **FK**: jadwal_id → jadwal_penyetoran.id
- [ ] **Fields**:
  - [ ] id
  - [ ] user_id ⭐ FK (CASCADE)
  - [ ] jadwal_id ⭐ FK
  - [ ] nama_lengkap (VARCHAR 255)
  - [ ] no_hp (VARCHAR 255)
  - [ ] titik_lokasi (TEXT)
  - [ ] jenis_sampah (VARCHAR 255) - Not FK!
  - [ ] berat_kg (DECIMAL 6,2)
  - [ ] foto_sampah (TEXT, nullable)
  - [ ] status (ENUM: pending/approved/rejected)
  - [ ] poin_didapat (INT)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)
- [ ] **Relationships**:
  - [ ] 1:M → poin_transaksis (CASCADE)

---

### ✅ PRODUCT & REDEMPTION SYSTEM (3 tables)

#### 7️⃣ PRODUKS
- [ ] **PK**: id (BIGINT)
- [ ] **Fields**:
  - [ ] id
  - [ ] nama (VARCHAR 255)
  - [ ] deskripsi (TEXT)
  - [ ] harga (DECIMAL 15,2)
  - [ ] poin_diperlukan (INT)
  - [ ] stok (INT)
  - [ ] kategori (VARCHAR 255)
  - [ ] foto (VARCHAR 255)
  - [ ] status (ENUM)
  - [ ] created_at (TIMESTAMP)
- [ ] **Relationships**:
  - [ ] 1:M → penukaran_produk (CASCADE)
  - [ ] 1:M → transaksis (CASCADE)

#### 8️⃣ PENUKARAN_PRODUK
- [ ] **PK**: id (BIGINT)
- [ ] **FK**: user_id → users.id (CASCADE)
- [ ] **FK**: produk_id → produks.id (CASCADE)
- [ ] **Fields**:
  - [ ] id
  - [ ] user_id ⭐ FK (CASCADE)
  - [ ] produk_id ⭐ FK (CASCADE)
  - [ ] nama_produk (VARCHAR 255)
  - [ ] poin_digunakan (INT)
  - [ ] jumlah (INT, default: 1)
  - [ ] status (ENUM)
  - [ ] metode_ambil (TEXT)
  - [ ] catatan (TEXT, nullable)
  - [ ] tanggal_penukaran (TIMESTAMP)
  - [ ] tanggal_diambil (TIMESTAMP, nullable)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)

#### 9️⃣ KATEGORI_TRANSAKSI
- [ ] **PK**: id (BIGINT)
- [ ] **Fields**:
  - [ ] id
  - [ ] nama (VARCHAR 255)
  - [ ] deskripsi (TEXT, nullable)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)
- [ ] **Relationships**:
  - [ ] 1:M → transaksis (CASCADE)

---

### ✅ TRANSACTION & CASH SYSTEM (3 tables)

#### 🔟 TRANSAKSIS
- [ ] **PK**: id (BIGINT)
- [ ] **FK**: user_id → users.id (CASCADE)
- [ ] **FK**: produk_id → produks.id (CASCADE)
- [ ] **FK**: kategori_id → kategori_transaksi.id (CASCADE)
- [ ] **Fields**:
  - [ ] id
  - [ ] user_id ⭐ FK (CASCADE)
  - [ ] produk_id ⭐ FK (CASCADE)
  - [ ] kategori_id ⭐ FK (CASCADE)
  - [ ] jumlah (INT)
  - [ ] total_poin (INT)
  - [ ] status (ENUM)
  - [ ] metode_pengiriman (VARCHAR 255, nullable)
  - [ ] alamat_pengiriman (TEXT, nullable)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)

#### 1️⃣1️⃣ PENARIKAN_TUNAI
- [ ] **PK**: id (BIGINT)
- [ ] **FK**: user_id → users.id (CASCADE)
- [ ] **FK**: processed_by → users.id (SET NULL)
- [ ] **Fields**:
  - [ ] id
  - [ ] user_id ⭐ FK (CASCADE)
  - [ ] jumlah_poin (INT)
  - [ ] jumlah_rupiah (DECIMAL 15,2)
  - [ ] nomor_rekening (VARCHAR 50)
  - [ ] nama_bank (VARCHAR 100)
  - [ ] nama_penerima (VARCHAR 255)
  - [ ] status (ENUM)
  - [ ] catatan_admin (TEXT, nullable)
  - [ ] processed_by ⭐ FK (SET NULL)
  - [ ] processed_at (TIMESTAMP, nullable)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)

---

### ✅ GAMIFICATION SYSTEM (4 tables)

#### 1️⃣2️⃣ BADGES
- [ ] **PK**: id (BIGINT)
- [ ] **Fields**:
  - [ ] id
  - [ ] nama (VARCHAR 255)
  - [ ] deskripsi (TEXT)
  - [ ] icon (VARCHAR 255)
  - [ ] syarat_poin (INT, default: 0)
  - [ ] syarat_setor (INT, default: 0)
  - [ ] reward_poin (INT, default: 0)
  - [ ] tipe (ENUM: poin/setor/kombinasi/special/ranking)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)
- [ ] **Relationships**:
  - [ ] 1:M → user_badges (CASCADE)
  - [ ] 1:M → badge_progress (CASCADE)

#### 1️⃣3️⃣ USER_BADGES
- [ ] **PK**: id (BIGINT)
- [ ] **FK**: user_id → users.id (CASCADE)
- [ ] **FK**: badge_id → badges.id (CASCADE)
- [ ] **UNIQUE**: (user_id, badge_id)
- [ ] **Fields**:
  - [ ] id
  - [ ] user_id ⭐ FK (CASCADE)
  - [ ] badge_id ⭐ FK (CASCADE)
  - [ ] tanggal_dapat (TIMESTAMP)
  - [ ] reward_claimed (BOOLEAN, default: true)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)

#### 1️⃣4️⃣ BADGE_PROGRESS
- [ ] **PK**: id (BIGINT)
- [ ] **FK**: user_id → users.id (CASCADE)
- [ ] **FK**: badge_id → badges.id (CASCADE)
- [ ] **UNIQUE**: (user_id, badge_id)
- [ ] **Fields**:
  - [ ] id
  - [ ] user_id ⭐ FK (CASCADE)
  - [ ] badge_id ⭐ FK (CASCADE)
  - [ ] current_value (INT, default: 0)
  - [ ] target_value (INT, default: 0)
  - [ ] progress_percentage (DECIMAL 5,2, 0-100)
  - [ ] is_unlocked (BOOLEAN, default: false)
  - [ ] unlocked_at (TIMESTAMP, nullable)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)

---

### ✅ POINT & AUDIT SYSTEM (1 table)

#### 1️⃣5️⃣ POIN_TRANSAKSIS
- [ ] **PK**: id (BIGINT)
- [ ] **FK**: user_id → users.id (CASCADE)
- [ ] **FK**: tabung_sampah_id → tabung_sampah.id (SET NULL)
- [ ] **UNIQUE**: (user_id, tabung_sampah_id, sumber)
- [ ] **Fields**:
  - [ ] id
  - [ ] user_id ⭐ FK (CASCADE)
  - [ ] tabung_sampah_id ⭐ FK (SET NULL)
  - [ ] jenis_sampah (VARCHAR 255, nullable)
  - [ ] berat_kg (DECIMAL 6,2, nullable)
  - [ ] poin_didapat (INT) - Can be negative!
  - [ ] sumber (VARCHAR 255)
  - [ ] keterangan (TEXT, nullable)
  - [ ] referensi_id (BIGINT, nullable)
  - [ ] referensi_tipe (VARCHAR 255, nullable)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)

---

### ✅ NOTIFICATION & LOGGING (2 tables)

#### 1️⃣6️⃣ NOTIFIKASI
- [ ] **PK**: id (BIGINT)
- [ ] **FK**: user_id → users.id (CASCADE)
- [ ] **Fields**:
  - [ ] id
  - [ ] user_id ⭐ FK (CASCADE)
  - [ ] judul (VARCHAR 255)
  - [ ] pesan (TEXT)
  - [ ] tipe (VARCHAR 255)
  - [ ] is_read (BOOLEAN, default: false)
  - [ ] related_id (BIGINT, nullable)
  - [ ] related_type (VARCHAR 255, nullable)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)

#### 1️⃣7️⃣ LOG_AKTIVITAS
- [ ] **PK**: id (BIGINT)
- [ ] **FK**: user_id → users.id (CASCADE)
- [ ] **Fields**:
  - [ ] id
  - [ ] user_id ⭐ FK (CASCADE)
  - [ ] tipe_aktivitas (VARCHAR 50)
  - [ ] deskripsi (TEXT, nullable)
  - [ ] poin_perubahan (INT, default: 0)
  - [ ] tanggal (TIMESTAMP)
  - [ ] created_at (TIMESTAMP)

---

### ✅ CONTENT & SESSIONS (2 tables)

#### 1️⃣8️⃣ SESSIONS
- [ ] **PK**: id (VARCHAR 255)
- [ ] **FK**: user_id → users.id (CASCADE)
- [ ] **Fields**:
  - [ ] id
  - [ ] user_id ⭐ FK (CASCADE)
  - [ ] ip_address (VARCHAR 45, nullable)
  - [ ] user_agent (TEXT, nullable)
  - [ ] payload (LONGTEXT)
  - [ ] last_activity (INT)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)

---

### ✅ SYSTEM SUPPORT (2 tables)

#### 1️⃣9️⃣ PERSONAL_ACCESS_TOKENS
- [ ] **PK**: id (BIGINT)
- [ ] **Fields**:
  - [ ] id
  - [ ] tokenable_type (VARCHAR 255)
  - [ ] tokenable_id (BIGINT)
  - [ ] name (VARCHAR 255)
  - [ ] token (VARCHAR 64, UNIQUE)
  - [ ] abilities (JSON)
  - [ ] last_used_at (TIMESTAMP, nullable)
  - [ ] created_at (TIMESTAMP)
  - [ ] updated_at (TIMESTAMP)

#### 2️⃣0️⃣ CACHE (Support tables)
- [ ] **PK**: key (VARCHAR 255)
- [ ] **Fields**:
  - [ ] key
  - [ ] value (LONGTEXT)
  - [ ] expiration (INT)
  - [ ] created_at (TIMESTAMP, nullable)

---

## 🔗 FOREIGN KEY RELATIONSHIPS CHECKLIST

### Total FKs to Validate: 25+

#### From USERS (9 outgoing):
- [ ] users.id → tabung_sampah.user_id (CASCADE)
- [ ] users.id → penukaran_produk.user_id (CASCADE)
- [ ] users.id → transaksis.user_id (CASCADE)
- [ ] users.id → penarikan_tunai.user_id (CASCADE)
- [ ] users.id → penarikan_tunai.processed_by (SET NULL)
- [ ] users.id → notifikasi.user_id (CASCADE)
- [ ] users.id → log_aktivitas.user_id (CASCADE)
- [ ] users.id → badge_progress.user_id (CASCADE)
- [ ] users.id → user_badges.user_id (CASCADE)
- [ ] users.id → poin_transaksis.user_id (CASCADE)
- [ ] users.id → sessions.user_id (CASCADE)

#### From KATEGORI_SAMPAH:
- [ ] kategori_sampah.id → jenis_sampah.kategori_sampah_id (CASCADE)

#### From JENIS_SAMPAH:
- [ ] (No outgoing FKs besides kategori_sampah)

#### From JADWAL_PENYETORAN:
- [ ] jadwal_penyetoran.id → tabung_sampah.jadwal_id

#### From TABUNG_SAMPAH:
- [ ] tabung_sampah.id → poin_transaksis.tabung_sampah_id (SET NULL)

#### From PRODUKS:
- [ ] produks.id → penukaran_produk.produk_id (CASCADE)
- [ ] produks.id → transaksis.produk_id (CASCADE)

#### From KATEGORI_TRANSAKSI:
- [ ] kategori_transaksi.id → transaksis.kategori_id (CASCADE)

#### From BADGES:
- [ ] badges.id → user_badges.badge_id (CASCADE)
- [ ] badges.id → badge_progress.badge_id (CASCADE)

---

## ✨ UNIQUE CONSTRAINTS CHECKLIST

- [ ] users.no_hp (UNIQUE) - Business Key
- [ ] users.email (UNIQUE)
- [ ] jenis_sampah.kode (UNIQUE)
- [ ] artikels.slug (UNIQUE)
- [ ] personal_access_tokens.token (UNIQUE)
- [ ] user_badges (UNIQUE: user_id, badge_id)
- [ ] badge_progress (UNIQUE: user_id, badge_id)
- [ ] poin_transaksis (UNIQUE: user_id, tabung_sampah_id, sumber)

---

## 🎯 CASCADE DELETE VALIDATION

Verify these CASCADE DELETE chains work:

```
When users.id deleted:
  ✓ tabung_sampah deleted
    ✓ poin_transaksis deleted
  ✓ penukaran_produk deleted
  ✓ transaksis deleted
  ✓ penarikan_tunai deleted (cascade on user_id, SET NULL on processed_by)
  ✓ notifikasi deleted
  ✓ log_aktivitas deleted
  ✓ user_badges deleted
  ✓ badge_progress deleted
  ✓ sessions deleted

When tabung_sampah deleted:
  ✓ poin_transaksis deleted (or SET NULL on tabung_sampah_id)

When produks deleted:
  ✓ penukaran_produk deleted
  ✓ transaksis deleted

When badges deleted:
  ✓ user_badges deleted
  ✓ badge_progress deleted
```

- [ ] All CASCADE rules correct
- [ ] SET NULL only on processed_by (penarikan_tunai)
- [ ] SET NULL on tabung_sampah_id (poin_transaksis)

---

## 📊 DATA TYPE VALIDATION

### INT vs BIGINT
- [ ] user_id fields: BIGINT ✓
- [ ] badge_id fields: BIGINT ✓
- [ ] poin_didapat: INT (can be negative!)
- [ ] total_poin: INT
- [ ] progress_percentage: DECIMAL(5,2)

### VARCHAR SIZES
- [ ] no_hp: VARCHAR(255) ✓
- [ ] email: VARCHAR(255) ✓
- [ ] nama: VARCHAR(255) ✓
- [ ] slug: VARCHAR(255) ✓
- [ ] sumber: VARCHAR(255) ✓

### TEXT vs LONGTEXT
- [ ] konten (artikels): LONGTEXT ✓
- [ ] payload (sessions): LONGTEXT ✓
- [ ] Short descriptions: TEXT ✓

### TIMESTAMP vs DATE
- [ ] tanggal_publikasi (artikels): DATE
- [ ] tanggal_penukaran: TIMESTAMP
- [ ] created_at/updated_at: TIMESTAMP

---

## 🏆 RELATIONSHIP TYPES VALIDATION

### One-to-Many (1:M)
- [ ] users:tabung_sampah (1:M)
- [ ] users:penukaran_produk (1:M)
- [ ] users:transaksis (1:M)
- [ ] kategori_sampah:jenis_sampah (1:M)
- [ ] produks:penukaran_produk (1:M)
- [ ] produks:transaksis (1:M)
- [ ] badges:user_badges (1:M)
- [ ] badges:badge_progress (1:M)
- [ ] Total 1:M relationships: 20+

### Many-to-Many (M:M)
- [ ] users:badges (M:M via user_badges junction)

### Self-Referencing
- [ ] penarikan_tunai.processed_by → users.id

---

## 🔍 SPECIAL FIELDS VALIDATION

### Fields that are NOT Foreign Keys (Important!)
- [ ] tabung_sampah.jenis_sampah (VARCHAR - not FK!)
- [ ] poin_transaksis.referensi_id (Polymorphic reference, not FK)
- [ ] poin_transaksis.referensi_tipe (Polymorphic reference type)

### Fields with Special Constraints
- [ ] poin_didapat: Can be NEGATIVE (INT, not UINT)
- [ ] progress_percentage: DECIMAL(5,2) for 0-100% precision
- [ ] is_unlocked: BOOLEAN (0/1)
- [ ] is_read: BOOLEAN (0/1)
- [ ] is_active: BOOLEAN (0/1)

### Nullable Fields (nullable = true)
- [ ] foto_profil (users) - nullable
- [ ] foto_sampah (tabung_sampah) - nullable
- [ ] foto (produks) - nullable
- [ ] konten (poin_transaksis) - nullable
- [ ] And 20+ more...

---

## 📈 PERFORMANCE VALIDATION

### Critical Indexes
- [ ] users(email) - for login
- [ ] users(no_hp) - for phone lookup
- [ ] tabung_sampah(user_id) - for user deposits
- [ ] poin_transaksis(user_id, created_at) - for audit trail
- [ ] poin_transaksis(user_id, sumber) - for source filtering
- [ ] badge_progress(user_id, is_unlocked) - for achievements
- [ ] badge_progress(badge_id, is_unlocked) - for badge popularity

### Query Optimization
- [ ] All FKs have indexes
- [ ] UNIQUE constraints have indexes
- [ ] Composite indexes for common query patterns

---

## ✅ FINAL VALIDATION CHECKLIST

### Before Approval, Verify:

**Structure** (Required):
- [ ] All 20 tables present
- [ ] All table names exact match
- [ ] All column names exact match
- [ ] All data types correct

**Relationships** (Critical):
- [ ] All 25+ foreign keys present
- [ ] All CASCADE rules correct
- [ ] All SET NULL rules correct
- [ ] No extra relationships

**Constraints** (Security):
- [ ] All UNIQUE constraints present
- [ ] BIGINT on all PKs (except exceptions)
- [ ] NOT NULL on required fields
- [ ] Defaults on optional fields

**Documentation** (Professional):
- [ ] Labels clear & readable
- [ ] Foreign keys clearly marked
- [ ] Cascade rules labeled
- [ ] Legend provided

**Overall Quality**:
- [ ] Diagram readable & organized
- [ ] Color-coded by system (waste, product, gamification)
- [ ] Tables grouped logically
- [ ] Relationships clear without crossing

---

## 🚀 NEXT STEPS

1. **Open Your draw.io Diagram**
   - Export or screenshot the diagram
   - Share with your team

2. **Use This Checklist**
   - Go through each section
   - Mark off items as you verify
   - Note any discrepancies

3. **Compare & Sync**
   - If differences found, update draw.io OR DATABASE_ERD_VISUAL_DETAILED.md
   - Ensure single source of truth
   - Document any intentional differences

4. **Final Approval**
   - Once all items checked ✓
   - Diagram is production-ready
   - Use for team documentation

---

## 📞 QUICK REFERENCE - 20 TABLES LIST

1. USERS
2. ARTIKELS
3. KATEGORI_SAMPAH
4. JENIS_SAMPAH
5. JADWAL_PENYETORAN
6. TABUNG_SAMPAH
7. PRODUKS
8. PENUKARAN_PRODUK
9. KATEGORI_TRANSAKSI
10. TRANSAKSIS
11. PENARIKAN_TUNAI
12. BADGES
13. USER_BADGES
14. BADGE_PROGRESS
15. POIN_TRANSAKSIS
16. NOTIFIKASI
17. LOG_AKTIVITAS
18. SESSIONS
19. PERSONAL_ACCESS_TOKENS
20. CACHE (+ CACHE_LOCKS)

**Total**: 20 Main Tables + 2 Support Tables = 22 Total

---

**Status**: ✅ Ready to Validate  
**Last Updated**: November 26, 2025  
**Validator**: GitHub Copilot

Use this document to ensure your draw.io ERD matches the production database schema exactly!
