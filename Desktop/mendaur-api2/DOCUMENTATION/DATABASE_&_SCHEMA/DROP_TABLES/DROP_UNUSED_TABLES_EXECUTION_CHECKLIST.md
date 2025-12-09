# ✅ DROP UNUSED TABLES - EXECUTION CHECKLIST

**Date:** December 1, 2025  
**Project:** Mendaur Database Cleanup  
**Status:** Ready for Execution

---

## 📋 DECISION PHASE

```
[ ] Project manager/lead has reviewed the proposal
[ ] Decision: APPROVED to proceed with table drop
[ ] Approval Date: _______________
[ ] Approved By: _______________
[ ] Comments/Notes: _______________________________________________________________

DECISION: [ ] YES, PROCEED  [ ] NO, WAIT  [ ] MODIFY APPROACH
```

---

## 🔐 PRE-EXECUTION PHASE

### **Database Backup (CRITICAL)**

```
[ ] 1.1 Backup location identified
    └─ Path: C:\Backups\
    
[ ] 1.2 Backup command ready
    └─ Command: mysqldump -u root -p mendaur_db > "C:\Backups\mendaur_db_backup_$timestamp.sql"
    
[ ] 1.3 Execute backup
    └─ Time: _______________
    └─ Status: [ ] SUCCESS [ ] FAILED
    
[ ] 1.4 Verify backup file
    └─ File: _______________
    └─ Size: _____ MB (should be > 5 MB)
    └─ Checksum: _______________
    └─ Status: [ ] OK [ ] FAILED
    
[ ] 1.5 Test backup restore (optional but recommended)
    └─ Restore to test database: [ ] YES [ ] NO
    └─ Status: [ ] SUCCESSFUL [ ] FAILED
```

### **System Preparation**

```
[ ] 2.1 Application status
    └─ Current state: [ ] RUNNING [ ] MAINTENANCE [ ] OFFLINE
    
[ ] 2.2 Set maintenance mode
    └─ Command: php artisan down
    └─ Status: [ ] DONE [ ] N/A
    
[ ] 2.3 Verify no active connections
    └─ Command: SELECT * FROM INFORMATION_SCHEMA.PROCESSLIST WHERE DB = 'mendaur_db';
    └─ Result: [ ] NO CONNECTIONS [ ] CONNECTIONS EXIST
    └─ Status: [ ] SAFE [ ] WAIT (kill connections)
    
[ ] 2.4 Stop queue workers (if any)
    └─ Status: [ ] STOPPED [ ] N/A
    
[ ] 2.5 Verify no cache operations
    └─ Status: [ ] VERIFIED [ ] N/A
```

### **Code & Documentation Review**

```
[ ] 3.1 Verify no code references to dropped tables
    └─ grep -r "cache" app/                    # grep -r "jobs" app/
    └─ Result: [ ] NO REFERENCES [ ] FOUND (acceptable)
    
[ ] 3.2 Verify migration file exists
    └─ File: database/migrations/2024_12_01_000000_drop_unused_tables.php
    └─ Status: [ ] EXISTS [ ] MISSING
    └─ Content verified: [ ] YES [ ] NO
    
[ ] 3.3 Verify SQL script exists
    └─ File: DROP_UNUSED_TABLES.sql
    └─ Status: [ ] EXISTS [ ] MISSING
    
[ ] 3.4 Documentation reviewed
    └─ QUICK_START.md: [ ] READ [ ] NOT READ
    └─ ANALYSIS.md: [ ] READ [ ] NOT READ
    └─ EXECUTION_GUIDE.md: [ ] READ [ ] NOT READ
```

### **Team Communication**

```
[ ] 4.1 Stakeholders notified
    └─ Notified: [ ] YES [ ] NO
    └─ Date/Time: _______________
    
[ ] 4.2 Maintenance window scheduled
    └─ Start: _______________
    └─ End: _______________
    
[ ] 4.3 Rollback plan communicated
    └─ Plan understood: [ ] YES [ ] NO
    └─ Rollback time estimate: 2 minutes
    
[ ] 4.4 Support team on standby
    └─ Status: [ ] READY [ ] N/A
```

---

## 🚀 EXECUTION PHASE

### **Choose Execution Method**

```
SELECTED METHOD:
[ ] Option A: Laravel Migration (RECOMMENDED)
[ ] Option B: SQL Script
[ ] Option C: Manual SQL Commands

Reason for selection: ________________________________________________________________
```

### **Option A: Laravel Migration Execution**

```
[ ] 5.1 Navigate to project directory
    └─ cd c:\Users\Adib\OneDrive\Desktop\mendaur-api
    └─ Status: [ ] DONE
    
[ ] 5.2 Clear Laravel caches (safety)
    └─ php artisan cache:clear
    └─ Status: [ ] DONE
    
[ ] 5.3 Run migration
    └─ Command: php artisan migrate
    └─ Start Time: _______________
    └─ End Time: _______________
    └─ Duration: _____ seconds
    
[ ] 5.4 Check output for errors
    └─ Output:
    ```
    _______________________________________________________________________________
    _______________________________________________________________________________
    ```
    └─ Status: [ ] SUCCESS [ ] ERRORS [ ] WARNINGS
    
[ ] 5.5 Expected output
    └─ "✓ Dropped: cache_locks"
    └─ "✓ Dropped: cache"
    └─ "✓ Dropped: job_batches"
    └─ "✓ Dropped: failed_jobs"
    └─ "✓ Dropped: jobs"
    └─ "✓ All unused tables dropped successfully!"
    
    All expected lines found: [ ] YES [ ] NO
```

### **Option B: SQL Script Execution**

```
[ ] 5.1 Run SQL script
    └─ Command: mysql -u root -p mendaur_db < DROP_UNUSED_TABLES.sql
    └─ Start Time: _______________
    └─ End Time: _______________
    
[ ] 5.2 Verify execution
    └─ Status: [ ] SUCCESS [ ] ERRORS
    
[ ] 5.3 Connection method
    └─ [ ] MySQL CLI
    └─ [ ] MySQL Workbench
    └─ [ ] Other: _______________
```

### **Option C: Manual SQL Execution**

```
[ ] 5.1 Connect to database
    └─ mysql -u root -p mendaur_db
    └─ Status: [ ] CONNECTED
    
[ ] 5.2 Execute commands one by one
    └─ Command 1: SET FOREIGN_KEY_CHECKS = 0;
       └─ Status: [ ] EXECUTED
    
    └─ Command 2: DROP TABLE IF EXISTS `cache_locks`;
       └─ Status: [ ] EXECUTED
    
    └─ Command 3: DROP TABLE IF EXISTS `cache`;
       └─ Status: [ ] EXECUTED
    
    └─ Command 4: DROP TABLE IF EXISTS `job_batches`;
       └─ Status: [ ] EXECUTED
    
    └─ Command 5: DROP TABLE IF EXISTS `failed_jobs`;
       └─ Status: [ ] EXECUTED
    
    └─ Command 6: DROP TABLE IF EXISTS `jobs`;
       └─ Status: [ ] EXECUTED
    
    └─ Command 7: SET FOREIGN_KEY_CHECKS = 1;
       └─ Status: [ ] EXECUTED
```

---

## ✔️ VERIFICATION PHASE

### **Immediate Verification**

```
[ ] 6.1 Check total table count
    Command: php artisan tinker
    >>> DB::select("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()");
    
    Result: _______________
    Expected: 24 tables
    Status: [ ] CORRECT [ ] WRONG
    
[ ] 6.2 Verify dropped tables don't exist
    >>> Schema::hasTable('cache');       # Should be false
    >>> Schema::hasTable('cache_locks'); # Should be false
    >>> Schema::hasTable('jobs');        # Should be false
    >>> Schema::hasTable('failed_jobs'); # Should be false
    >>> Schema::hasTable('job_batches'); # Should be false
    
    All false: [ ] YES [ ] NO
    
[ ] 6.3 Verify critical tables exist
    >>> Schema::hasTable('users');       # Should be true
    >>> Schema::hasTable('transaksis');  # Should be true
    >>> Schema::hasTable('badges');      # Should be true
    
    All true: [ ] YES [ ] NO
    
[ ] 6.4 Check foreign key relationships
    >>> DB::select("SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");
    
    Result: _______________
    Expected: 22 relationships
    Status: [ ] CORRECT [ ] WRONG
    
[ ] 6.5 Exit Tinker
    >>> exit()
    └─ Status: [ ] DONE
```

### **Application Testing**

```
[ ] 6.6 Restart application
    └─ php artisan up (if maintenance mode was on)
    └─ Status: [ ] DONE
    
[ ] 6.7 Check error logs
    └─ tail -f storage/logs/laravel.log
    └─ Errors: [ ] NONE [ ] FOUND
    └─ If found, describe: ________________________________________________________________
    
[ ] 6.8 Test API endpoints
    ```
    [ ] GET /api/user/profile
        └─ Status: [ ] 200 OK [ ] ERROR
        └─ Response time: _____ ms
    
    [ ] GET /api/points
        └─ Status: [ ] 200 OK [ ] ERROR
        └─ Response time: _____ ms
    
    [ ] GET /api/products
        └─ Status: [ ] 200 OK [ ] ERROR
        └─ Response time: _____ ms
    
    [ ] GET /api/badges
        └─ Status: [ ] 200 OK [ ] ERROR
        └─ Response time: _____ ms
    ```
    
    All endpoints working: [ ] YES [ ] NO

[ ] 6.9 Test critical workflows
    ```
    [ ] User login
        └─ Status: [ ] WORKING [ ] BROKEN
    
    [ ] View waste deposit
        └─ Status: [ ] WORKING [ ] BROKEN
    
    [ ] View points balance
        └─ Status: [ ] WORKING [ ] BROKEN
    
    [ ] View badges
        └─ Status: [ ] WORKING [ ] BROKEN
    ```
    
    All workflows: [ ] WORKING [ ] ISSUES
```

### **Database Integrity Check**

```
[ ] 6.10 Run database integrity check
    ```
    Command: php artisan tinker
    >>> DB::select("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");
    ```
    
    Result: [ ] 22 FK relationships intact [ ] MISMATCH
    
[ ] 6.11 Check for orphaned records
    ```
    >>> DB::select("SELECT COUNT(*) FROM users");
    >>> DB::select("SELECT COUNT(*) FROM transaksis");
    >>> DB::select("SELECT COUNT(*) FROM badges");
    ```
    
    All counts: [ ] NORMAL [ ] UNEXPECTED
```

---

## 📊 FINAL VERIFICATION SUMMARY

```
EXECUTION SUCCESS CRITERIA:

All of the following must be TRUE:

✓ Criteria 1: 5 unused tables dropped
  └─ cache: [ ] DROPPED
  └─ cache_locks: [ ] DROPPED
  └─ jobs: [ ] DROPPED
  └─ failed_jobs: [ ] DROPPED
  └─ job_batches: [ ] DROPPED
  
✓ Criteria 2: Total table count = 24
  └─ Count: [ ] 24 tables
  
✓ Criteria 3: All 23 business tables exist
  └─ Check: [ ] PASSED
  
✓ Criteria 4: All 4 framework tables exist
  └─ Check: [ ] PASSED
  
✓ Criteria 5: 22 FK relationships intact
  └─ Count: [ ] 22 relationships
  
✓ Criteria 6: No errors in application logs
  └─ Status: [ ] CLEAN
  
✓ Criteria 7: All API endpoints working
  └─ Status: [ ] ALL OK
  
✓ Criteria 8: Database integrity verified
  └─ Status: [ ] INTACT

OVERALL STATUS:
[ ] ✅ SUCCESS - All criteria met
[ ] ⚠️ PARTIAL - Some issues, but usable
[ ] ❌ FAILED - Need rollback

If SUCCESS: Proceed to COMPLETION PHASE
If FAILED: Proceed to ROLLBACK PHASE
```

---

## 🔄 ROLLBACK PROCEDURE (IF NEEDED)

```
ONLY EXECUTE THIS IF VERIFICATION FAILED!

[ ] 7.1 Decision to rollback
    Reason: _______________________________________________________________________
    
    [ ] YES, ROLLBACK NEEDED
    [ ] NO, KEEP AS IS
    
    Approver: _______________
    Time: _______________

[ ] 7.2 Restore from backup (if migration rollback fails)
    
    Command:
    mysql -u root -p mendaur_db < "C:\Backups\mendaur_db_backup_YYYYMMDD_HHMMSS.sql"
    
    Status: [ ] EXECUTING [ ] DONE [ ] ERROR
    
    Duration: _____ seconds
    
[ ] 7.3 Verify rollback
    >>> DB::select('SHOW TABLES;')
    
    Should show 29 tables (original count)
    Result: [ ] CORRECT [ ] INCORRECT
    
[ ] 7.4 Re-verify application
    [ ] Restart app
    [ ] Test endpoints
    [ ] Check logs
    
    Status: [ ] OK [ ] STILL BROKEN
```

---

## 📝 COMPLETION PHASE

### **Documentation & Sign-off**

```
[ ] 8.1 Execution completed successfully
    Date: _______________
    Time: _______________
    Duration: _______________
    
[ ] 8.2 Log execution details
    Method used: [ ] Migration [ ] SQL [ ] Manual
    Executed by: _______________
    Reviewed by: _______________
    
[ ] 8.3 Update documentation
    [ ] CHANGELOG updated
    [ ] README updated
    [ ] Architecture docs updated
    [ ] Team wiki updated
    
[ ] 8.4 Archive backup
    Location: C:\Backups\mendaur_db_backup_20241201_HHMMSS.sql
    Size: _____ MB
    Checksum: _______________
    Archive: [ ] YES [ ] NO
    
[ ] 8.5 Final sign-off
    Project Lead: _______________  Date: _______________
    Database Admin: _______________  Date: _______________
    Technical Reviewer: _______________  Date: _______________
```

---

## 🎯 FINAL CHECKLIST SUMMARY

```
EXECUTION CHECKLIST STATUS:

Phase 1 - DECISION
    [ ] Complete - Approval obtained

Phase 2 - PRE-EXECUTION
    [ ] Complete - All systems checked
    [ ] Complete - Backup created & verified
    [ ] Complete - Team notified
    [ ] Complete - Maintenance window set

Phase 3 - EXECUTION
    [ ] Complete - Migration/SQL executed
    [ ] Complete - No errors reported
    [ ] Complete - Execution logged

Phase 4 - VERIFICATION
    [ ] Complete - All criteria verified
    [ ] Complete - Database integrity confirmed
    [ ] Complete - Application tested
    [ ] Complete - API endpoints working
    [ ] Complete - Error logs clean

Phase 5 - COMPLETION
    [ ] Complete - Documentation updated
    [ ] Complete - Backup archived
    [ ] Complete - Team notified
    [ ] Complete - Sign-off obtained

OVERALL COMPLETION: _____ % (should be 100%)

READY FOR PRODUCTION: [ ] YES [ ] NO [ ] CONDITIONAL

NEXT STEPS: ___________________________________________________________________
```

---

## 📞 ISSUE TRACKING

```
If issues occur during execution:

Issue 1:
    Description: ________________________________________________________________
    When: _______________
    Severity: [ ] CRITICAL [ ] HIGH [ ] MEDIUM [ ] LOW
    Resolution: _________________________________________________________________
    Status: [ ] RESOLVED [ ] PENDING

Issue 2:
    Description: ________________________________________________________________
    When: _______________
    Severity: [ ] CRITICAL [ ] HIGH [ ] MEDIUM [ ] LOW
    Resolution: _________________________________________________________________
    Status: [ ] RESOLVED [ ] PENDING

Issue 3:
    Description: ________________________________________________________________
    When: _______________
    Severity: [ ] CRITICAL [ ] HIGH [ ] MEDIUM [ ] LOW
    Resolution: _________________________________________________________________
    Status: [ ] RESOLVED [ ] PENDING
```

---

## ✅ PROJECT COMPLETION

```
Date Started: _______________
Date Completed: _______________
Total Duration: _______________

Tables Dropped: 5 ✓
├─ cache ✓
├─ cache_locks ✓
├─ jobs ✓
├─ failed_jobs ✓
└─ job_batches ✓

Tables Preserved: 24 ✓
├─ Business Logic: 23 ✓
└─ Framework Support: 4 ✓

Final Status: [ ] ✅ SUCCESSFUL [ ] ⚠️ PARTIAL [ ] ❌ FAILED

Signed by (Project Lead):

Name: _______________
Title: _______________
Date: _______________
Signature: _______________

---

## 🎉 PROJECT COMPLETED SUCCESSFULLY!

Database cleanup completed as planned.
- 5 unused tables removed
- 24 essential tables preserved
- Full backup available
- All systems operational
- Ready for production

Thank you for following the checklist!
```

---

**Checklist Version:** 1.0  
**Created:** December 1, 2025  
**Status:** Ready to Use  
**Estimated Completion Time:** 1-2 hours (including all phases)
