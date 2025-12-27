-- ================================================================
-- SQL Script: Fix Inconsistent Role-Level Data
-- ================================================================
-- Purpose: Synchronize 'level' column with 'role_id' based on
--          the role's level_akses value
-- Date: 2025-12-24
-- ================================================================

-- Backup current state (optional)
-- CREATE TABLE users_backup_20251224 AS SELECT * FROM users;

-- ================================================================
-- 1. Fix Superadmin users (level_akses = 3)
-- ================================================================
UPDATE users
SET level = 'superadmin',
    updated_at = NOW()
WHERE role_id IN (
    SELECT role_id
    FROM roles
    WHERE level_akses = 3
)
AND level != 'superadmin'
AND deleted_at IS NULL;

-- Check affected rows
SELECT
    'Superadmin' as fix_type,
    COUNT(*) as rows_affected,
    GROUP_CONCAT(DISTINCT old_level) as old_levels
FROM (
    SELECT user_id, level as old_level
    FROM users_backup_20251224
    WHERE role_id IN (SELECT role_id FROM roles WHERE level_akses = 3)
    AND level != 'superadmin'
) as changes;

-- ================================================================
-- 2. Fix Admin users (level_akses = 2)
-- ================================================================
UPDATE users
SET level = 'admin',
    updated_at = NOW()
WHERE role_id IN (
    SELECT role_id
    FROM roles
    WHERE level_akses = 2
)
AND level != 'admin'
AND deleted_at IS NULL;

-- Check affected rows
SELECT
    'Admin' as fix_type,
    COUNT(*) as rows_affected,
    GROUP_CONCAT(DISTINCT old_level) as old_levels
FROM (
    SELECT user_id, level as old_level
    FROM users_backup_20251224
    WHERE role_id IN (SELECT role_id FROM roles WHERE level_akses = 2)
    AND level != 'admin'
) as changes;

-- ================================================================
-- 3. Fix Nasabah users with admin/superadmin level (level_akses = 1)
-- ================================================================
UPDATE users
SET level = 'bronze',
    updated_at = NOW()
WHERE role_id IN (
    SELECT role_id
    FROM roles
    WHERE level_akses = 1
)
AND level IN ('admin', 'superadmin')
AND deleted_at IS NULL;

-- Check affected rows
SELECT
    'Nasabah' as fix_type,
    COUNT(*) as rows_affected,
    GROUP_CONCAT(DISTINCT old_level) as old_levels
FROM (
    SELECT user_id, level as old_level
    FROM users_backup_20251224
    WHERE role_id IN (SELECT role_id FROM roles WHERE level_akses = 1)
    AND level IN ('admin', 'superadmin')
) as changes;

-- ================================================================
-- 4. Fix Nasabah users with NULL or invalid level
-- ================================================================
UPDATE users
SET level = 'bronze',
    updated_at = NOW()
WHERE role_id IN (
    SELECT role_id
    FROM roles
    WHERE level_akses = 1
)
AND (
    level IS NULL
    OR level NOT IN ('bronze', 'silver', 'gold', 'admin', 'superadmin')
)
AND deleted_at IS NULL;

-- ================================================================
-- 5. Verification Queries
-- ================================================================

-- Count users by role type and level
SELECT
    r.nama_role,
    r.level_akses,
    u.level,
    COUNT(*) as user_count
FROM users u
JOIN roles r ON u.role_id = r.role_id
WHERE u.deleted_at IS NULL
GROUP BY r.nama_role, r.level_akses, u.level
ORDER BY r.level_akses DESC, u.level;

-- Find any remaining inconsistencies
SELECT
    u.user_id,
    u.nama,
    u.email,
    r.nama_role,
    r.level_akses,
    u.level,
    CASE
        WHEN r.level_akses = 3 AND u.level != 'superadmin' THEN '❌ Should be superadmin'
        WHEN r.level_akses = 2 AND u.level != 'admin' THEN '❌ Should be admin'
        WHEN r.level_akses = 1 AND u.level IN ('admin', 'superadmin') THEN '❌ Should be bronze/silver/gold'
        WHEN r.level_akses = 1 AND u.level NOT IN ('bronze', 'silver', 'gold') THEN '❌ Invalid nasabah level'
        ELSE '✅ OK'
    END as status
FROM users u
JOIN roles r ON u.role_id = r.role_id
WHERE u.deleted_at IS NULL
HAVING status LIKE '❌%'
ORDER BY r.level_akses DESC;

-- ================================================================
-- Summary Report
-- ================================================================
SELECT
    'Total Active Users' as metric,
    COUNT(*) as value
FROM users
WHERE deleted_at IS NULL

UNION ALL

SELECT
    CONCAT('Superadmin (level=superadmin)'),
    COUNT(*)
FROM users u
JOIN roles r ON u.role_id = r.role_id
WHERE r.level_akses = 3
  AND u.level = 'superadmin'
  AND u.deleted_at IS NULL

UNION ALL

SELECT
    CONCAT('Admin (level=admin)'),
    COUNT(*)
FROM users u
JOIN roles r ON u.role_id = r.role_id
WHERE r.level_akses = 2
  AND u.level = 'admin'
  AND u.deleted_at IS NULL

UNION ALL

SELECT
    CONCAT('Nasabah - Bronze'),
    COUNT(*)
FROM users u
JOIN roles r ON u.role_id = r.role_id
WHERE r.level_akses = 1
  AND u.level = 'bronze'
  AND u.deleted_at IS NULL

UNION ALL

SELECT
    CONCAT('Nasabah - Silver'),
    COUNT(*)
FROM users u
JOIN roles r ON u.role_id = r.role_id
WHERE r.level_akses = 1
  AND u.level = 'silver'
  AND u.deleted_at IS NULL

UNION ALL

SELECT
    CONCAT('Nasabah - Gold'),
    COUNT(*)
FROM users u
JOIN roles r ON u.role_id = r.role_id
WHERE r.level_akses = 1
  AND u.level = 'gold'
  AND u.deleted_at IS NULL;

-- ================================================================
-- NOTES:
-- ================================================================
-- 1. Backup table created: users_backup_20251224
-- 2. Only affects non-deleted users (deleted_at IS NULL)
-- 3. Achievement levels (bronze/silver/gold) are preserved for nasabah
-- 4. Admin/Superadmin levels are enforced based on role's level_akses
-- ================================================================
