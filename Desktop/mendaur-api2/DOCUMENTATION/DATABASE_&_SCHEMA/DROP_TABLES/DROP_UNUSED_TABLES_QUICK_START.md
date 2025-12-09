# ✅ DROP UNUSED TABLES - QUICK START GUIDE

**Status:** 🟢 READY TO EXECUTE  
**Date:** December 1, 2025

---

## 🎯 WHAT WE'RE DOING

Dropping **5 unused tables** dari Mendaur database, sambil menjaga semua **23 tabel business logic** yang CRITICAL tetap utuh.

### **Drop These 5 (Empty, Unused):**
- `cache` - Cache storage (not used)
- `cache_locks` - Cache locks (not used)
- `jobs` - Database queue (not used)
- `failed_jobs` - Failed queue jobs (not used)
- `job_batches` - Job batching (not used)

### **Keep These 24 (All Used):**
- **23 Business Logic** (CRITICAL) - All kept ✓
- **4 Framework Support** - All kept ✓

---

## 🚀 QUICK EXECUTION (3 OPTIONS)

### **OPTION 1: Via Laravel Migration (RECOMMENDED) ⭐**

```bash
# Step 1: Backup
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
mysqldump -u root -p mendaur_db > "C:\Backups\mendaur_db_backup_$timestamp.sql"

# Step 2: Run migration
cd c:\Users\Adib\OneDrive\Desktop\mendaur-api
php artisan migrate

# Step 3: Verify
php artisan tinker
>>> DB::select('SHOW TABLES;')
>>> exit()

# Done! ✓
```

---

### **OPTION 2: Via SQL Script**

```bash
# Step 1: Backup
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
mysqldump -u root -p mendaur_db > "C:\Backups\mendaur_db_backup_$timestamp.sql"

# Step 2: Run SQL script
mysql -u root -p mendaur_db < DROP_UNUSED_TABLES.sql

# Done! ✓
```

---

### **OPTION 3: Manual SQL Commands**

```sql
-- Backup first (using terminal above)

-- Then in MySQL:
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `jobs`;
SET FOREIGN_KEY_CHECKS = 1;

-- Verify:
SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'mendaur_db';
-- Should show: 24
```

---

## 📊 EXPECTED RESULTS

**Before:**
```
Total Tables: 29
├─ Business: 23 ✓
├─ Framework: 4 ✓
└─ Unused: 5 ❌

Storage: ~50-100 MB
Cleanliness: 70%
```

**After:**
```
Total Tables: 24
├─ Business: 23 ✓
├─ Framework: 4 ✓
└─ Unused: 0 ✓

Storage: ~48-95 MB
Cleanliness: 100%
```

---

## ⏳ TIMELINE

- **Backup:** 5 minutes
- **Execution:** 5 minutes
- **Verification:** 5 minutes
- **Total:** ~15 minutes

---

## 🔄 IF SOMETHING GOES WRONG

### **Rollback via Migration:**
```bash
php artisan migrate:rollback
```

### **Rollback via Backup:**
```bash
mysql -u root -p mendaur_db < C:\Backups\mendaur_db_backup_YYYYMMDD_HHMMSS.sql
```

**Rollback Time:** 2 minutes

---

## ✅ PRE-EXECUTION CHECKLIST

- [ ] Database backed up
- [ ] Backup verified
- [ ] Application offline or maintenance mode set
- [ ] No active API requests
- [ ] No queue workers running
- [ ] Ready to execute

---

## 📚 DETAILED DOCUMENTATION

If you need more details, see:

1. **DROP_UNUSED_TABLES_ANALYSIS.md**
   - Complete analysis of each table
   - Why we're dropping them
   - Risk assessment

2. **DROP_UNUSED_TABLES_EXECUTION_GUIDE.md**
   - Step-by-step execution guide
   - Verification procedures
   - Troubleshooting

3. **DROP_UNUSED_TABLES_SUMMARY.md**
   - Before/after comparison
   - Benefits of cleanup
   - Impact analysis

4. **DROP_UNUSED_TABLES_VISUAL.md**
   - Visual diagrams
   - Table flow charts
   - Statistics

---

## 🎯 DECISION

**Ready to drop unused tables?**

- [ ] **YES** - Execute immediately
- [ ] **MAYBE** - Need more information (see docs above)
- [ ] **NO** - Keep tables as-is

---

**Status:** 🟢 READY TO PROCEED

Choose one option above and execute!
