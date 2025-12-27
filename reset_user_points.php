<?php
/**
 * Reset dan atur poin user dengan nilai beragam untuk leaderboard
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== RESET DAN ATUR POIN USER ===\n\n";

// Data poin beragam untuk setiap user (user_id => [actual_poin, display_poin, poin_tercatat])
$userPoints = [
    // Admin/Superadmin (poin tinggi untuk testing)
    1 => ['actual' => 5000, 'display' => 4500, 'tercatat' => 5000],   // Admin
    2 => ['actual' => 10000, 'display' => 9500, 'tercatat' => 10000], // Superadmin

    // Regular users dengan poin beragam
    3 => ['actual' => 15000, 'display' => 14200, 'tercatat' => 15000],  // Top 1
    4 => ['actual' => 12500, 'display' => 11800, 'tercatat' => 12500],  // Top 2
    5 => ['actual' => 9800, 'display' => 9200, 'tercatat' => 9800],     // Top 3
    6 => ['actual' => 8500, 'display' => 8000, 'tercatat' => 8500],     // Top 4
    7 => ['actual' => 7200, 'display' => 6800, 'tercatat' => 7200],     // Top 5
    8 => ['actual' => 6000, 'display' => 5500, 'tercatat' => 6000],     // Top 6
    9 => ['actual' => 4800, 'display' => 4300, 'tercatat' => 4800],     // Top 7
    10 => ['actual' => 3500, 'display' => 3100, 'tercatat' => 3500],    // Top 8
    11 => ['actual' => 2800, 'display' => 2400, 'tercatat' => 2800],    // Top 9
    12 => ['actual' => 2000, 'display' => 1700, 'tercatat' => 2000],    // Top 10
    13 => ['actual' => 1500, 'display' => 1200, 'tercatat' => 1500],
    14 => ['actual' => 1200, 'display' => 900, 'tercatat' => 1200],
    15 => ['actual' => 800, 'display' => 600, 'tercatat' => 800],
    16 => ['actual' => 500, 'display' => 350, 'tercatat' => 500],
    17 => ['actual' => 300, 'display' => 200, 'tercatat' => 300],
    18 => ['actual' => 150, 'display' => 100, 'tercatat' => 150],
    19 => ['actual' => 50, 'display' => 30, 'tercatat' => 50],
    20 => ['actual' => 0, 'display' => 0, 'tercatat' => 0],  // New user with no points
];

// Level berdasarkan actual_poin
function determineLevel($actualPoin) {
    if ($actualPoin >= 10000) return 'Gold';
    if ($actualPoin >= 5000) return 'Silver';
    return 'Bronze';
}

echo "Sebelum Reset:\n";
echo str_repeat("-", 90) . "\n";
printf("%-5s | %-25s | %-10s | %-10s | %-10s | %-10s\n", "ID", "Nama", "Level", "Actual", "Display", "Tercatat");
echo str_repeat("-", 90) . "\n";

$users = User::orderBy('user_id')->get();
foreach ($users as $user) {
    printf("%-5s | %-25s | %-10s | %-10s | %-10s | %-10s\n",
        $user->user_id,
        substr($user->nama, 0, 25),
        $user->level,
        $user->actual_poin,
        $user->display_poin,
        $user->poin_tercatat
    );
}

echo "\n\nMelakukan reset poin...\n\n";

DB::beginTransaction();
try {
    foreach ($users as $user) {
        $points = $userPoints[$user->user_id] ?? ['actual' => rand(100, 1000), 'display' => rand(50, 800), 'tercatat' => rand(100, 1000)];

        // Jangan ubah level admin/superadmin
        $newLevel = in_array(strtolower($user->level), ['admin', 'superadmin'])
            ? $user->level
            : determineLevel($points['actual']);

        $user->update([
            'actual_poin' => $points['actual'],
            'display_poin' => $points['display'],
            'poin_tercatat' => $points['tercatat'],
            'level' => $newLevel,
        ]);

        echo "✓ Updated: {$user->nama} -> actual:{$points['actual']}, display:{$points['display']}, tercatat:{$points['tercatat']}, level:{$newLevel}\n";
    }

    DB::commit();
    echo "\n✅ Semua poin berhasil di-reset!\n";
} catch (Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n\nSetelah Reset:\n";
echo str_repeat("-", 90) . "\n";
printf("%-5s | %-25s | %-10s | %-10s | %-10s | %-10s\n", "ID", "Nama", "Level", "Actual", "Display", "Tercatat");
echo str_repeat("-", 90) . "\n";

$users = User::orderBy('actual_poin', 'desc')->get();
foreach ($users as $user) {
    printf("%-5s | %-25s | %-10s | %-10s | %-10s | %-10s\n",
        $user->user_id,
        substr($user->nama, 0, 25),
        $user->level,
        $user->actual_poin,
        $user->display_poin,
        $user->poin_tercatat
    );
}

echo "\n=== LEADERBOARD PREVIEW (Top 10 by actual_poin) ===\n";
echo str_repeat("-", 70) . "\n";
printf("%-5s | %-25s | %-10s | %-10s\n", "Rank", "Nama", "Level", "Poin");
echo str_repeat("-", 70) . "\n";

$rank = 1;
$leaderboard = User::whereNotIn('level', ['Admin', 'Superadmin', 'admin', 'superadmin'])
    ->orderBy('actual_poin', 'desc')
    ->take(10)
    ->get();

foreach ($leaderboard as $user) {
    printf("%-5s | %-25s | %-10s | %-10s\n",
        "#$rank",
        substr($user->nama, 0, 25),
        $user->level,
        number_format($user->actual_poin)
    );
    $rank++;
}

echo "\n✅ Done! Leaderboard sekarang menampilkan user dengan poin beragam.\n";
