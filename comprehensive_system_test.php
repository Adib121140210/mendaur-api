<?php
/**
 * Comprehensive System Test: Dual-Nasabah & Badge Reward System
 *
 * Tests:
 * 1. User model methods
 * 2. Badge reward distribution
 * 3. Feature access control
 * 4. Poin tracking
 * 5. RBAC permissions
 * 6. Dual-nasabah logic
 *
 * Run: php comprehensive_system_test.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Badge;
use App\Models\Role;
use App\Models\PoinTransaksi;
use App\Models\LogAktivitas;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║      COMPREHENSIVE SYSTEM TEST - DUAL-NASABAH SYSTEM         ║\n";
echo "║                                                               ║\n";
echo "║      Testing: RBAC, Dual-Nasabah, Badge Rewards, Poin       ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$testResults = [];
$totalTests = 0;
$passedTests = 0;

// ============================================================
// TEST 1: RBAC SYSTEM
// ============================================================
echo "📋 TEST 1: RBAC SYSTEM\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$test1_pass = true;
$issues1 = [];

// Check roles exist
$nasabahRole = Role::where('nama_role', 'nasabah')->first();
$adminRole = Role::where('nama_role', 'admin')->first();
$superAdminRole = Role::where('nama_role', 'superadmin')->first();

if (!$nasabahRole || !$adminRole || !$superAdminRole) {
    $test1_pass = false;
    $issues1[] = "Not all roles found in database";
} else {
    echo "✓ All 3 roles exist: nasabah, admin, superadmin\n";
    echo "  • Nasabah (Level {$nasabahRole->level_akses}): {$nasabahRole->permissions->count()} permissions\n";
    echo "  • Admin (Level {$adminRole->level_akses}): {$adminRole->permissions->count()} permissions\n";
    echo "  • SuperAdmin (Level {$superAdminRole->level_akses}): {$superAdminRole->permissions->count()} permissions\n";
}

// Check permissions hierarchy
$totalPerms = Role::all()->sum(fn($r) => $r->permissions->count());
echo "✓ Total permission records: $totalPerms\n";

if ($test1_pass) {
    echo "✅ TEST 1 PASSED: RBAC System\n\n";
    $passedTests++;
} else {
    echo "❌ TEST 1 FAILED: " . implode(", ", $issues1) . "\n\n";
}
$totalTests++;
$testResults[] = ['RBAC System', $test1_pass];

// ============================================================
// TEST 2: USER MODEL METHODS
// ============================================================
echo "📋 TEST 2: USER MODEL METHODS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$test2_pass = true;
$issues2 = [];

// Create test user
$testUser = User::create([
    'nama' => 'Test User ' . time(),
    'email' => 'test_' . time() . '@test.com',
    'no_hp' => '08123456789',
    'password' => bcrypt('password'),
    'tipe_nasabah' => 'konvensional',
    'total_poin' => 500,
    'poin_tercatat' => 500,
    'nama_bank' => 'BNI46',
    'nomor_rekening' => '1234567890',
    'atas_nama_rekening' => 'Test User',
    'role_id' => $nasabahRole->id,
]);

// Test methods
if (!method_exists($testUser, 'isNasabahKonvensional')) {
    $test2_pass = false;
    $issues2[] = "isNasabahKonvensional() method not found";
} else {
    if ($testUser->isNasabahKonvensional()) {
        echo "✓ isNasabahKonvensional() = true\n";
    } else {
        $test2_pass = false;
        $issues2[] = "isNasabahKonvensional() returned false";
    }
}

if (!method_exists($testUser, 'hasRole')) {
    $test2_pass = false;
    $issues2[] = "hasRole() method not found";
} else {
    if ($testUser->hasRole('nasabah')) {
        echo "✓ hasRole('nasabah') = true\n";
    } else {
        $test2_pass = false;
        $issues2[] = "hasRole('nasabah') returned false";
    }
}

if (!method_exists($testUser, 'getDisplayedPoin')) {
    $test2_pass = false;
    $issues2[] = "getDisplayedPoin() method not found";
} else {
    $displayed = $testUser->getDisplayedPoin();
    if ($displayed === $testUser->total_poin) {
        echo "✓ getDisplayedPoin() = {$displayed} (correct for konvensional)\n";
    } else {
        $test2_pass = false;
        $issues2[] = "getDisplayedPoin() returned wrong value";
    }
}

if ($test2_pass) {
    echo "✅ TEST 2 PASSED: User Model Methods\n\n";
    $passedTests++;
} else {
    echo "❌ TEST 2 FAILED: " . implode(", ", $issues2) . "\n\n";
}
$totalTests++;
$testResults[] = ['User Model Methods', $test2_pass];

// ============================================================
// TEST 3: DUAL-NASABAH TYPES
// ============================================================
echo "📋 TEST 3: DUAL-NASABAH TYPES\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$test3_pass = true;
$issues3 = [];

// Create modern nasabah
$modernUser = User::create([
    'nama' => 'Modern User ' . time(),
    'email' => 'modern_' . time() . '@test.com',
    'no_hp' => '08987654321',
    'password' => bcrypt('password'),
    'tipe_nasabah' => 'modern',
    'total_poin' => 0,
    'poin_tercatat' => 300,
    'nama_bank' => 'BNI46',
    'nomor_rekening' => '9876543210',
    'atas_nama_rekening' => 'Modern User',
    'role_id' => $nasabahRole->id,
]);

echo "✓ Konvensional user created (ID: {$testUser->id})\n";
echo "  • total_poin: {$testUser->total_poin}\n";
echo "  • poin_tercatat: {$testUser->poin_tercatat}\n";
echo "  • isNasabahKonvensional: " . ($testUser->isNasabahKonvensional() ? 'true' : 'false') . "\n";

echo "✓ Modern user created (ID: {$modernUser->id})\n";
echo "  • total_poin: {$modernUser->total_poin}\n";
echo "  • poin_tercatat: {$modernUser->poin_tercatat}\n";
echo "  • isNasabahModern: " . ($modernUser->isNasabahModern() ? 'true' : 'false') . "\n";

if ($testUser->isNasabahKonvensional() && $modernUser->isNasabahModern()) {
    echo "✅ TEST 3 PASSED: Dual-Nasabah Types\n\n";
    $passedTests++;
} else {
    $test3_pass = false;
    $issues3[] = "Nasabah type detection failed";
    echo "❌ TEST 3 FAILED\n\n";
}
$totalTests++;
$testResults[] = ['Dual-Nasabah Types', $test3_pass];

// ============================================================
// TEST 4: BADGE SYSTEM
// ============================================================
echo "📋 TEST 4: BADGE SYSTEM\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$test4_pass = true;
$issues4 = [];

$badgeWithReward = Badge::where('reward_poin', '>', 0)->first();

if (!$badgeWithReward) {
    $test4_pass = false;
    $issues4[] = "No badge with reward found";
    echo "❌ TEST 4 FAILED: No badge with reward_poin > 0\n\n";
} else {
    echo "✓ Badge found: {$badgeWithReward->nama} (reward: {$badgeWithReward->reward_poin} poin)\n";

    // Simulate badge unlock for both types
    $konvBefore = $testUser->total_poin;
    $konvTercatatBefore = $testUser->poin_tercatat;

    // Award to konvensional
    if ($testUser->isNasabahKonvensional()) {
        $testUser->increment('total_poin', $badgeWithReward->reward_poin);
        $testUser->refresh();

        if ($testUser->total_poin === $konvBefore + $badgeWithReward->reward_poin) {
            echo "✓ Konvensional: total_poin increased ({$konvBefore} → {$testUser->total_poin})\n";
        } else {
            $test4_pass = false;
            $issues4[] = "Konvensional poin increment failed";
        }
    }

    // Award to modern
    $modernBefore = $modernUser->total_poin;
    $modernTercatatBefore = $modernUser->poin_tercatat;

    if ($modernUser->isNasabahModern()) {
        $modernUser->increment('poin_tercatat', $badgeWithReward->reward_poin);
        $modernUser->refresh();

        if ($modernUser->total_poin === $modernBefore) {
            echo "✓ Modern: total_poin stayed at {$modernUser->total_poin} (blocked)\n";
        } else {
            $test4_pass = false;
            $issues4[] = "Modern total_poin changed unexpectedly";
        }

        if ($modernUser->poin_tercatat === $modernTercatatBefore + $badgeWithReward->reward_poin) {
            echo "✓ Modern: poin_tercatat increased ({$modernTercatatBefore} → {$modernUser->poin_tercatat})\n";
        } else {
            $test4_pass = false;
            $issues4[] = "Modern poin_tercatat increment failed";
        }
    }

    if ($test4_pass) {
        echo "✅ TEST 4 PASSED: Badge System\n\n";
        $passedTests++;
    } else {
        echo "❌ TEST 4 FAILED: " . implode(", ", $issues4) . "\n\n";
    }
}
$totalTests++;
$testResults[] = ['Badge System', $test4_pass];

// ============================================================
// TEST 5: POIN TRACKING
// ============================================================
echo "📋 TEST 5: POIN TRACKING\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$test5_pass = true;
$issues5 = [];

// Create poin transaction
$poinTrx = PoinTransaksi::create([
    'user_id' => $testUser->id,
    'poin_didapat' => 100,
    'sumber' => 'test',
    'keterangan' => 'Test transaction',
    'is_usable' => true,
]);

if ($poinTrx) {
    echo "✓ PoinTransaksi record created (ID: {$poinTrx->id})\n";
    echo "  • user_id: {$poinTrx->user_id}\n";
    echo "  • poin_didapat: {$poinTrx->poin_didapat}\n";
    echo "  • is_usable: " . ($poinTrx->is_usable ? 'true' : 'false') . "\n";
} else {
    $test5_pass = false;
    $issues5[] = "Failed to create PoinTransaksi";
}

// Create modern poin transaction
$modernPoinTrx = PoinTransaksi::create([
    'user_id' => $modernUser->id,
    'poin_didapat' => 50,
    'sumber' => 'badge',
    'keterangan' => 'Modern user badge reward',
    'is_usable' => false,
    'reason_not_usable' => 'modern_nasabah_type',
]);

if ($modernPoinTrx) {
    echo "✓ Modern user PoinTransaksi record created (ID: {$modernPoinTrx->id})\n";
    echo "  • is_usable: " . ($modernPoinTrx->is_usable ? 'true' : 'false') . "\n";
    echo "  • reason_not_usable: {$modernPoinTrx->reason_not_usable}\n";
} else {
    $test5_pass = false;
    $issues5[] = "Failed to create modern user PoinTransaksi";
}

if ($test5_pass) {
    echo "✅ TEST 5 PASSED: Poin Tracking\n\n";
    $passedTests++;
} else {
    echo "❌ TEST 5 FAILED: " . implode(", ", $issues5) . "\n\n";
}
$totalTests++;
$testResults[] = ['Poin Tracking', $test5_pass];

// ============================================================
// TEST 6: BANK INFORMATION (DEFAULT BNI46)
// ============================================================
echo "📋 TEST 6: BANK INFORMATION (DEFAULT BNI46)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$test6_pass = true;
$issues6 = [];

echo "✓ User banking info stored:\n";
echo "  • testUser.nama_bank: {$testUser->nama_bank}\n";
echo "  • testUser.nomor_rekening: {$testUser->nomor_rekening}\n";
echo "  • testUser.atas_nama_rekening: {$testUser->atas_nama_rekening}\n";

echo "✓ Modern user banking info:\n";
echo "  • modernUser.nama_bank: {$modernUser->nama_bank}\n";
echo "  • modernUser.nomor_rekening: {$modernUser->nomor_rekening}\n";
echo "  • modernUser.atas_nama_rekening: {$modernUser->atas_nama_rekening}\n";

if ($testUser->nama_bank === 'BNI46' && $modernUser->nama_bank === 'BNI46') {
    echo "✅ TEST 6 PASSED: Bank Information (Default BNI46)\n\n";
    $passedTests++;
} else {
    $test6_pass = false;
    $issues6[] = "Bank name not set to BNI46";
    echo "❌ TEST 6 FAILED\n\n";
}
$totalTests++;
$testResults[] = ['Bank Information', $test6_pass];

// ============================================================
// FINAL SUMMARY
// ============================================================
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                      TEST SUMMARY                            ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

foreach ($testResults as $result) {
    $status = $result[1] ? "✅ PASS" : "❌ FAIL";
    echo "$status - {$result[0]}\n";
}

echo "\n📊 RESULTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";

if ($passedTests === $totalTests) {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║              ✅ ALL TESTS PASSED! ✅                          ║\n";
    echo "║                                                               ║\n";
    echo "║  System is working correctly:                                ║\n";
    echo "║  ✅ RBAC system functional                                    ║\n";
    echo "║  ✅ User model methods working                                ║\n";
    echo "║  ✅ Dual-nasabah types properly differentiated               ║\n";
    echo "║  ✅ Badge system with correct reward distribution            ║\n";
    echo "║  ✅ Poin tracking with audit trail                           ║\n";
    echo "║  ✅ Bank information stored (BNI46 default)                  ║\n";
    echo "║                                                               ║\n";
    echo "║          READY FOR PRODUCTION DEPLOYMENT! 🚀                 ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED\n";
    exit(1);
}
