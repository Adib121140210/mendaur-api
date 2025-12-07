-- ================================================================
-- USER SEED DATA VERIFICATION QUERIES
-- ================================================================
-- Run these queries to verify user seed data is correct

-- ================================================================
-- 1. VIEW ALL USERS WITH KEY FIELDS
-- ================================================================
SELECT
    id,
    nama,
    email,
    tipe_nasabah,
    total_poin,
    poin_tercatat,
    nama_bank,
    nomor_rekening,
    atas_nama_rekening
FROM users
ORDER BY id;

-- ================================================================
-- 2. VIEW KONVENSIONAL USERS (SHOULD HAVE NO BANKING INFO)
-- ================================================================
SELECT
    id,
    nama,
    email,
    tipe_nasabah,
    total_poin,
    poin_tercatat,
    nama_bank,
    nomor_rekening,
    atas_nama_rekening
FROM users
WHERE tipe_nasabah = 'konvensional'
ORDER BY id;

-- Expected: nama_bank = NULL, nomor_rekening = NULL, atas_nama_rekening = NULL

-- ================================================================
-- 3. VIEW MODERN USERS (SHOULD HAVE BANKING INFO)
-- ================================================================
SELECT
    id,
    nama,
    email,
    tipe_nasabah,
    total_poin,
    poin_tercatat,
    nama_bank,
    nomor_rekening,
    atas_nama_rekening
FROM users
WHERE tipe_nasabah = 'modern'
ORDER BY id;

-- Expected: nama_bank != NULL, nomor_rekening != NULL, atas_nama_rekening != NULL

-- ================================================================
-- 4. CHECK FOR DATA ISSUES
-- ================================================================

-- Check konvensional users that HAVE banking info (SHOULD BE 0)
SELECT COUNT(*) as 'Konvensional dengan banking info (ERROR)'
FROM users
WHERE tipe_nasabah = 'konvensional'
AND (nama_bank IS NOT NULL
     OR nomor_rekening IS NOT NULL
     OR atas_nama_rekening IS NOT NULL);

-- Result should be: 0 (no errors)

-- ================================================================
-- 5. CHECK MODERN USERS HAVE COMPLETE BANKING INFO
-- ================================================================

-- Check modern users missing banking info (SHOULD BE 0)
SELECT COUNT(*) as 'Modern users missing banking info (ERROR)'
FROM users
WHERE tipe_nasabah = 'modern'
AND (nama_bank IS NULL
     OR nomor_rekening IS NULL
     OR atas_nama_rekening IS NULL);

-- Result should be: 0 (no errors)

-- ================================================================
-- 6. COUNT BY TYPE
-- ================================================================
SELECT
    tipe_nasabah,
    COUNT(*) as 'Jumlah'
FROM users
GROUP BY tipe_nasabah;

-- Expected:
-- konvensional | 4 (atau jumlah konvensional users)
-- modern       | 2 (atau jumlah modern users)

-- ================================================================
-- 7. DETAILED SUMMARY REPORT
-- ================================================================
SELECT
    'SUMMARY REPORT' as 'Report',
    (SELECT COUNT(*) FROM users WHERE tipe_nasabah = 'konvensional') as 'Konvensional Count',
    (SELECT COUNT(*) FROM users WHERE tipe_nasabah = 'modern') as 'Modern Count',
    (SELECT COUNT(*) FROM users WHERE tipe_nasabah = 'konvensional'
     AND (nama_bank IS NOT NULL OR nomor_rekening IS NOT NULL)) as 'Konv with banking (should be 0)',
    (SELECT COUNT(*) FROM users WHERE tipe_nasabah = 'modern'
     AND (nama_bank IS NULL OR nomor_rekening IS NULL)) as 'Modern missing banking (should be 0)',
    COUNT(*) as 'Total Users'
FROM users;

-- ================================================================
-- 8. VERIFY ALL FIELDS ARE NOT NULL (WHERE REQUIRED)
-- ================================================================

-- Check required fields for all users
SELECT
    id,
    nama,
    email,
    CASE
        WHEN id IS NULL THEN 'MISSING: id'
        WHEN nama IS NULL THEN 'MISSING: nama'
        WHEN email IS NULL THEN 'MISSING: email'
        WHEN tipe_nasabah IS NULL THEN 'MISSING: tipe_nasabah'
        WHEN total_poin IS NULL THEN 'MISSING: total_poin'
        WHEN poin_tercatat IS NULL THEN 'MISSING: poin_tercatat'
        ELSE 'OK'
    END as 'Status'
FROM users
WHERE
    id IS NULL
    OR nama IS NULL
    OR email IS NULL
    OR tipe_nasabah IS NULL
    OR total_poin IS NULL
    OR poin_tercatat IS NULL;

-- Result should be: empty (no missing required fields)

-- ================================================================
-- EXPECTED RESULTS SUMMARY
-- ================================================================
/*

After running these queries, expected results:

1. All users displayed with their data ✓
2. Konvensional users: ALL have NULL banking info ✓
3. Modern users: ALL have banking info ✓
4. Konvensional dengan banking info = 0 ✓
5. Modern missing banking info = 0 ✓
6. Konvensional count = 4, Modern count = 2 ✓
7. All counters consistent ✓
8. No missing required fields ✓

If ALL these checks pass ✓, your data is CORRECT and ready for production!

*/
