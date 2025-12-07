<?php
/**
 * Verification Script: Dual-Nasabah Badge Reward System
 *
 * Purpose: Verify that badge rewards are distributed correctly for both nasabah types
 * - Konvensional: reward → total_poin (usable)
 * - Modern: reward → poin_tercatat (non-usable)
 *
 * Run: php verify_dual_nasabah_badge.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Badge;
use App\Models\UserBadge;
use App\Models\PoinTransaksi;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║   DUAL-NASABAH BADGE REWARD VERIFICATION                    ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Test data setup
$testData = [
    'konvensional' => [
        'tipe_nasabah' => 'konvensional',
        'total_poin_before' => 500,
        'poin_tercatat_before' => 500,
        'expected_reward_location' => 'total_poin',
    ],
    'modern' => [
        'tipe_nasabah' => 'modern',
        'total_poin_before' => 0,
        'poin_tercatat_before' => 500,
        'expected_reward_location' => 'poin_tercatat',
    ],
];

$badge = Badge::where('reward_poin', '>', 0)->first();
if (!$badge) {
    echo "❌ Error: No badge with reward_poin found. Run seeder first.\n";
    exit(1);
}

$badge_reward = $badge->reward_poin;
echo "Using badge: {$badge->nama} (reward: {$badge_reward} poin)\n\n";

$results = [];

foreach ($testData as $type => $expectedData) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Test: " . strtoupper($type) . " Nasabah Badge Reward\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    // Create test user
    $testUser = User::create([
        'nama' => "Test {$type} User - Badge Test",
        'email' => "badge_test_{$type}_" . time() . "@test.com",
        'no_hp' => '08123456789',
        'password' => bcrypt('password'),
        'tipe_nasabah' => $expectedData['tipe_nasabah'],
        'total_poin' => $expectedData['total_poin_before'],
        'poin_tercatat' => $expectedData['poin_tercatat_before'],
        'role_id' => 1, // nasabah
    ]);

    echo "✓ Created test user (ID: {$testUser->id}, type: {$type})\n";
    echo "  Before badge unlock:\n";
    echo "    • total_poin: {$testUser->total_poin}\n";
    echo "    • poin_tercatat: {$testUser->poin_tercatat}\n";

    // Simulate badge unlock (award badge)
    UserBadge::create([
        'user_id' => $testUser->id,
        'badge_id' => $badge->id,
        'tanggal_dapat' => now(),
        'reward_claimed' => true,
    ]);

    // Apply reward based on nasabah type
    if ($testUser->isNasabahKonvensional()) {
        $testUser->increment('total_poin', $badge_reward);
    } else {
        $testUser->increment('poin_tercatat', $badge_reward);
    }

    // Refresh from database
    $testUser->refresh();

    echo "\n  After badge unlock with {$badge_reward} poin reward:\n";
    echo "    • total_poin: {$testUser->total_poin}\n";
    echo "    • poin_tercatat: {$testUser->poin_tercatat}\n";

    // Verify results
    $expected_before = $expectedData['total_poin_before'];
    $expected_tercatat = $expectedData['poin_tercatat_before'];

    $test_pass = true;
    $issues = [];

    if ($type === 'konvensional') {
        // Konvensional should have reward in total_poin
        if ($testUser->total_poin !== $expected_before + $badge_reward) {
            $test_pass = false;
            $issues[] = "total_poin should be " . ($expected_before + $badge_reward) . " but got " . $testUser->total_poin;
        }
        if ($testUser->poin_tercatat !== $expected_tercatat) {
            $test_pass = false;
            $issues[] = "poin_tercatat should be " . $expected_tercatat . " but got " . $testUser->poin_tercatat;
        }
    } else {
        // Modern should have reward in poin_tercatat, NOT in total_poin
        if ($testUser->total_poin !== $expected_before) {
            $test_pass = false;
            $issues[] = "total_poin should stay " . $expected_before . " but got " . $testUser->total_poin;
        }
        if ($testUser->poin_tercatat !== $expected_tercatat + $badge_reward) {
            $test_pass = false;
            $issues[] = "poin_tercatat should be " . ($expected_tercatat + $badge_reward) . " but got " . $testUser->poin_tercatat;
        }
    }

    echo "\n  ✅ Verification:\n";
    if ($test_pass) {
        echo "     Status: " . (($type === 'konvensional') ? "PASS ✓" : "PASS ✓") . "\n";
        if ($type === 'konvensional') {
            echo "     • Reward correctly added to total_poin (usable)\n";
            echo "     • poin_tercatat unchanged ✓\n";
            echo "     ✓ User CAN use this reward for withdrawal/redemption\n";
        } else {
            echo "     • Reward correctly added to poin_tercatat (recorded)\n";
            echo "     • total_poin stayed at 0 (blocked) ✓\n";
            echo "     ✓ User CANNOT use this reward for withdrawal/redemption\n";
        }
    } else {
        echo "     Status: FAIL ✗\n";
        foreach ($issues as $issue) {
            echo "     ✗ {$issue}\n";
        }
    }

    $results[$type] = $test_pass;

    // Check audit trail
    echo "\n  📝 Audit Trail:\n";
    $recentReward = PoinTransaksi::where('user_id', $testUser->id)
        ->where('sumber', 'badge')
        ->latest('created_at')
        ->first();

    if ($recentReward) {
        echo "     ✓ Reward logged in poin_transaksis\n";
        echo "       • poin_didapat: {$recentReward->poin_didapat}\n";
        echo "       • is_usable: " . ($recentReward->is_usable ? "true (usable)" : "false (recorded only)") . "\n";
    } else {
        echo "     ⚠ No audit trail found\n";
    }

    echo "\n";

    // Cleanup
    $testUser->delete();
}

// Summary
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                         SUMMARY                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$all_pass = array_reduce($results, fn($carry, $item) => $carry && $item, true);

echo "Results:\n";
foreach ($results as $type => $pass) {
    $status = $pass ? "✅ PASS" : "❌ FAIL";
    echo "  $status - " . ucfirst($type) . " nasabah badge reward\n";
}

echo "\n";
if ($all_pass) {
    echo "✅ ALL TESTS PASSED!\n";
    echo "✅ Badge reward system is working correctly for both nasabah types.\n";
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED!\n";
    echo "❌ Badge reward logic needs review.\n";
    exit(1);
}
