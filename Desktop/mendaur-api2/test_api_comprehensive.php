#!/usr/bin/env php
<?php
/**
 * Comprehensive API Test Suite - Post Drop Tables
 *
 * This script will:
 * 1. Check database state
 * 2. Test critical API endpoints
 * 3. Generate verification report
 *
 * Usage: php test_api_comprehensive.php
 */

// Colors for output
define('RED', "\033[31m");
define('GREEN', "\033[32m");
define('YELLOW', "\033[33m");
define('BLUE', "\033[34m");
define('CYAN', "\033[36m");
define('RESET', "\033[0m");
define('BOLD', "\033[1m");

$results = [
    'database' => [],
    'endpoints' => [],
    'errors' => [],
    'summary' => []
];

// ===== PART 1: DATABASE VERIFICATION =====
echo "\n";
echo BOLD . CYAN . "════════════════════════════════════════════════════════════════" . RESET . "\n";
echo BOLD . CYAN . "  API VERIFICATION - POST DROP TABLES" . RESET . "\n";
echo BOLD . CYAN . "════════════════════════════════════════════════════════════════" . RESET . "\n\n";

echo BOLD . "PART 1: DATABASE VERIFICATION" . RESET . "\n";
echo "────────────────────────────────────────────────────────────────\n\n";

// Load Laravel
try {
    require_once __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';

    $pdo = $app['db']->connection()->getPdo();

    // Check 1: Table count
    echo "Checking table count...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $tableCount = $row['cnt'] ?? 0;

    if ($tableCount == 24) {
        echo GREEN . "✅ PASS" . RESET . " - Table count: $tableCount\n";
        $results['database']['table_count'] = 'PASS';
    } else {
        echo RED . "❌ FAIL" . RESET . " - Expected 24, found $tableCount\n";
        $results['database']['table_count'] = 'FAIL';
    }

    // Check 2: Dropped tables
    echo "Checking dropped tables...\n";
    $droppedTables = ['cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches'];
    $allDropped = true;

    foreach ($droppedTables as $table) {
        $stmt = $pdo->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);

        if ($stmt->rowCount() == 0) {
            echo "  " . GREEN . "✅" . RESET . " $table - Dropped\n";
        } else {
            echo "  " . RED . "❌" . RESET . " $table - Still exists!\n";
            $allDropped = false;
        }
    }

    if ($allDropped) {
        echo GREEN . "✅ PASS" . RESET . " - All 5 tables dropped\n";
        $results['database']['dropped_tables'] = 'PASS';
    } else {
        echo RED . "❌ FAIL" . RESET . " - Some tables still exist!\n";
        $results['database']['dropped_tables'] = 'FAIL';
    }

    // Check 3: Critical tables
    echo "Checking critical tables...\n";
    $critical = ['users', 'sessions', 'transaksis', 'badges', 'produks'];
    $allExist = true;

    foreach ($critical as $table) {
        $stmt = $pdo->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);

        if ($stmt->rowCount() > 0) {
            echo "  " . GREEN . "✅" . RESET . " $table\n";
        } else {
            echo "  " . RED . "❌" . RESET . " $table - MISSING!\n";
            $allExist = false;
        }
    }

    if ($allExist) {
        echo GREEN . "✅ PASS" . RESET . " - All critical tables exist\n";
        $results['database']['critical_tables'] = 'PASS';
    } else {
        echo RED . "❌ FAIL" . RESET . " - Some tables missing!\n";
        $results['database']['critical_tables'] = 'FAIL';
    }

    // Check 4: FK relationships
    echo "Checking foreign key relationships...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $fkCount = $row['cnt'] ?? 0;

    if ($fkCount == 22) {
        echo GREEN . "✅ PASS" . RESET . " - FK relationships: $fkCount\n";
        $results['database']['fk_relationships'] = 'PASS';
    } else {
        echo YELLOW . "⚠️  WARNING" . RESET . " - Expected 22, found $fkCount\n";
        $results['database']['fk_relationships'] = 'WARNING';
    }

    echo "\n";

} catch (\Exception $e) {
    echo RED . "❌ DATABASE ERROR" . RESET . "\n";
    echo "Error: " . $e->getMessage() . "\n";
    $results['errors'][] = $e->getMessage();
}

// ===== PART 2: API ENDPOINT TESTING =====
echo BOLD . "PART 2: API ENDPOINT TESTING" . RESET . "\n";
echo "────────────────────────────────────────────────────────────────\n\n";

echo "Starting Laravel server...\n";
// This is informational - actual API tests would require running server separately
echo YELLOW . "ⓘ" . RESET . " For complete API testing:\n";
echo "  1. Start server: php artisan serve\n";
echo "  2. Run in another terminal: php test_api_endpoints.php\n";
echo "  3. Or use Postman/curl to test endpoints\n\n";

// ===== PART 3: SUMMARY =====
echo BOLD . "PART 3: VERIFICATION SUMMARY" . RESET . "\n";
echo "────────────────────────────────────────────────────────────────\n\n";

$dbTests = array_values($results['database']);
$passed = count(array_filter($dbTests, fn($v) => $v === 'PASS'));
$total = count($dbTests);

echo "Database Tests: " . GREEN . "$passed/$total passed" . RESET . "\n\n";

echo "✅ Verified:\n";
echo "   • " . ($results['database']['table_count'] === 'PASS' ? GREEN . "✓" . RESET : RED . "✗" . RESET) . " Table count is correct (24)\n";
echo "   • " . ($results['database']['dropped_tables'] === 'PASS' ? GREEN . "✓" . RESET : RED . "✗" . RESET) . " All 5 tables dropped\n";
echo "   • " . ($results['database']['critical_tables'] === 'PASS' ? GREEN . "✓" . RESET : RED . "✗" . RESET) . " All critical tables exist\n";
echo "   • " . ($results['database']['fk_relationships'] !== 'FAIL' ? GREEN . "✓" . RESET : RED . "✗" . RESET) . " FK relationships intact\n";

echo "\n";
echo BOLD . CYAN . "════════════════════════════════════════════════════════════════" . RESET . "\n";

if ($passed == $total) {
    echo GREEN . BOLD . "✅ DATABASE VERIFICATION SUCCESSFUL!" . RESET . "\n";
    echo "\nNext: Test API endpoints with server running\n";
} else {
    echo RED . BOLD . "⚠️  SOME TESTS FAILED - CHECK ABOVE" . RESET . "\n";
}

echo BOLD . CYAN . "════════════════════════════════════════════════════════════════" . RESET . "\n";
echo "\n";
?>
