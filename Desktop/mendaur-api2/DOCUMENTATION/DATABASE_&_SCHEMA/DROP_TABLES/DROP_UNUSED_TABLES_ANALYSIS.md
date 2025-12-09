# 🗑️ DROP UNUSED TABLES - ANALISIS & EKSEKUSI

**Date:** December 1, 2025  
**Status:** ⚠️ REVIEW BEFORE EXECUTION  
**Backup Required:** ✅ YES - MANDATORY

---

## ⚠️ CRITICAL WARNING

```
⚠️  BAHAYA! Operasi ini PERMANENT dan tidak bisa di-undo!
    
WAJIB:
    1. ✅ Backup database SEBELUM eksekusi
    2. ✅ Test di environment DEVELOPMENT dulu
    3. ✅ Review tabel yang akan dihapus
    4. ✅ Pastikan tidak ada aplikasi lain yang pakai
    5. ✅ Set maintenance mode sebelum eksekusi production
```

---

## 📊 TABEL YANG AKAN DI-DROP

Berdasarkan analisis sebelumnya, ada **6 tabel tidak digunakan**:

### **GROUP: TIDAK DIGUNAKAN (6 Tabel)**

| # | Tabel | Status | Rows | Alasan |
|---|-------|--------|------|--------|
| 1 | `cache` | ❌ Unused | ~0 | Laravel built-in, tidak dipakai |
| 2 | `cache_locks` | ❌ Unused | ~0 | Lock mechanism untuk cache, tidak dipakai |
| 3 | `failed_jobs` | ❌ Unused | ~0 | Queue failed jobs, tidak ada queue jobs |
| 4 | `jobs` | ❌ Unused | ~0 | Queue jobs, tidak implementasi async jobs |
| 5 | `job_batches` | ❌ Unused | ~0 | Job batching, tidak dipakai |
| 6 | `personal_access_tokens` | ❓ Maybe | ~0 | Sanctum tokens, optional jika auth via session |

---

## 🔍 ANALISIS DETAIL SETIAP TABEL

### **1. `cache` - DROP ✅**
```
Purpose:     Laravel cache table storage
Foreign Key: NONE
Referenced: NONE
Data:        Empty (~0 rows)
Impact:      NONE - tidak dipakai di sistem
Risk:        VERY LOW
Decision:    ✅ SAFE TO DROP
```

### **2. `cache_locks` - DROP ✅**
```
Purpose:     Lock mechanism untuk cache operations
Foreign Key: NONE
Referenced: NONE
Data:        Empty (~0 rows)
Impact:      NONE - dependency: cache table
Risk:        VERY LOW (jika cache di-drop)
Decision:    ✅ SAFE TO DROP (setelah cache)
```

### **3. `failed_jobs` - DROP ✅**
```
Purpose:     Store failed queue jobs
Foreign Key: NONE
Referenced: NONE
Data:        Empty (~0 rows)
Impact:      NONE - tidak ada async job processing
Risk:        VERY LOW
Decision:    ✅ SAFE TO DROP
```

### **4. `jobs` - DROP ✅**
```
Purpose:     Queue job processing (database queue)
Foreign Key: NONE
Referenced: NONE
Data:        Empty (~0 rows)
Impact:      NONE - tidak ada queue implementation
Risk:        VERY LOW
Decision:    ✅ SAFE TO DROP
```

### **5. `job_batches` - DROP ✅**
```
Purpose:     Batch job grouping
Foreign Key: NONE
Referenced: NONE
Data:        Empty (~0 rows)
Impact:      NONE - dependency: jobs table
Risk:        VERY LOW
Decision:    ✅ SAFE TO DROP (setelah jobs)
```

### **6. `personal_access_tokens` - ⚠️ CAUTION**
```
Purpose:     Sanctum API tokens (optional auth)
Foreign Key: user_id → users(id) [CASCADE DELETE]
Referenced: Sanctum middleware
Data:        Empty (~0 rows)
Impact:      MEDIUM - jika future pakai Sanctum
Risk:        MEDIUM - bisa jadi diperlukan nanti
Decision:    ⚠️ OPTIONAL - Keep if might use API tokens later
Recommendation: KEEP for now (safe to keep empty)
```

---

## 📋 TABEL YANG HARUS DI-KEEP

### **CRITICAL - DO NOT DROP:**

```
✅ USERS - Core user management
✅ ROLES - Role-based access control
✅ ROLE_PERMISSIONS - Permission management
✅ SESSIONS - Laravel session storage
✅ NOTIFIKASI - Push notifications
✅ KATEGORI_SAMPAH - Waste categories
✅ JENIS_SAMPAH - Waste types
✅ TABUNG_SAMPAH - Waste containers
✅ JADWAL_PENYETORANS - Deposit schedules
✅ TRANSAKSIS - Transactions
✅ KATEGORI_TRANSAKSI - Transaction categories
✅ POIN_TRANSAKSIS - Point transactions
✅ PRODUKS - Products
✅ PENUKARAN_PRODUK - Product redemptions
✅ BADGES - Gamification badges
✅ USER_BADGES - User badge progress
✅ PENARIKAN_TUNAI - Cash withdrawals
✅ LOG_AKTIVITAS - Activity logging
✅ AUDIT_LOGS - Audit trail
✅ ARTIKELS - Content/articles
✅ MIGRATIONS - Migration history (Laravel required)
✅ PASSWORD_RESET_TOKENS - Password reset tokens
```

---

## 🛠️ DROP STRATEGY

### **Option 1: DROP UNUSED TABEL (Recommended)**

Drop hanya 5 tabel:
- `cache`
- `cache_locks`
- `failed_jobs`
- `jobs`
- `job_batches`

**Keep:**
- `personal_access_tokens` (bisa berguna untuk API auth di future)
- `migrations` (required Laravel)
- `sessions` (active usage)
- `password_reset_tokens` (active usage)

**Benefit:**
- ✅ Bersihkan database dari unused tables
- ✅ Kurangi storage (minimal, tapi clean)
- ✅ Tidak ada breaking changes
- ✅ Simple and safe

---

## 📝 EXECUTION PLAN

### **Step 1: Backup Database**

```bash
# Linux/Mac
mysqldump -u root -p mendaur_db > mendaur_db_backup_$(date +%Y%m%d_%H%M%S).sql

# Windows PowerShell
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
mysqldump -u root -p mendaur_db > "mendaur_db_backup_$timestamp.sql"
```

### **Step 2: Create Laravel Migration**

Tempat: `database/migrations/YYYY_MM_DD_HHMMSS_drop_unused_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop unused Laravel queue/cache tables
        // These tables are not used in the Mendaur system
        
        Schema::dropIfExists('cache_locks');  // Must drop first (no FK)
        Schema::dropIfExists('cache');        // Must drop first (no FK)
        Schema::dropIfExists('job_batches');  // Must drop first (no FK)
        Schema::dropIfExists('failed_jobs');  // No FK
        Schema::dropIfExists('jobs');         // No FK
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate tables on rollback
        
        // Create jobs table
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // Create failed_jobs table
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Create job_batches table
        Schema::create('job_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids')->nullable();
            $table->text('options')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('cancelled_at')->nullable();
        });

        // Create cache table
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->unique();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        // Create cache_locks table
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->unique();
            $table->string('owner');
            $table->integer('expiration');
        });
    }
};
```

### **Step 3: Execute Migration**

```bash
# Development environment
php artisan migrate

# Production environment (dengan backup!)
php artisan down  # Set maintenance mode
php artisan migrate
php artisan up    # Resume application
```

### **Step 4: Verify**

```bash
# Check remaining tables
php artisan tinker
>>> DB::select('SHOW TABLES;')

# Verify critical tables still exist
>>> Schema::hasTable('users')
>>> Schema::hasTable('transaksis')
>>> Schema::hasTable('badges')
```

---

## ✅ PRE-EXECUTION CHECKLIST

```
BEFORE DROPPING TABLES:

Database Level:
  [ ] Backup created and verified
  [ ] Tested backup restore
  [ ] Notification to all users
  [ ] Schedule during low-traffic period

Application Level:
  [ ] No running jobs
  [ ] No cache operations happening
  [ ] No queue workers running
  [ ] All API responses verified
  [ ] No external dependencies using dropped tables

Code Level:
  [ ] Search codebase for 'cache' references
  [ ] Search codebase for 'jobs' references
  [ ] Search codebase for 'failed_jobs' references
  [ ] Verify no middleware using dropped tables
  [ ] Verify no models extending dropped tables

Documentation:
  [ ] Migration file created
  [ ] Rollback plan documented
  [ ] Change log updated
  [ ] Team notified

Execution:
  [ ] Maintenance mode enabled (production)
  [ ] Monitor database during execution
  [ ] Verify migration success
  [ ] Check error logs
  [ ] Test all critical features
```

---

## 🔄 ROLLBACK PLAN

Jika ada masalah:

```bash
# Rollback migration
php artisan migrate:rollback

# Or restore from backup
mysql -u root -p mendaur_db < mendaur_db_backup_20241201_120000.sql

# Verify rollback
php artisan tinker
>>> DB::select('SHOW TABLES;')
>>> DB::table('jobs')->count()  // Should exist again
```

---

## 📊 EXPECTED RESULTS

### **Before Drop:**
```
Total Tables: 29
├─ Business Logic: 23 (KEEP)
├─ Framework Support: 8
│  ├─ migrations (KEEP)
│  ├─ sessions (KEEP)
│  ├─ password_reset_tokens (KEEP)
│  ├─ personal_access_tokens (KEEP)
│  ├─ cache (DROP)
│  ├─ cache_locks (DROP)
│  ├─ failed_jobs (DROP)
│  ├─ jobs (DROP)
│  └─ job_batches (DROP)
└─ Unused: 6 (WILL DROP 5)

Storage: ~50-100 MB total
```

### **After Drop:**
```
Total Tables: 24
├─ Business Logic: 23 (KEEP) ✅
├─ Framework Support: 4
│  ├─ migrations ✅
│  ├─ sessions ✅
│  ├─ password_reset_tokens ✅
│  └─ personal_access_tokens ✅
└─ Unused: 0

Storage: ~50-95 MB total (minimal reduction but cleaner)
Benefit: Cleaner schema, easier maintenance
```

---

## 🎯 FINAL RECOMMENDATION

### **✅ DO: Drop these 5 tables**
```
1. cache
2. cache_locks
3. failed_jobs
4. jobs
5. job_batches
```

**Reasons:**
- ✅ Not used in Mendaur system
- ✅ No foreign keys
- ✅ Empty (0 rows)
- ✅ Easy to recreate if needed
- ✅ Safe to drop
- ✅ No breaking changes

### **⚠️ MAYBE LATER: personal_access_tokens**
```
Current: Empty, not used
Future: Might need for Sanctum API authentication
Recommendation: KEEP for now (doesn't hurt, might need later)
```

### **✅ MUST KEEP: Everything else**
```
- 23 Business logic tables (CRITICAL)
- migrations, sessions, password_reset_tokens (REQUIRED)
```

---

## 🚀 NEXT STEPS

**If you approve:**
1. ✅ Create migration file in `database/migrations/`
2. ✅ Run `php artisan migrate`
3. ✅ Verify with database admin
4. ✅ Monitor for any issues

**If you want to be more conservative:**
1. ✅ Keep personal_access_tokens (for Sanctum)
2. ✅ Drop only: cache, cache_locks, failed_jobs, jobs, job_batches
3. ✅ Plan for future cleanup

---

## 📞 DECISION NEEDED

**Pilih salah satu:**

- [ ] **Option A**: Drop 5 tables NOW (cache, cache_locks, failed_jobs, jobs, job_batches)
- [ ] **Option B**: Drop 5 tables + keep personal_access_tokens (same)
- [ ] **Option C**: Wait, review code more carefully first
- [ ] **Option D**: Drop with custom date (specify backup date)

**Approval from:** _______________  
**Date:** _______________  
**Comments:** _______________

---

**Status:** ⏳ AWAITING APPROVAL
**Risk Level:** 🟢 LOW (if backup exists)
**Execution Time:** ~5 minutes
**Downtime Required:** ~1 minute (if production)
