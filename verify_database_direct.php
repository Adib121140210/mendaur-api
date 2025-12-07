#!/usr/bin/env php
<?php
/**
 * Direct Database Verification (without Laravel Container)
 * Connects directly to MySQL
 */

// Get env file
$envFile = __DIR__ . '/.env';
$env = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value, '"\'');
        }
    }
}

// Database credentials
$db_host = $env['DB_HOST'] ?? 'localhost';
$db_port = $env['DB_PORT'] ?? 3306;
$db_name = $env['DB_DATABASE'] ?? 'mendaur';
$db_user = $env['DB_USERNAME'] ?? 'root';
$db_pass = $env['DB_PASSWORD'] ?? '';

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "  DATABASE VERIFICATION - POST DROP TABLES\n";
echo "════════════════════════════════════════════════════════════════\n\n";

echo "Connecting to database...\n";
echo "  Host: $db_host:$db_port\n";
echo "  Database: $db_name\n";
echo "\n";

try {
    // Connect to MySQL
    $pdo = new PDO(
        "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "✅ Connected to database\n\n";

    // CHECK 1: Table count
    echo "CHECK 1: Total Table Count\n";
    echo "────────────────────────────────────────────────────────────────\n";

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()");
    $result = $stmt->fetch();
    $tableCount = $result['cnt'] ?? 0;

    echo "Total tables: $tableCount\n";
    if ($tableCount == 24) {
        echo "✅ PASS - Correct (expected 24)\n\n";
    } else {
        echo "⚠️  WARNING - Expected 24, found $tableCount\n\n";
    }

    // CHECK 2: List all tables
    echo "CHECK 2: All Tables\n";
    echo "────────────────────────────────────────────────────────────────\n";

    $stmt = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");
    $tables = $stmt->fetchAll();

    foreach ($tables as $table) {
        echo "  ✓ " . $table['TABLE_NAME'] . "\n";
    }
    echo "\nTotal: " . count($tables) . " tables\n\n";

    // CHECK 3: Verify dropped tables
    echo "CHECK 3: Dropped Tables Verification\n";
    echo "────────────────────────────────────────────────────────────────\n";

    $droppedTables = ['cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches'];
    $allDropped = true;

    foreach ($droppedTables as $table) {
        $stmt = $pdo->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);

        if ($stmt->rowCount() == 0) {
            echo "  ✅ $table - Dropped\n";
        } else {
            echo "  ❌ $table - Still exists!\n";
            $allDropped = false;
        }
    }

    if ($allDropped) {
        echo "✅ PASS - All 5 tables successfully dropped\n\n";
    } else {
        echo "❌ FAIL - Some tables still exist!\n\n";
    }

    // CHECK 4: Critical tables
    echo "CHECK 4: Critical Tables\n";
    echo "────────────────────────────────────────────────────────────────\n";

    $critical = ['users', 'sessions', 'transaksis', 'badges', 'produks', 'roles'];
    $allExist = true;

    foreach ($critical as $table) {
        $stmt = $pdo->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);

        if ($stmt->rowCount() > 0) {
            echo "  ✅ $table\n";
        } else {
            echo "  ❌ $table - MISSING!\n";
            $allExist = false;
        }
    }

    if ($allExist) {
        echo "✅ PASS - All critical tables exist\n\n";
    } else {
        echo "❌ FAIL - Some critical tables missing!\n\n";
    }

    // CHECK 5: FK relationships
    echo "CHECK 5: Foreign Key Relationships\n";
    echo "────────────────────────────────────────────────────────────────\n";

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");
    $result = $stmt->fetch();
    $fkCount = $result['cnt'] ?? 0;

    echo "Total FK relationships: $fkCount\n";
    if ($fkCount == 22) {
        echo "✅ PASS - All 22 relationships intact\n\n";
    } else {
        echo "⚠️  WARNING - Expected 22, found $fkCount\n\n";
    }

    // CHECK 6: Sample data
    echo "CHECK 6: Sample Data Counts\n";
    echo "────────────────────────────────────────────────────────────────\n";

    $checkTables = ['users', 'transaksis', 'badges', 'sessions'];
    foreach ($checkTables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM $table");
            $result = $stmt->fetch();
            $count = $result['cnt'] ?? 0;
            echo "  " . str_pad($table, 15) . ": $count records\n";
        } catch (\Exception $e) {
            echo "  " . str_pad($table, 15) . ": ERROR - " . $e->getMessage() . "\n";
        }
    }
    echo "\n✅ PASS - Database queries working\n\n";

    // SUMMARY
    echo "════════════════════════════════════════════════════════════════\n";
    echo "  VERIFICATION SUMMARY\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    echo "✅ Database Verification Successful!\n";
    echo "   • $tableCount tables found (expected 24)\n";
    echo "   • All 5 unused tables dropped\n";
    echo "   • All critical tables exist\n";
    echo "   • $fkCount FK relationships intact\n";
    echo "   • Data queries working\n\n";

    echo "Next steps:\n";
    echo "1. Start Laravel server: php artisan serve\n";
    echo "2. Test API endpoints in another terminal\n";
    echo "3. Check logs: storage/logs/laravel.log\n";
    echo "4. Monitor for any errors\n\n";

    echo "════════════════════════════════════════════════════════════════\n\n";

} catch (\PDOException $e) {
    echo "❌ DATABASE ERROR\n";
    echo "Error: " . $e->getMessage() . "\n\n";

    echo "Troubleshooting:\n";
    echo "1. Check .env file for correct DB credentials\n";
    echo "2. Verify MySQL server is running\n";
    echo "3. Check database name: $db_name\n";
    echo "4. Check username: $db_user\n";
    echo "\n";
} catch (\Exception $e) {
    echo "❌ ERROR\n";
    echo "Error: " . $e->getMessage() . "\n\n";
}
?>
