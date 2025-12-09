# 🚀 DROP UNUSED TABLES - EXECUTION GUIDE

**Status:** Ready to Execute  
**Risk Level:** 🟢 LOW (with backup)  
**Execution Time:** 5-10 minutes total

---

## 📋 PRE-EXECUTION CHECKLIST

Pastikan semua ini sudah dilakukan:

```
Database Level:
  [ ] Database backed up
  [ ] Backup file verified and stored safely
  [ ] No active transactions
  [ ] No other users connected

Application Level:
  [ ] App is in development mode OR maintenance mode set
  [ ] No queue workers running
  [ ] No API requests happening
  [ ] No background jobs running

Code Level:
  [ ] No code references to dropped tables
  [ ] No models using dropped tables
  [ ] No cache operations in progress

Team Level:
  [ ] Stakeholders notified
  [ ] Change documented
  [ ] Rollback plan understood
```

---

## 🔄 EXECUTION OPTIONS

### **OPTION 1: Via Laravel Migration (RECOMMENDED) ⭐**

**Step 1: Backup database**

```powershell
# Windows PowerShell
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
mysqldump -u root -p mendaur_db > "C:\Backups\mendaur_db_backup_$timestamp.sql"
```

**Step 2: Verify backup created**

```powershell
# Check file exists and size > 0
Get-Item "C:\Backups\mendaur_db_backup_*.sql" | Format-Table Name, Length
```

**Step 3: Run migration**

```bash
# Navigate to project directory
cd c:\Users\Adib\OneDrive\Desktop\mendaur-api

# Run migration
php artisan migrate

# Output should show:
# INFO  Migrating [database/migrations/2024_12_01_000000_drop_unused_tables.php]
# ✓ Dropped: cache_locks
# ✓ Dropped: cache
# ✓ Dropped: job_batches
# ✓ Dropped: failed_jobs
# ✓ Dropped: jobs
# ✓ All unused tables dropped successfully!
```

**Step 4: Verify tables were dropped**

```bash
# Check remaining tables
php artisan tinker

# In Tinker:
>>> DB::select('SHOW TABLES;')

# Or check specific tables:
>>> Schema::hasTable('cache')           # Should return false
>>> Schema::hasTable('jobs')            # Should return false
>>> Schema::hasTable('users')           # Should return true (CRITICAL)
>>> Schema::hasTable('transaksis')      # Should return true (CRITICAL)
```

**Step 5: Verify database integrity**

```bash
# Still in Tinker:

# Check foreign key constraints
>>> $constraints = DB::select("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                              WHERE TABLE_SCHEMA = 'mendaur_db' AND REFERENCED_TABLE_NAME IS NOT NULL");
>>> count($constraints)  # Should show 22 relationships

# Check table count
>>> DB::select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES 
               WHERE TABLE_SCHEMA = 'mendaur_db'");
# Should show: count = 24 (23 business + 1 framework)

# Exit Tinker
>>> exit()
```

---

### **OPTION 2: Via Raw SQL (Direct method)**

**Step 1: Backup database**

```powershell
# Windows PowerShell
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
mysqldump -u root -p mendaur_db > "C:\Backups\mendaur_db_backup_$timestamp.sql"
```

**Step 2: Open MySQL client**

```bash
# Connect to MySQL
mysql -u root -p mendaur_db

# Or using MySQL Workbench/HeidiSQL (GUI tool)
```

**Step 3: Execute DROP script**

```bash
# In MySQL CLI, run:
source c:\Users\Adib\OneDrive\Desktop\mendaur-api\DROP_UNUSED_TABLES.sql

# Or copy-paste the SQL commands from the file
```

**Step 4: Verify in MySQL**

```sql
-- Check total tables
SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'mendaur_db';
-- Should show: 24

-- List all tables
SHOW TABLES;
-- Should NOT show: cache, cache_locks, jobs, failed_jobs, job_batches

-- Verify critical tables exist
SHOW TABLES LIKE 'users';     -- Should exist
SHOW TABLES LIKE 'transaksis'; -- Should exist
SHOW TABLES LIKE 'badges';    -- Should exist
```

---

## ⚠️ TROUBLESHOOTING

### **Problem 1: Foreign Key Constraint Error**

```
Error: Cannot delete or update a parent row: a foreign key constraint fails
```

**Solution:**
```bash
# This shouldn't happen because dropped tables have no FKs
# But if it does, check:

# 1. Verify no other tables reference the dropped tables
SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_NAME IN ('cache', 'jobs', 'failed_jobs', 'job_batches', 'cache_locks')
AND TABLE_SCHEMA = 'mendaur_db';

# If any results show, we have a problem (shouldn't happen)

# 2. Disable FK checks (already done in migration/script)
SET FOREIGN_KEY_CHECKS = 0;

# Then retry the migration or script
```

### **Problem 2: Table Still Exists After Drop**

```
Table 'cache' still exists (verified with SHOW TABLES)
```

**Solution:**
```bash
# Check if table exists in information schema
SELECT * FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'mendaur_db' 
AND TABLE_NAME = 'cache';

# If query returns results, try manual drop:
DROP TABLE IF EXISTS `cache`;

# If still fails, database may be locked
# Check for open connections:
SELECT * FROM INFORMATION_SCHEMA.PROCESSLIST 
WHERE DB = 'mendaur_db';

# Kill any long-running transactions if needed
KILL QUERY process_id;
```

### **Problem 3: Migration File Not Found**

```
Migration not found in database/migrations/
```

**Solution:**
```bash
# Verify file exists
dir database\migrations\

# Check if file is in correct format
# File should be named: database/migrations/2024_12_01_000000_drop_unused_tables.php

# Verify file contents:
type database\migrations\2024_12_01_000000_drop_unused_tables.php

# Clear migration cache:
php artisan cache:clear
php artisan config:cache

# Retry migration:
php artisan migrate
```

### **Problem 4: Rollback Needed (Something went wrong)**

```
Need to restore dropped tables
```

**Solution (Option A - From Backup):**
```bash
# Restore from backup
mysql -u root -p mendaur_db < "C:\Backups\mendaur_db_backup_YYYYMMDD_HHMMSS.sql"
```

**Solution (Option B - Via Migration Rollback):**
```bash
# Rollback the migration
php artisan migrate:rollback

# Verify tables recreated
php artisan tinker
>>> DB::select('SHOW TABLES;')
>>> Schema::hasTable('cache')  # Should return true now
```

---

## ✅ POST-EXECUTION VERIFICATION

After dropping tables, run these checks:

### **Check 1: Table Count**

```bash
php artisan tinker

>>> DB::select("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()");
# Expected: 24 tables
```

### **Check 2: Verify Dropped Tables Gone**

```bash
>>> foreach(['cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches'] as $table) {
      echo "$table: " . (Schema::hasTable($table) ? '❌ EXISTS' : '✓ DROPPED') . "\n";
    }

# Expected output:
# cache: ✓ DROPPED
# cache_locks: ✓ DROPPED
# jobs: ✓ DROPPED
# failed_jobs: ✓ DROPPED
# job_batches: ✓ DROPPED
```

### **Check 3: Verify Critical Tables Exist**

```bash
>>> $critical = ['users', 'transaksis', 'badges', 'produks', 'penukaran_produk', 'penarikan_tunai'];
>>> foreach($critical as $table) {
      echo "$table: " . (Schema::hasTable($table) ? '✓ EXISTS' : '❌ MISSING!') . "\n";
    }

# Expected: all ✓ EXISTS
```

### **Check 4: Database Integrity**

```bash
>>> // Check foreign key relationships
>>> DB::select("SELECT COUNT(*) as fk_count FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
              WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");

# Expected: 22 foreign key relationships intact
```

### **Check 5: Test API Endpoints**

```bash
# In browser or Postman:
GET /api/user/profile
# Should work normally ✓

GET /api/points
# Should work normally ✓

GET /api/products
# Should work normally ✓

# Monitor error logs for any issues
```

---

## 📊 SUCCESS CRITERIA

```
✅ SUCCESS = ALL of these are true:

1. Migration executed without errors
2. 5 unused tables dropped (cache, cache_locks, jobs, failed_jobs, job_batches)
3. Total tables now = 24 (down from 29)
4. All 23 business logic tables still exist
5. All 22 foreign key relationships intact
6. No errors in application logs
7. All API endpoints work normally
8. No database integrity issues
9. Backup file saved and verified
10. Change documented and communicated

RESULT: ✅ Database cleanup successful!
```

---

## 📝 EXECUTION LOG

Use this section to log your execution:

```
Execution Date: _______________
Executed By: _______________
Backup File: _______________
Backup Size: _______________
Backup Verified: [ ] YES [ ] NO
Execution Method: [ ] Migration [ ] SQL [ ] Other: _______
Start Time: _______________
End Time: _______________
Execution Status: [ ] SUCCESS [ ] FAILED [ ] PARTIAL
Issues Encountered: _______________
Resolution: _______________
Post-Execution Tests: [ ] PASSED [ ] FAILED [ ] NOT DONE
Database Integrity: [ ] OK [ ] ISSUES
Application Status: [ ] NORMAL [ ] ERRORS [ ] NOT TESTED
Sign-off: _______________
```

---

## 🔄 ROLLBACK PROCEDURE

If anything goes wrong:

### **Immediate Rollback (Within same session):**

```bash
# Rollback the migration
php artisan migrate:rollback

# Verify tables recreated
php artisan tinker
>>> Schema::hasTable('cache')     # Should return true
>>> Schema::hasTable('jobs')      # Should return true
```

### **Restore From Backup (if rollback fails):**

```powershell
# Windows PowerShell
# Find your backup file
$backupFile = "C:\Backups\mendaur_db_backup_YYYYMMDD_HHMMSS.sql"

# Restore database
mysql -u root -p mendaur_db < $backupFile

# Verify restore
mysql -u root -p -e "USE mendaur_db; SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES;"
# Should show: 29 (original count)
```

---

## ✅ FINAL CHECKLIST BEFORE EXECUTION

```
BEFORE YOU RUN:

System Status:
  [ ] Database backed up ✓
  [ ] Backup verified ✓
  [ ] Application offline/maintenance mode ✓
  [ ] No active connections to database ✓
  [ ] No queue workers running ✓

Documentation:
  [ ] Execution log prepared ✓
  [ ] Rollback plan understood ✓
  [ ] Team notified ✓
  [ ] Change request submitted ✓

Execution:
  [ ] Using Option 1 (Migration) OR Option 2 (SQL) ✓
  [ ] Will monitor execution ✓
  [ ] Will verify success ✓
  [ ] Will test API endpoints ✓

All checklist items completed? → READY TO EXECUTE ✓
```

---

**Status:** 🟢 READY FOR EXECUTION  
**Next Step:** Choose Option 1 or 2 above and execute  
**Support:** If issues arise, follow TROUBLESHOOTING section
