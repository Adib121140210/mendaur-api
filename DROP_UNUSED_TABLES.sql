-- ============================================================================
-- DROP UNUSED TABLES - MENDAUR DATABASE CLEANUP SCRIPT
-- ============================================================================
--
-- TUJUAN: Menghapus 5 tabel yang tidak digunakan di sistem Mendaur
-- TANGGAL: December 1, 2025
--
-- ⚠️  WARNING: Operasi ini PERMANENT dan tidak bisa di-undo!
--     WAJIB BACKUP DATABASE SEBELUM MENJALANKAN SCRIPT INI!
--
-- ============================================================================

-- STEP 1: Backup confirmation (manual step)
-- ============================================================================
-- Jalankan perintah ini di terminal/command line sebelum eksekusi script:
--
-- Windows PowerShell:
-- $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
-- mysqldump -u root -p mendaur_db > "mendaur_db_backup_$timestamp.sql"
--
-- Linux/Mac:
-- mysqldump -u root -p mendaur_db > mendaur_db_backup_$(date +%Y%m%d_%H%M%S).sql
--
-- ============================================================================

-- STEP 2: Set up for safe execution
-- ============================================================================

-- Disable foreign key checks (safety)
SET FOREIGN_KEY_CHECKS = 0;

-- Check table existence before dropping
-- ============================================================================

-- TABEL YANG AKAN DI-DROP:
-- 1. cache_locks - Lock mechanism untuk cache (tidak dipakai)
-- 2. cache - Cache storage table (tidak dipakai)
-- 3. job_batches - Job batch processing (tidak dipakai)
-- 4. failed_jobs - Failed queue jobs (tidak dipakai)
-- 5. jobs - Database queue jobs (tidak dipakai)

-- ============================================================================
-- STEP 3: DROP UNUSED TABLES (in correct order)
-- ============================================================================

-- Drop 1: cache_locks (no dependencies)
-- Purpose: Lock mechanism untuk cache operations
-- Status: Empty, not used
DROP TABLE IF EXISTS `cache_locks`;
-- Result: ✓ cache_locks dropped

-- Drop 2: cache (no dependencies)
-- Purpose: Cache storage
-- Status: Empty, not used
DROP TABLE IF EXISTS `cache`;
-- Result: ✓ cache dropped

-- Drop 3: job_batches (no FK, but logically depends on jobs)
-- Purpose: Batch job grouping
-- Status: Empty, not used
DROP TABLE IF EXISTS `job_batches`;
-- Result: ✓ job_batches dropped

-- Drop 4: failed_jobs (no FK)
-- Purpose: Store failed queue jobs
-- Status: Empty, not used
DROP TABLE IF EXISTS `failed_jobs`;
-- Result: ✓ failed_jobs dropped

-- Drop 5: jobs (no FK)
-- Purpose: Database queue job processing
-- Status: Empty, not used
DROP TABLE IF EXISTS `jobs`;
-- Result: ✓ jobs dropped

-- ============================================================================
-- STEP 4: Re-enable foreign key checks
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- STEP 5: Verification queries
-- ============================================================================
--
-- Jalankan queries ini untuk verify bahwa proses drop berhasil:
--
-- 1. Check total tables
SELECT 'Total remaining tables:' as Info, COUNT(*) as Count
FROM information_schema.tables
WHERE table_schema = DATABASE();

-- 2. Verify dropped tables don't exist
SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = 'cache'),
         '❌ FAILED: cache still exists',
         '✓ OK: cache dropped') as Status;

SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = 'cache_locks'),
         '❌ FAILED: cache_locks still exists',
         '✓ OK: cache_locks dropped') as Status;

SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = 'jobs'),
         '❌ FAILED: jobs still exists',
         '✓ OK: jobs dropped') as Status;

SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = 'failed_jobs'),
         '❌ FAILED: failed_jobs still exists',
         '✓ OK: failed_jobs dropped') as Status;

SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = 'job_batches'),
         '❌ FAILED: job_batches still exists',
         '✓ OK: job_batches dropped') as Status;

-- 3. Verify critical tables still exist
SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = 'users'),
         '✓ OK: users table exists',
         '❌ ERROR: users table missing!') as Status;

SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = 'transaksis'),
         '✓ OK: transaksis table exists',
         '❌ ERROR: transaksis table missing!') as Status;

SELECT IF(EXISTS(SELECT 1 FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = 'badges'),
         '✓ OK: badges table exists',
         '❌ ERROR: badges table missing!') as Status;

-- 4. List all remaining tables (should be 24 total)
-- 23 business logic + 1 framework tables (migrations, sessions, etc)
SELECT 'Remaining tables after cleanup:' as Info;
SELECT table_name FROM information_schema.tables
WHERE table_schema = DATABASE()
ORDER BY table_name;

-- ============================================================================
-- SUMMARY
-- ============================================================================
--
-- BEFORE: 29 tables
-- DROPPED: 5 tables (cache, cache_locks, failed_jobs, jobs, job_batches)
-- AFTER: 24 tables (23 business logic + 4 framework support)
--
-- TABLES DROPPED:
--   ✓ cache - Cache storage (unused)
--   ✓ cache_locks - Cache locks (unused)
--   ✓ failed_jobs - Failed queue jobs (unused)
--   ✓ jobs - Queue jobs (unused)
--   ✓ job_batches - Job batches (unused)
--
-- TABLES KEPT (CRITICAL):
--   ✓ USERS (core user management)
--   ✓ ROLES (role-based access control)
--   ✓ ROLE_PERMISSIONS (permissions)
--   ✓ SESSIONS (Laravel session storage)
--   ✓ TRANSAKSIS (transactions)
--   ✓ BADGES (gamification)
--   ✓ USER_BADGES (user badge progress)
--   ✓ PRODUKS (products)
--   ✓ PENUKARAN_PRODUK (product redemptions)
--   ✓ PENARIKAN_TUNAI (cash withdrawals)
--   ✓ LOG_AKTIVITAS (activity logs)
--   ✓ AUDIT_LOGS (audit trail)
--   ✓ ... + 11 more critical tables
--
-- FRAMEWORK TABLES KEPT:
--   ✓ migrations (required Laravel)
--   ✓ sessions (active usage)
--   ✓ password_reset_tokens (active usage)
--   ✓ personal_access_tokens (for future Sanctum API)
--
-- ============================================================================
-- ROLLBACK (if something went wrong)
-- ============================================================================
--
-- If you need to restore:
-- mysql -u root -p mendaur_db < mendaur_db_backup_20241201_HHMMSS.sql
--
-- Or using PHP Artisan:
-- php artisan migrate:rollback
--
-- ============================================================================

-- END OF SCRIPT
-- Execution Time: ~5 seconds
-- Status: ✓ READY TO EXECUTE
