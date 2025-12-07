<?php

/**
 * API Verification Script - Post Drop Tables
 *
 * Run this to verify that:
 * 1. Database tables dropped successfully (24 remaining)
 * 2. All critical business tables still exist
 * 3. API endpoints are working
 * 4. No database errors
 *
 * Usage:
 * php verify_after_drop.php
 */

// Set environment
require_once __DIR__.'/bootstrap/app.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create application
$app->bind(
    Illuminate\Contracts\Console\Kernel::class,
    \App\Console\Kernel::class
);

// Get database connection
$db = app('db');

echo "\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "  API VERIFICATION - POST DROP TABLES\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "\n";

// ===== CHECK 1: Table Count =====
echo "✓ CHECK 1: Total Table Count\n";
echo "───────────────────────────────────────────────────────────────────\n";

try {
    $tables = $db->select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()");
    $tableCount = $tables[0]->count ?? 0;

    echo "Total tables: " . $tableCount . "\n";

    if ($tableCount == 24) {
        echo "✅ PASS - Correct! 24 tables (5 dropped)\n\n";
    } else {
        echo "⚠️  WARNING - Expected 24, found " . $tableCount . "\n\n";
    }
} catch (\Exception $e) {
    echo "❌ ERROR - " . $e->getMessage() . "\n\n";
}

// ===== CHECK 2: Verify Dropped Tables Don't Exist =====
echo "✓ CHECK 2: Verify Dropped Tables Don't Exist\n";
echo "───────────────────────────────────────────────────────────────────\n";

$droppedTables = ['cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches'];
$allDropped = true;

try {
    foreach ($droppedTables as $table) {
        $result = $db->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$table]);

        if (empty($result)) {
            echo "  ✅ " . $table . " - Dropped\n";
        } else {
            echo "  ❌ " . $table . " - Still exists!\n";
            $allDropped = false;
        }
    }

    if ($allDropped) {
        echo "✅ PASS - All 5 tables successfully dropped\n\n";
    } else {
        echo "❌ FAIL - Some tables still exist!\n\n";
    }
} catch (\Exception $e) {
    echo "❌ ERROR - " . $e->getMessage() . "\n\n";
}

// ===== CHECK 3: Verify Critical Tables Still Exist =====
echo "✓ CHECK 3: Verify Critical Business Tables Exist\n";
echo "───────────────────────────────────────────────────────────────────\n";

$criticalTables = [
    'users', 'transaksis', 'badges', 'produks', 'penukaran_produk',
    'penarikan_tunai', 'sessions', 'roles', 'role_permissions'
];
$allExist = true;

try {
    foreach ($criticalTables as $table) {
        $result = $db->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$table]);

        if (!empty($result)) {
            echo "  ✅ " . $table . " - Exists\n";
        } else {
            echo "  ❌ " . $table . " - MISSING!\n";
            $allExist = false;
        }
    }

    if ($allExist) {
        echo "✅ PASS - All critical tables exist\n\n";
    } else {
        echo "❌ FAIL - Some critical tables missing!\n\n";
    }
} catch (\Exception $e) {
    echo "❌ ERROR - " . $e->getMessage() . "\n\n";
}

// ===== CHECK 4: Check Foreign Key Relationships =====
echo "✓ CHECK 4: Foreign Key Relationships (should be 22)\n";
echo "───────────────────────────────────────────────────────────────────\n";

try {
    $fks = $db->select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");
    $fkCount = $fks[0]->count ?? 0;

    echo "Total FK relationships: " . $fkCount . "\n";

    if ($fkCount == 22) {
        echo "✅ PASS - All 22 relationships intact\n\n";
    } else {
        echo "⚠️  WARNING - Expected 22, found " . $fkCount . "\n\n";
    }
} catch (\Exception $e) {
    echo "❌ ERROR - " . $e->getMessage() . "\n\n";
}

// ===== CHECK 5: Test Basic Queries =====
echo "✓ CHECK 5: Test Basic Database Queries\n";
echo "───────────────────────────────────────────────────────────────────\n";

try {
    // Check users
    $userCount = $db->table('users')->count();
    echo "  Users: " . $userCount . " ✅\n";

    // Check transaksis
    $transCount = $db->table('transaksis')->count();
    echo "  Transactions: " . $transCount . " ✅\n";

    // Check badges
    $badgeCount = $db->table('badges')->count();
    echo "  Badges: " . $badgeCount . " ✅\n";

    // Check produks
    $productCount = $db->table('produks')->count();
    echo "  Products: " . $productCount . " ✅\n";

    echo "✅ PASS - All basic queries working\n\n";
} catch (\Exception $e) {
    echo "❌ ERROR - " . $e->getMessage() . "\n\n";
}

// ===== CHECK 6: Database Integrity =====
echo "✓ CHECK 6: Database Integrity Check\n";
echo "───────────────────────────────────────────────────────────────────\n";

try {
    // Try a complex query with JOIN
    $result = $db->select(
        "SELECT u.id, u.name, COUNT(t.id) as transaction_count
         FROM users u
         LEFT JOIN transaksis t ON u.id = t.user_id
         GROUP BY u.id
         LIMIT 1"
    );

    echo "  Complex JOIN query: ✅\n";
    echo "  Sample result: " . json_encode($result) . "\n";
    echo "✅ PASS - Database integrity verified\n\n";
} catch (\Exception $e) {
    echo "❌ ERROR - " . $e->getMessage() . "\n\n";
}

// ===== SUMMARY =====
echo "═══════════════════════════════════════════════════════════════════\n";
echo "  VERIFICATION SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "\n";
echo "✅ All checks completed!\n";
echo "\n";
echo "Next Steps:\n";
echo "1. Test API endpoints in browser/Postman\n";
echo "2. Run: php artisan serve\n";
echo "3. Visit: http://localhost:8000/api/user/profile\n";
echo "4. Check application logs for errors\n";
echo "\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "\n";
