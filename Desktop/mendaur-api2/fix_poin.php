<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\TabungSampah;
use App\Models\LogAktivitas;
use App\Services\BadgeService;

// Get the approved tabung_sampah record
$tabungSampah = TabungSampah::find(1);

if (!$tabungSampah) {
    echo "❌ TabungSampah record not found" . PHP_EOL;
    exit;
}

echo "🔧 Fixing Points for User..." . PHP_EOL;
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;

// Get the user
$user = User::find($tabungSampah->user_id);
echo "👤 User: " . $user->nama . PHP_EOL;
echo "📊 Before: {$user->total_poin} poin" . PHP_EOL;

// Increment the points
$pointsToAdd = $tabungSampah->poin_didapat;
$user->increment('total_poin', $pointsToAdd);

// Refresh to see the new value
$user->refresh();
echo "✅ After: {$user->total_poin} poin (added {$pointsToAdd})" . PHP_EOL;
echo PHP_EOL;

// Increment total deposits
$user->increment('total_setor_sampah');
$user->refresh();
echo "📈 Total Setor Sampah: {$user->total_setor_sampah}" . PHP_EOL;

// Log the activity
LogAktivitas::log(
    $user->id,
    LogAktivitas::TYPE_SETOR_SAMPAH,
    "Menyetor {$tabungSampah->berat_kg}kg sampah {$tabungSampah->jenis_sampah}",
    $pointsToAdd
);
echo "📝 Activity logged" . PHP_EOL;

// Check for badges
$badgeService = new BadgeService();
$newBadges = $badgeService->checkAndAwardBadges($user->id);

if ($newBadges && count($newBadges) > 0) {
    echo "🏆 New Badges Awarded:" . PHP_EOL;
    foreach ($newBadges as $badge) {
        echo "  - {$badge['nama']}" . PHP_EOL;
    }
} else {
    echo "🏆 No new badges unlocked" . PHP_EOL;
}

echo PHP_EOL;
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;
echo "✅ Poin Fix Complete!" . PHP_EOL;
