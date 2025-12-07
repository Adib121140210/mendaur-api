# 🔄 draw.io ERD SYNCHRONIZATION GUIDE
## Menyecocokkan diagram draw.io dengan DATABASE_ERD_VISUAL_DETAILED.md

**Tujuan**: Pastikan diagram visual draw.io 100% sesuai dengan dokumentasi resmi ERD  
**Status**: Ready for Sync  
**Tanggal**: November 26, 2025

---

## 📋 PANDUAN SINKRONISASI

### Step 1: IDENTIFIKASI PERBEDAAN

Buka **kedua file** secara bersamaan:
- **File 1** (Draw.io): Mendaur-Physical-ERD.drawio.html (diagram visual)
- **File 2** (Dokumentasi): DATABASE_ERD_VISUAL_DETAILED.md (definisi resmi)

Tujuan: Temukan area yang berbeda antara keduanya.

---

## 🎯 PRIORITAS SINKRONISASI (Urutan Penting)

### Priority 1: CRITICAL (Must Match 100%)
Jika ada perbedaan di sini, database akan RUSAK!

#### 1. Table Names & Primary Keys
| No | Table | PK | Status | Notes |
|----|-----------|----|--------|-------|
| 1 | users | id (BIGINT) | Check | ✓ Harus BIGINT, not INT |
| 2 | artikels | id (BIGINT) | Check | - |
| 3 | kategori_sampah | id (BIGINT) | Check | - |
| 4 | jenis_sampah | id (BIGINT) | Check | - |
| 5 | jadwal_penyetoran | id (BIGINT) | Check | - |
| 6 | tabung_sampah | id (BIGINT) | Check | - |
| 7 | produks | id (BIGINT) | Check | - |
| 8 | penukaran_produk | id (BIGINT) | Check | - |
| 9 | kategori_transaksi | id (BIGINT) | Check | - |
| 10 | transaksis | id (BIGINT) | Check | - |
| 11 | penarikan_tunai | id (BIGINT) | Check | - |
| 12 | badges | id (BIGINT) | Check | - |
| 13 | user_badges | id (BIGINT) | Check | ✓ UNIQUE(user_id, badge_id) |
| 14 | badge_progress | id (BIGINT) | Check | ✓ UNIQUE(user_id, badge_id) |
| 15 | poin_transaksis | id (BIGINT) | Check | ✓ UNIQUE(user_id, tbs_id, sumber) |
| 16 | notifikasi | id (BIGINT) | Check | - |
| 17 | log_aktivitas | id (BIGINT) | Check | - |
| 18 | sessions | id (VARCHAR 255) | Check | ⚠️ Berbeda! VARCHAR, bukan BIGINT |
| 19 | personal_access_tokens | id (BIGINT) | Check | - |
| 20 | cache | key (VARCHAR 255) | Check | - |

**Action**: Jika ada yang berbeda, update draw.io diagram.

#### 2. Business Keys & UNIQUE Constraints

| Table | Field | Constraint | Status |
|-------|-------|------------|--------|
| users | no_hp | UNIQUE | Check |
| users | email | UNIQUE | Check |
| jenis_sampah | kode | UNIQUE | Check |
| artikels | slug | UNIQUE | Check |
| user_badges | (user_id, badge_id) | UNIQUE | Check |
| badge_progress | (user_id, badge_id) | UNIQUE | Check |
| poin_transaksis | (user_id, tbs_id, sumber) | UNIQUE | Check |
| personal_access_tokens | token | UNIQUE | Check |

**Action**: Setiap field UNIQUE harus ditandai jelas di diagram.

#### 3. Foreign Key Relationships (25+ Total)

**Users Relationships** (11 FKs dari users):
```
users → tabung_sampah.user_id          (CASCADE)
users → penukaran_produk.user_id       (CASCADE)
users → transaksis.user_id             (CASCADE)
users → penarikan_tunai.user_id        (CASCADE)
users → penarikan_tunai.processed_by   (SET NULL) ⚠️ PENTING!
users → notifikasi.user_id             (CASCADE)
users → log_aktivitas.user_id          (CASCADE)
users → badge_progress.user_id         (CASCADE)
users → user_badges.user_id            (CASCADE)
users → poin_transaksis.user_id        (CASCADE)
users → sessions.user_id               (CASCADE)
```

**Checklist**:
- [ ] Semua 11 FK dari users ada?
- [ ] CASCADE rules benar?
- [ ] processed_by ada & SET NULL? (⚠️ Special!)

**Other FKs** (14 lebih):
```
kategori_sampah → jenis_sampah.kategori_sampah_id (CASCADE)
jadwal_penyetoran → tabung_sampah.jadwal_id
tabung_sampah → poin_transaksis.tabung_sampah_id (SET NULL)
produks → penukaran_produk.produk_id (CASCADE)
produks → transaksis.produk_id (CASCADE)
kategori_transaksi → transaksis.kategori_id (CASCADE)
badges → user_badges.badge_id (CASCADE)
badges → badge_progress.badge_id (CASCADE)
```

**Checklist**:
- [ ] SET NULL ada di tabung_sampah_id? (bukan CASCADE!)
- [ ] semua CASCADE rules ada?
- [ ] No missing relationships?

---

### Priority 2: IMPORTANT (High Impact)

#### 4. Critical Field Names & Data Types

**USERS Table** (11 fields):
```
✓ id (BIGINT UNSIGNED AUTO_INCREMENT) - PK
✓ no_hp (VARCHAR 255) - UNIQUE, Business Key
✓ nama (VARCHAR 255)
✓ email (VARCHAR 255) - UNIQUE
✓ password (VARCHAR 255)
✓ alamat (TEXT)
✓ foto_profil (VARCHAR 255)
✓ total_poin (INT, default: 0) - ⚠️ NOT NULL!
✓ total_setor_sampah (INT, default: 0) - ⚠️ NOT NULL!
✓ level (VARCHAR 255)
✓ created_at (TIMESTAMP), updated_at (TIMESTAMP)
```

**Checklist**: Apakah semua 11 field ada dengan nama & tipe tepat?

**POIN_TRANSAKSIS Table** (Special - Polymorphic):
```
✓ id (BIGINT PK)
✓ user_id (BIGINT FK) - CASCADE
✓ tabung_sampah_id (BIGINT FK) - SET NULL, nullable ⚠️
✓ jenis_sampah (VARCHAR 255, nullable)
✓ berat_kg (DECIMAL 6,2, nullable)
✓ poin_didapat (INT) - CAN BE NEGATIVE! ⚠️
✓ sumber (VARCHAR 255) - setor/tukar/badge/bonus/manual
✓ keterangan (TEXT, nullable)
✓ referensi_id (BIGINT, nullable) - Polymorphic ⚠️
✓ referensi_tipe (VARCHAR 255, nullable) - Polymorphic ⚠️
✓ created_at, updated_at
```

**Special Notes**:
- [ ] poin_didapat bisa negatif? (INT, not UNSIGNED)
- [ ] referensi_id & referensi_tipe ada? (Polymorphic!)
- [ ] tabung_sampah_id nullable & SET NULL? (bukan CASCADE!)

**BADGE_PROGRESS Table** (Complex):
```
✓ id (BIGINT PK)
✓ user_id (BIGINT FK) - CASCADE
✓ badge_id (BIGINT FK) - CASCADE
✓ current_value (INT, default: 0)
✓ target_value (INT, default: 0)
✓ progress_percentage (DECIMAL 5,2)
✓ is_unlocked (BOOLEAN, default: false)
✓ unlocked_at (TIMESTAMP, nullable)
✓ created_at, updated_at
✓ UNIQUE(user_id, badge_id)
```

**Checklist**: Apakah semua 9 field ada dengan tepat?

---

### Priority 3: MODERATE (Consistency)

#### 5. Enum/Enum Values

| Table | Field | Possible Values | Notes |
|-------|-------|-----------------|-------|
| badges | tipe | poin, setor, kombinasi, special, ranking | 5 types |
| tabung_sampah | status | pending, approved, rejected | Deposit |
| penukaran_produk | status | pending, approved, cancelled | Redemption |
| transaksis | status | pending, diproses, dikirim, selesai, dibatalkan | 5 states |
| penarikan_tunai | status | pending, approved, rejected | Withdrawal |
| notifikasi | tipe | info, warning, success, error | Message type |
| log_aktivitas | tipe_aktivitas | login, deposit, tukar, withdraw, badge_unlock, etc | Activity |
| jenis_sampah | - | - | No enum |
| jadwal_penyetoran | status | aktif, penuh, tutup | Schedule |

**Action**: Verify enum values if diagram shows them.

#### 6. Default Values & Constraints

| Field | Default | Constraint | Notes |
|-------|---------|-----------|-------|
| users.total_poin | 0 | NOT NULL | ✓ Critical |
| users.total_setor_sampah | 0 | NOT NULL | ✓ Critical |
| jenis_sampah.satuan | 'kg' | - | - |
| tabung_sampah.poin_didapat | - | NOT NULL | - |
| produks.stok | - | NOT NULL | - |
| views (artikels) | 0 | NOT NULL | - |
| is_active | true | - | - |
| is_read | false | - | - |
| reward_claimed | true | - | - |
| is_unlocked | false | - | - |
| poin_didapat (transaksis) | - | - | Can be negative! |

**Action**: Verify defaults match if shown in diagram.

---

### Priority 4: NICE-TO-HAVE (Documentation)

#### 7. Relationship Labels

Setiap relationship harus labeled:
```
users 1 ----< M tabung_sampah [Label: "CASCADE on DELETE"]
users 1 ----< M penarikan_tunai (processed_by) [Label: "SET NULL"]
kategori_sampah 1 ----< M jenis_sampah [Label: "CASCADE"]
badges M ----< 1 user_badges [Label: "Many-to-Many"]
```

#### 8. Color Coding by System

| Color | System | Tables |
|-------|--------|--------|
| 🟦 Blue | Core | users, artikels |
| 🟩 Green | Waste Mgmt | kategori_sampah, jenis_sampah, jadwal_penyetoran, tabung_sampah |
| 🟨 Yellow | Products | produks, penukaran_produk, kategori_transaksi, transaksis |
| 🟧 Orange | Cash | penarikan_tunai |
| 🟪 Purple | Gamification | badges, user_badges, badge_progress |
| 🟥 Red | Audit | poin_transaksis, notifikasi, log_aktivitas |
| ⚪ Gray | System | sessions, personal_access_tokens, cache |

**Action**: Use consistent colors if doing color-coding.

---

## 🔍 DETAIL COMPARISON TEMPLATE

Untuk setiap table, gunakan template ini:

### TABLE: [NAME]

**Draw.io Status**: ☐ Present / ☐ Missing / ☐ Different

#### PK & Constraints
- Draw.io PK: ___________
- Should be: id (BIGINT)
- Status: ☐ Match / ☐ Different

- Draw.io UNIQUEs: ___________
- Should be: ___________
- Status: ☐ Match / ☐ Missing

#### Fields (Count)
- Draw.io: ___ fields
- Should be: ___ fields
- Status: ☐ Match / ☐ Different

**Missing fields**:
- [ ] Field 1?
- [ ] Field 2?

#### Relationships
- Draw.io: ___ incoming, ___ outgoing
- Should be: ___ incoming, ___ outgoing
- Status: ☐ Match / ☐ Different

**Missing/Extra relationships**:
- [ ] Relationship 1?
- [ ] Relationship 2?

#### Notes
_____________

---

## 🚀 SYNC EXECUTION STEPS

### Phase 1: IDENTIFY (1 hour)

1. Open both files side-by-side
   - [ ] draw.io diagram open in browser/app
   - [ ] DATABASE_ERD_VISUAL_DETAILED.md open in editor

2. Print ERD_VALIDATION_CHECKLIST.md
   - [ ] Use as reference sheet

3. Go through Priority 1 items
   - [ ] List all differences found
   - [ ] Document in ERD_SYNC_ISSUES.md

### Phase 2: CATEGORIZE (30 min)

Organize findings:
- [ ] Critical issues (Priority 1)
- [ ] Important issues (Priority 2)
- [ ] Nice-to-have improvements (Priority 3 & 4)

### Phase 3: DECIDE (1 hour)

For each discrepancy:
- [ ] Update diagram to match docs? OR
- [ ] Update docs to match diagram? OR
- [ ] Discuss with team for consensus?

### Phase 4: EXECUTE (2-3 hours)

Make updates:
- [ ] Update draw.io diagram in Mendaur-Physical-ERD.drawio.html
- [ ] OR update DATABASE_ERD_VISUAL_DETAILED.md
- [ ] Document all changes in CHANGE_LOG.md

### Phase 5: VERIFY (1 hour)

Final check:
- [ ] Re-compare both files
- [ ] All items in checklist checked ✓
- [ ] No discrepancies remain
- [ ] Document approval

### Phase 6: DOCUMENT (30 min)

- [ ] Create ERD_SYNC_FINAL_REPORT.md
- [ ] Archive old versions
- [ ] Announce sync complete

---

## 📋 ISSUES LOG TEMPLATE

Create file: **ERD_SYNC_ISSUES.md**

```markdown
# ERD Synchronization Issues Found

## Issue #1: [Priority] [Table] [Field]

**Found in**: Draw.io vs Docs
**Description**: 

**Current state**:
- Draw.io: ...
- Docs: ...

**Required fix**: 

**Status**: ☐ Not started / ☐ In progress / ☐ Fixed / ☐ Approved

---

## Issue #2: ...
```

---

## ⚠️ COMMON ISSUES TO WATCH FOR

### 1. Data Type Mismatches
```
❌ Wrong: user_id INT (should be BIGINT!)
❌ Wrong: id INT UNSIGNED (should be BIGINT UNSIGNED)
❌ Wrong: progress_percentage INT (should be DECIMAL 5,2)
✓ Right: id BIGINT UNSIGNED
✓ Right: progress_percentage DECIMAL(5,2)
```

### 2. Nullable Confusion
```
❌ Wrong: tabung_sampah_id NOT NULL (should be nullable!)
❌ Wrong: referensi_id NOT NULL (should be nullable!)
✓ Right: tabung_sampah_id BIGINT nullable
✓ Right: referensi_id BIGINT nullable
```

### 3. Foreign Key Cascade Rules
```
❌ Wrong: penarikan_tunai.processed_by CASCADE (should be SET NULL!)
❌ Wrong: tabung_sampah_id CASCADE (should be SET NULL!)
❌ Wrong: all relationships CASCADE (too dangerous!)
✓ Right: users → many CASCADE (user deleted → children deleted)
✓ Right: processed_by SET NULL (admin user deleted → set null)
✓ Right: tabung_sampah_id SET NULL (deposit deleted → poin orphaned)
```

### 4. Missing UNIQUE Constraints
```
❌ Wrong: user_badges without UNIQUE(user_id, badge_id)
❌ Wrong: badge_progress without UNIQUE(user_id, badge_id)
❌ Wrong: no UNIQUE on user_id, tabung_sampah_id, sumber
✓ Right: UNIQUE(user_id, badge_id) on both tables
✓ Right: UNIQUE on composite key prevents duplicates
```

### 5. Missing Polymorphic Fields
```
❌ Wrong: poin_transaksis missing referensi_id
❌ Wrong: poin_transaksis missing referensi_tipe
✓ Right: Both fields present & nullable
✓ Right: Used for audit trail tracing
```

---

## 🎯 FINAL VALIDATION CRITERIA

Your ERD is SYNCED when:

- [ ] All 20 tables present with correct names
- [ ] All PKs are BIGINT (except sessions: VARCHAR)
- [ ] All business keys marked UNIQUE
- [ ] All 25+ FKs correct with CASCADE/SET NULL
- [ ] All field names & types exact match
- [ ] All UNIQUE constraints present
- [ ] All nullable fields nullable
- [ ] All defaults correct
- [ ] Polymorphic fields present (referensi_id, referensi_tipe)
- [ ] Enum values documented
- [ ] Relationships clearly labeled
- [ ] No missing or extra fields
- [ ] No data type mismatches
- [ ] Diagram readable & professional

**Pass**: ✅ All items checked  
**Fail**: ❌ Some items unchecked

---

## 📞 QUICK REFERENCE

**If you find a difference**, ask:

1. **Which file is CORRECT?**
   - Draw.io (visual)?
   - Docs (detailed)?
   - Need to decide together?

2. **How critical is it?**
   - Can break database? (Priority 1)
   - Affects queries? (Priority 2)
   - Nice to have? (Priority 3)

3. **Update which?**
   - Update diagram (easier for visual)?
   - Update docs (more detailed)?
   - Both?

4. **Document why**
   - Why this difference exists?
   - Why fixing now?
   - Approve before finalizing?

---

**Status**: ✅ Ready to Use  
**Last Updated**: November 26, 2025  
**Document Version**: 1.0
