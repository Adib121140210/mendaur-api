# 🔍 INTERACTIVE DROP UNUSED TABLES - MANUAL EXECUTION GUIDE

**Date:** December 1, 2025  
**Status:** Ready for manual execution  
**Method:** Using MySQL GUI or CLI directly

---

## 📋 STEP 1: CONNECT TO DATABASE & LIST TABLES

### **Using MySQL Command Line (if available)**

```bash
# Connect to database
mysql -u root -p mendaur_db

# List all tables
SHOW TABLES;

# OR: See tables with more detail
SELECT TABLE_NAME, TABLE_TYPE, TABLE_ROWS FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'mendaur_db' ORDER BY TABLE_NAME;
```

### **Using MySQL Workbench / HeidiSQL (GUI)**

1. Open MySQL Workbench or HeidiSQL
2. Connect to your MySQL server
3. Select database: `mendaur_db`
4. In query editor, run:
   ```sql
   SHOW TABLES;
   ```

### **Expected Output (29 tables):**

```
Tables_in_mendaur_db
articels
audit_logs
badges
cache
cache_locks
categori_sampah
categori_transaksi
failed_jobs
jadwal_penyetorans
jenis_sampah
job_batches
jobs
log_aktivitas
notifikasi
penukaran_produk
penarikan_tunai
personal_access_tokens
password_reset_tokens
produks
poin_transaksis
role_permissions
roles
sessions
tabung_sampah
transaksis
users
```

---

## 🎯 STEP 2: IDENTIFY TABLES TO DROP

### **5 TABLES TO DROP (Unused, Empty)**

```sql
-- These 5 tables are:
-- ✗ Empty (0 rows)
-- ✗ Not used in Mendaur system
-- ✗ No code references
-- ✗ No foreign key dependencies

1. cache
2. cache_locks
3. failed_jobs
4. jobs
5. job_batches
```

### **24 TABLES TO KEEP (All Used & Critical)**

```sql
-- These 24 tables are:
-- ✓ Used by Mendaur system
-- ✓ Contains business logic
-- ✓ No risk to drop other tables

KEEP: articels, audit_logs, badges, categori_sampah, 
      categori_transaksi, jadwal_penyetorans, jenis_sampah, 
      log_aktivitas, notifikasi, penukaran_produk, penarikan_tunai, 
      personal_access_tokens, password_reset_tokens, produks, 
      poin_transaksis, role_permissions, roles, sessions, 
      tabung_sampah, transaksis, users, migrations
```

---

## 📊 STEP 3: VERIFY TABLES ARE EMPTY

Before dropping, verify these 5 tables have 0 rows:

```sql
-- Run this query to check row counts
SELECT TABLE_NAME, TABLE_ROWS 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'mendaur_db' 
AND TABLE_NAME IN ('cache', 'cache_locks', 'failed_jobs', 'jobs', 'job_batches')
ORDER BY TABLE_NAME;
```

**Expected Result:**
```
TABLE_NAME      | TABLE_ROWS
cache           | 0
cache_locks     | 0
failed_jobs     | 0
jobs            | 0
job_batches     | 0
```

All should show 0 rows. If any show different count, DON'T DROP IT.

---

## 💾 STEP 4: BACKUP DATABASE (CRITICAL!)

### **Using Command Line:**

```powershell
# Windows PowerShell (from any directory)
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
# Find mysqldump.exe location first (usually in MySQL bin folder)
# Then run:
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump" -u root -p mendaur_db > "C:\Backups\mendaur_db_backup_$timestamp.sql"

# Enter password when prompted
```

### **Using MySQL Workbench:**

1. Right-click database `mendaur_db`
2. Select "Export as SQL Dump"
3. Save to: `C:\Backups\mendaur_db_backup_YYYYMMDD_HHMMSS.sql`
4. Verify file created and > 5 MB

### **Backup Verification:**

```powershell
# Verify backup file exists and is not empty
Get-Item "C:\Backups\mendaur_db_backup_*.sql" | Format-Table FullName, Length
```

**Expected:** File size > 5 MB

---

## 🔪 STEP 5: DROP THE 5 TABLES

### **Option A: Run all DROP commands at once (Safest)**

Copy this entire block and paste into MySQL query editor:

```sql
-- ========================================
-- DROP 5 UNUSED TABLES FROM MENDAUR DB
-- ========================================

-- Disable foreign key checks (safety)
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables in correct order
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `jobs`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verification: Show remaining table count
SELECT COUNT(*) as Remaining_Table_Count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'mendaur_db';

-- Should show: 24 tables
```

**Then press EXECUTE or Ctrl+Enter**

### **Option B: Drop tables one by one (More control)**

```sql
-- Table 1: Drop cache_locks
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `cache_locks`;
SET FOREIGN_KEY_CHECKS = 1;
SELECT 'cache_locks dropped' as Status;

-- Table 2: Drop cache
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `cache`;
SET FOREIGN_KEY_CHECKS = 1;
SELECT 'cache dropped' as Status;

-- Table 3: Drop job_batches
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `job_batches`;
SET FOREIGN_KEY_CHECKS = 1;
SELECT 'job_batches dropped' as Status;

-- Table 4: Drop failed_jobs
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `failed_jobs`;
SET FOREIGN_KEY_CHECKS = 1;
SELECT 'failed_jobs dropped' as Status;

-- Table 5: Drop jobs
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `jobs`;
SET FOREIGN_KEY_CHECKS = 1;
SELECT 'jobs dropped' as Status;
```

**Run each block one by one and verify**

---

## ✔️ STEP 6: VERIFY DROP SUCCESS

After dropping, run this verification query:

```sql
-- ==================================================
-- VERIFICATION: Check remaining tables (should be 24)
-- ==================================================

-- Count total tables
SELECT COUNT(*) as Total_Tables FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'mendaur_db';
-- Expected: 24

-- List all remaining tables
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'mendaur_db' ORDER BY TABLE_NAME;

-- Verify dropped tables don't exist
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'mendaur_db' 
AND TABLE_NAME IN ('cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches');
-- Expected: Empty result (0 rows)

-- Verify critical tables still exist
SELECT COUNT(*) as Critical_Tables FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'mendaur_db' 
AND TABLE_NAME IN ('users', 'transaksis', 'badges', 'produks', 'penukaran_produk');
-- Expected: 5 (all exist)

-- Check foreign key relationships (should be 22)
SELECT COUNT(*) as FK_Relationships FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'mendaur_db' AND REFERENCED_TABLE_NAME IS NOT NULL;
-- Expected: 22
```

**Expected Verification Output:**
```
Total_Tables:         24 ✓
Dropped tables found: (empty) ✓
Critical tables:      5 ✓
FK relationships:     22 ✓
```

---

## 🧪 STEP 7: TEST APPLICATION

### **Test API Endpoints:**

```bash
# In browser or Postman, test these endpoints
GET http://localhost:8000/api/user/profile
GET http://localhost:8000/api/points
GET http://localhost:8000/api/products
GET http://localhost:8000/api/badges

# All should return 200 OK ✓
```

### **Check Application Logs:**

```bash
cd c:\Users\Adib\OneDrive\Desktop\mendaur-api
tail -f storage/logs/laravel.log

# Should see no errors related to dropped tables
```

### **Laravel Artisan Check:**

```bash
php artisan tinker
>>> DB::select('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()')
# Expected: 24 tables
>>> exit()
```

---

## 🔄 STEP 8: IF SOMETHING GOES WRONG - ROLLBACK

### **Option A: Restore from backup**

```powershell
# If you backed up the database before dropping:
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql" -u root -p mendaur_db < "C:\Backups\mendaur_db_backup_YYYYMMDD_HHMMSS.sql"

# Enter password when prompted
```

### **Option B: Recreate tables from Laravel migration**

```bash
php artisan migrate:rollback
# Then:
php artisan migrate
```

---

## 📋 FINAL CHECKLIST

### **Before You Start:**
```
[ ] Read this entire guide
[ ] Backup database created
[ ] Backup file verified (> 5 MB)
[ ] Have MySQL GUI (Workbench) or CLI access
[ ] Know your MySQL password
[ ] Have time to complete (30 minutes)
```

### **Execution:**
```
[ ] Verify 5 tables are empty (0 rows)
[ ] Create backup
[ ] Run DROP statements
[ ] Run verification queries
[ ] All checks pass: 24 tables, no dropped tables found
```

### **Post-Execution:**
```
[ ] Test API endpoints
[ ] Check error logs (no errors)
[ ] Confirm application working normally
[ ] Document completion
[ ] Archive backup file
```

---

## 🆘 TROUBLESHOOTING

### **Problem: "Cannot drop table - foreign key constraint"**

**Cause:** Another table references the table being dropped  
**Solution:** Already handled by `SET FOREIGN_KEY_CHECKS = 0;`  
**But if still fails:** Check which table references it
```sql
SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_NAME = 'cache' 
AND TABLE_SCHEMA = 'mendaur_db';
```

### **Problem: "Table doesn't exist"**

**Cause:** Table already dropped or wrong name  
**Solution:** Use `DROP TABLE IF EXISTS` (already in script)  
**Safe to ignore:** This message

### **Problem: "Column doesn't exist" in application**

**Cause:** Application trying to use dropped table  
**Solution:** Shouldn't happen - dropped tables not used  
**Action:** Check application code for references

### **Problem: "Database locked"**

**Cause:** Another process using database  
**Solution:** 
```sql
-- Check active processes
SHOW PROCESSLIST;

-- Kill long-running queries if needed
KILL QUERY process_id;
```

---

## 📝 EXECUTION LOG

Use this to track your execution:

```
Date Started: _______________
Started By: _______________
Backup File: _______________
Backup Size: _____ MB
Backup Verified: [ ] YES [ ] NO

Drop Method Used: [ ] Option A (all at once) [ ] Option B (one by one)
Start Time: _______________
End Time: _______________
Duration: _____ minutes

Verification Status:
[ ] Total tables = 24
[ ] No dropped tables found
[ ] Critical tables exist
[ ] FK relationships = 22
[ ] API endpoints working
[ ] Error logs clean

Overall Status: [ ] SUCCESS [ ] FAILED [ ] PARTIAL

Issues Encountered: ___________________________________________________________

Resolution: ____________________________________________________________________

Sign-off: _________________________ Date: ________________
```

---

## ✅ SUCCESS CRITERIA

All of these must be TRUE:

```
✓ 5 unused tables dropped (cache, cache_locks, jobs, failed_jobs, job_batches)
✓ Total table count = 24
✓ No FK constraint errors
✓ All 24 remaining tables intact
✓ 22 FK relationships intact
✓ No errors in application logs
✓ All API endpoints working
✓ Database integrity verified
✓ Backup file saved
✓ Execution documented

If all TRUE: ✅ SUCCESS - Database cleaned successfully!
If any FALSE: ⚠️ ROLLBACK - Restore from backup
```

---

## 🎯 QUICK REFERENCE

**To DROP (Copy this entire block):**
```sql
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `jobs`;
SET FOREIGN_KEY_CHECKS = 1;
```

**To VERIFY:**
```sql
SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'mendaur_db';
```

**To ROLLBACK:**
```powershell
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql" -u root -p mendaur_db < "C:\Backups\mendaur_db_backup_YYYYMMDD_HHMMSS.sql"
```

---

**Ready to execute?** Follow steps 1-8 above! ✅
