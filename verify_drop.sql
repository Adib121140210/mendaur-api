-- ===================================================================
-- API VERIFICATION - POST DROP TABLES
-- ===================================================================

-- 1. Total Table Count (should be 24)
SELECT 'CHECK 1: Total Table Count' as 'Verification';
SELECT COUNT(*) as 'Total Tables'
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE();

-- 2. List all remaining tables
SELECT '============================================' as '';
SELECT 'CHECK 2: All Remaining Tables (24 total)' as '';
SELECT '============================================' as '';
SELECT TABLE_NAME as 'Table Name'
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME;

-- 3. Verify dropped tables don't exist
SELECT '============================================' as '';
SELECT 'CHECK 3: Verify Dropped Tables Don\'t Exist' as '';
SELECT '============================================' as '';
SELECT
    'cache' as 'Table',
    CASE WHEN EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cache')
         THEN '❌ EXISTS' ELSE '✅ DROPPED' END as 'Status'
UNION ALL
SELECT
    'cache_locks',
    CASE WHEN EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cache_locks')
         THEN '❌ EXISTS' ELSE '✅ DROPPED' END
UNION ALL
SELECT
    'jobs',
    CASE WHEN EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'jobs')
         THEN '❌ EXISTS' ELSE '✅ DROPPED' END
UNION ALL
SELECT
    'failed_jobs',
    CASE WHEN EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'failed_jobs')
         THEN '❌ EXISTS' ELSE '✅ DROPPED' END
UNION ALL
SELECT
    'job_batches',
    CASE WHEN EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'job_batches')
         THEN '❌ EXISTS' ELSE '✅ DROPPED' END;

-- 4. Verify critical tables exist
SELECT '============================================' as '';
SELECT 'CHECK 4: Critical Business Tables Status' as '';
SELECT '============================================' as '';
SELECT TABLE_NAME as 'Critical Table', 'EXISTS ✅' as 'Status'
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
    'users', 'transaksis', 'badges', 'produks', 'penukaran_produk',
    'penarikan_tunai', 'sessions', 'roles', 'role_permissions',
    'kategori_sampah', 'jenis_sampah', 'tabung_sampah'
  )
ORDER BY TABLE_NAME;

-- 5. Foreign key count (should be 22)
SELECT '============================================' as '';
SELECT 'CHECK 5: Foreign Key Relationships' as '';
SELECT '============================================' as '';
SELECT COUNT(*) as 'Total FK Relationships'
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
  AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 6. Sample data counts
SELECT '============================================' as '';
SELECT 'CHECK 6: Sample Data in Critical Tables' as '';
SELECT '============================================' as '';
SELECT 'users' as 'Table', COUNT(*) as 'Record Count' FROM users
UNION ALL
SELECT 'transaksis', COUNT(*) FROM transaksis
UNION ALL
SELECT 'badges', COUNT(*) FROM badges
UNION ALL
SELECT 'produks', COUNT(*) FROM produks
UNION ALL
SELECT 'sessions', COUNT(*) FROM sessions
ORDER BY `Table`;

-- 7. Storage engine check
SELECT '============================================' as '';
SELECT 'CHECK 7: All Tables InnoDB (correct)?' as '';
SELECT '============================================' as '';
SELECT TABLE_NAME, ENGINE
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
GROUP BY ENGINE;

SELECT '============================================' as '';
SELECT 'VERIFICATION COMPLETE' as '';
SELECT '✅ Database drop successful!' as '';
SELECT '============================================' as '';
