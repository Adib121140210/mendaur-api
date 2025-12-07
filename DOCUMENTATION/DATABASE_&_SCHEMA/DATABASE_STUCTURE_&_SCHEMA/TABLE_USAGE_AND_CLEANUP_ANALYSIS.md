# 📊 Database Table Usage & Cleanup Analysis

## Executive Summary

**Review Date**: November 29, 2025  
**Total Tables in Database**: 24+ tables  
**Tables with Models**: 21 tables ✓ (USED)  
**Potential Unused Tables**: 3 tables ❓ (REVIEW NEEDED)  
**Standardize Migration Files**: 11 files (EMPTY/NO-OP)

---

## 🟢 ACTIVELY USED TABLES (21 Tables)

These tables have corresponding Eloquent Models and are actively used in the application:

| # | Table Name | Model File | Purpose | Status |
|---|---|---|---|---|
| 1 | `users` | User.php | User authentication & profile | ✅ ACTIVE |
| 2 | `roles` | Role.php | RBAC role definitions | ✅ ACTIVE |
| 3 | `role_permissions` | RolePermission.php | Role permission mappings | ✅ ACTIVE |
| 4 | `audit_logs` | AuditLog.php | Activity audit trail | ✅ ACTIVE |
| 5 | `log_aktivitas` | LogAktivitas.php | User activity logging | ✅ ACTIVE |
| 6 | `log_poin` | (implied) | Points transaction logging | ✅ ACTIVE |
| 7 | `kategori_sampah` | KategoriSampah.php | Waste category classifications | ✅ ACTIVE |
| 8 | `jenis_sampah` | JenisSampah.php | Waste type definitions | ✅ ACTIVE |
| 9 | `tabung_sampah` | TabungSampah.php | Waste container/bin tracking | ✅ ACTIVE |
| 10 | `poin_transaksis` | PoinTransaksi.php | Point transaction records | ✅ ACTIVE |
| 11 | `produks` | Produk.php | Product catalog | ✅ ACTIVE |
| 12 | `penukaran_produk` | PenukaranProduk.php | Product redemption history | ✅ ACTIVE |
| 13 | `penarikan_tunai` | PenarikanTunai.php | Cash withdrawal transactions | ✅ ACTIVE |
| 14 | `badges` | Badge.php | Badge/achievement definitions | ✅ ACTIVE |
| 15 | `badge_progress` | BadgeProgress.php | User badge progress tracking | ✅ ACTIVE |
| 16 | `user_badges` | UserBadge.php | User-badge relationships | ✅ ACTIVE |
| 17 | `artikels` | Artikel.php | Article/blog content | ✅ ACTIVE |
| 18 | `notifikasi` | Notifikasi.php | User notifications | ✅ ACTIVE |
| 19 | `transaksis` | Transaksi.php | General transactions | ✅ ACTIVE |
| 20 | `kategori_transaksi` | KategoriTransaksi.php | Transaction category types | ✅ ACTIVE |
| 21 | `jadwal_penyetorans` | JadwalPenyetoran.php | Deposit schedule planning | ✅ ACTIVE |

---

## 🟡 POTENTIALLY UNUSED TABLES (Review Required)

### ⚠️ Issue 1: `jenis_sampah_new` Table
- **Status**: ❓ SUSPICIOUS
- **Model**: `JenisSampahNew.php` exists
- **Concern**: Naming suggests this is a test/temporary table
- **Recommendation**: 
  - ✅ **CHECK**: Is this a new version being tested?
  - ❌ **DELETE IF**: Old version replaced by this, and old data migrated
  - 📋 **KEEP IF**: Feature is still under development/testing

### ⚠️ Issue 2: `transaksis` vs Transaction System
- **Status**: ⚠️ REVIEW
- **Current Status**: Has model `Transaksi.php`
- **Observation**: Also has `poin_transaksis`, `kategori_transaksi` tables
- **Recommendation**:
  - ✅ **KEEP IF**: Stores general transactions (money, point transfers)
  - ❌ **DELETE IF**: Redundant with poin_transaksis

### ⚠️ Issue 3: `kategori_transaksi` Table
- **Status**: ⚠️ VERIFY USAGE
- **Current Status**: Has model `KategoriTransaksi.php`
- **Concern**: Need to verify if actively used in application
- **Recommendation**:
  - ✅ **KEEP IF**: Used to categorize transaction types
  - ❌ **DELETE IF**: Never used in controllers/queries

---

## 🔴 MIGRATION FILES: STANDARDIZE COLUMN NAMES (11 Files - EMPTY)

### Current Status: ⚠️ CRITICAL - THESE ARE NO-OP FILES

**Location**: `database/migrations/2025_11_28_100XXX_standardize_*.php`

| Filename | Target Table | Current State | Content |
|----------|---|---|---|
| `2025_11_28_100001_standardize_users_columns.php` | users | 🟡 EMPTY/STUB | No-op migration |
| `2025_11_28_100002_standardize_kategori_sampah_columns.php` | kategori_sampah | 🟡 EMPTY/STUB | No-op migration |
| `2025_11_28_100003_standardize_jenis_sampah_columns.php` | jenis_sampah | 🟡 EMPTY/STUB | No-op migration |
| `2025_11_28_100004_standardize_tabung_sampah_columns.php` | tabung_sampah | 🟡 EMPTY/STUB | No-op migration |
| `2025_11_28_100005_standardize_produk_columns.php` | produks | 🟡 EMPTY/STUB | No-op migration |
| `2025_11_28_100006_standardize_penukaran_produk_columns.php` | penukaran_produk | 🟡 EMPTY/STUB | No-op migration |
| `2025_11_28_100007_standardize_penarikan_tunai_columns.php` | penarikan_tunai | 🟡 EMPTY/STUB | No-op migration |
| `2025_11_28_100008_standardize_badges_columns.php` | badges | 🟡 EMPTY/STUB | No-op migration |
| `2025_11_28_100009_standardize_artikels_columns.php` | artikels | 🟡 EMPTY/STUB | No-op migration |
| `2025_11_28_100010_standardize_log_poin_columns.php` | log_poin | 🟡 EMPTY/STUB | No-op migration |
| `2025_11_28_100011_standardize_log_user_activity_columns.php` | log_aktivitas | 🟡 EMPTY/STUB | No-op migration |

### What These Files Are:
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { }      // ← EMPTY (no-op)
    public function down(): void { }    // ← EMPTY (no-op)
};
```

**These are "stub" migrations created during error recovery.**

---

## 📋 MY RECOMMENDATIONS

### ✅ RECOMMENDATION 1: DELETE STANDARDIZE MIGRATION FILES (All 11)

**Reason**: These are empty no-op migrations that:
- Serve no purpose (empty up/down methods)
- Take up space and clutter the migration history
- Were created as error-recovery stubs when you undid the standardization work
- Won't hurt anything if left, but won't help either

**Action**: Safe to delete - they do nothing anyway

**Command to delete**:
```bash
# PowerShell
Get-Item "database/migrations/2025_11_28_100*.php" | Remove-Item
```

---

### ⚠️ RECOMMENDATION 2: VERIFY & DECIDE ON POTENTIALLY UNUSED TABLES

**Before cleaning up, verify these 3 items:**

#### Task 1: Check `jenis_sampah_new` Table
1. Search your codebase for usage of `JenisSampahNew` model
2. Check if tests reference it
3. **Decision**:
   - If UNUSED → Delete the model and drop the table via new migration
   - If USED → Rename it appropriately

#### Task 2: Verify `transaksis` Table Usage
1. Check controllers for queries using `Transaksi` model
2. See if it's used alongside or instead of `poin_transaksis`
3. **Decision**:
   - If UNUSED → Can be deprecated/dropped
   - If USED → Keep it

#### Task 3: Verify `kategori_transaksi` Table Usage
1. Search for references to `KategoriTransaksi` in code
2. Check if any controllers query it
3. **Decision**:
   - If UNUSED → Can be deprecated/dropped
   - If USED → Keep it

---

### ✅ RECOMMENDATION 3: IF DROPPING TABLES - CREATE PROPER DROP MIGRATIONS

**If you decide to drop unused tables, DON'T delete them manually.** Instead:

Create new migration files:
```bash
php artisan make:migration drop_jenis_sampah_new_table
php artisan make:migration drop_transaksis_table  # if unused
php artisan make:migration drop_kategori_transaksi_table  # if unused
```

Then fill them with proper drop logic:
```php
public function up(): void
{
    Schema::dropIfExists('table_name');
}

public function down(): void
{
    // Optional: recreate table if needed
    Schema::create('table_name', function (Blueprint $table) {
        // ...
    });
}
```

---

## 🎯 CLEANUP PLAN (3 PHASES)

### Phase 1: SAFE & IMMEDIATE ✅
- **Action**: Delete all 11 standardize migration files
- **Risk Level**: 🟢 ZERO (they're no-op stubs)
- **Time**: 30 seconds
- **Command**: Use find/remove or manual deletion

### Phase 2: VERIFICATION ⚠️
- **Action**: Review code for unused table references
- **Risk Level**: 🟡 LOW (just reading code)
- **Time**: 5-10 minutes
- **Files to check**:
  - `app/Http/Controllers/**`
  - `app/Models/**`
  - `database/seeders/**`
  - Test files

### Phase 3: SAFE CLEANUP (OPTIONAL)
- **Action**: Create drop migrations for truly unused tables
- **Risk Level**: 🟡 LOW (proper migrations are reversible)
- **Time**: 10 minutes
- **Execute**: Only after Phase 2 verification

---

## 📊 SUMMARY TABLE

| Action | Files | Risk | Time | Recommendation |
|--------|-------|------|------|---|
| Delete standardize migrations | 11 | 🟢 ZERO | 30s | ✅ **DO IT NOW** |
| Verify jenis_sampah_new usage | 1 | 🟡 LOW | 2m | 📋 CHECK FIRST |
| Verify transaksis usage | 1 | 🟡 LOW | 2m | 📋 CHECK FIRST |
| Verify kategori_transaksi usage | 1 | 🟡 LOW | 2m | 📋 CHECK FIRST |
| Create drop migrations | ~3 | 🟡 LOW | 10m | ✅ IF UNUSED |

---

## ❓ NEXT STEPS

### You decide:

**Option A: QUICK CLEANUP (2 minutes)**
- Delete all 11 standardize migration files now
- Done! Database cleanup is simplified

**Option B: THOROUGH CLEANUP (20 minutes)**
1. Delete all 11 standardize migration files
2. Have me search code for unused table references
3. Create proper drop migrations for unused tables
4. Verify everything still works with `php artisan migrate:fresh --seed`

**Option C: LEAVE AS-IS**
- Keep everything as-is for now
- Come back to cleanup later
- No harm in leaving empty migrations (but not ideal)

---

**What would you like to do?** Tell me:
1. Delete standardize files? (yes/no)
2. Do thorough cleanup? (yes/no)
3. Any tables you know are unused?

