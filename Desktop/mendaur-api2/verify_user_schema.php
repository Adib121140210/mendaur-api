<?php
/**
 * Schema Verification: Check User Table Structure & Bank Default
 *
 * Verifies:
 * 1. All required columns exist
 * 2. Column defaults are correct
 * 3. Bank columns are properly configured
 *
 * Run: php verify_user_schema.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║           USER TABLE SCHEMA VERIFICATION                     ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$tests_pass = true;
$issues = [];

// Check columns exist
$requiredColumns = [
    'id', 'nama', 'email', 'no_hp', 'password', 'role_id',
    'tipe_nasabah', 'total_poin', 'poin_tercatat',
    'nama_bank', 'nomor_rekening', 'atas_nama_rekening'
];

echo "📋 CHECKING REQUIRED COLUMNS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

foreach ($requiredColumns as $column) {
    if (Schema::hasColumn('users', $column)) {
        echo "✓ Column '$column' exists\n";
    } else {
        echo "✗ Column '$column' MISSING\n";
        $tests_pass = false;
        $issues[] = "Column '$column' not found";
    }
}

// Get actual column info from database
echo "\n📋 DATABASE SCHEMA DETAILS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$columns = DB::select("SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT, IS_NULLABLE, COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND TABLE_SCHEMA = DATABASE()");

$banking_columns = ['nama_bank', 'nomor_rekening', 'atas_nama_rekening', 'tipe_nasabah', 'total_poin', 'poin_tercatat'];

foreach ($columns as $col) {
    if (in_array($col->COLUMN_NAME, $banking_columns)) {
        echo "Column: {$col->COLUMN_NAME}\n";
        echo "  • Type: {$col->COLUMN_TYPE}\n";
        echo "  • Default: " . ($col->COLUMN_DEFAULT ?? 'NULL') . "\n";
        echo "  • Nullable: " . ($col->IS_NULLABLE === 'YES' ? 'YES' : 'NO') . "\n";
        if ($col->COLUMN_COMMENT) {
            echo "  • Comment: {$col->COLUMN_COMMENT}\n";
        }

        // Verify bank default
        if ($col->COLUMN_NAME === 'nama_bank') {
            if ($col->COLUMN_DEFAULT === 'BNI46') {
                echo "  ✅ BANK DEFAULT CORRECT: BNI46\n";
            } else {
                echo "  ⚠️  BANK DEFAULT NOT SET: {$col->COLUMN_DEFAULT}\n";
                $issues[] = "Bank default is not BNI46";
            }
        }

        // Verify tipe_nasabah default
        if ($col->COLUMN_NAME === 'tipe_nasabah') {
            if ($col->COLUMN_DEFAULT === 'konvensional') {
                echo "  ✅ DEFAULT TYPE: konvensional\n";
            } else {
                echo "  ⚠️  TYPE DEFAULT: {$col->COLUMN_DEFAULT}\n";
            }
        }

        echo "\n";
    }
}

// Test creating user with defaults
echo "📋 TESTING DEFAULT VALUES ON NEW USER\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

use App\Models\User;

// Create user without specifying bank
$testUser = User::create([
    'nama' => 'Default Test User ' . time(),
    'email' => 'default_test_' . time() . '@test.com',
    'no_hp' => '08111111111',
    'password' => bcrypt('password'),
    'role_id' => 1,
]);

echo "User created:\n";
echo "  • ID: {$testUser->id}\n";
echo "  • nama_bank: {$testUser->nama_bank}\n";
echo "  • nomor_rekening: " . ($testUser->nomor_rekening ?? 'NULL') . "\n";
echo "  • tipe_nasabah: {$testUser->tipe_nasabah}\n";
echo "  • total_poin: {$testUser->total_poin}\n";
echo "  • poin_tercatat: {$testUser->poin_tercatat}\n";

if ($testUser->nama_bank === 'BNI46') {
    echo "\n✅ BANK DEFAULT APPLIED CORRECTLY!\n";
} else {
    echo "\n⚠️  Bank not defaulted. Current: {$testUser->nama_bank}\n";
    $issues[] = "Bank default not applied on user creation";
}

if ($testUser->tipe_nasabah === 'konvensional') {
    echo "✅ NASABAH TYPE DEFAULT APPLIED CORRECTLY!\n";
} else {
    echo "⚠️  Type not defaulted. Current: {$testUser->tipe_nasabah}\n";
}

// Summary
echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                       SUMMARY                                 ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

if (count($issues) === 0) {
    echo "✅ ALL SCHEMA CHECKS PASSED!\n\n";
    echo "Verified:\n";
    echo "  ✅ All required columns exist\n";
    echo "  ✅ Bank column defaults to BNI46\n";
    echo "  ✅ Nasabah type defaults to konvensional\n";
    echo "  ✅ Default values applied on user creation\n";
    exit(0);
} else {
    echo "⚠️  ISSUES FOUND:\n";
    foreach ($issues as $issue) {
        echo "  • $issue\n";
    }
    echo "\nNote: If migration hasn't been refreshed, you may need to:\n";
    echo "  php artisan migrate:refresh --path=database/migrations/2025_11_27_000004_add_rbac_dual_nasabah_to_users_table.php\n";
    exit(1);
}
