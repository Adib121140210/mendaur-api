<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\TabungSampah;

// Check user 'adib123' (Adib Surya)
$user = User::where('email', 'adib@example.com')->first();

if (!$user) {
    echo "❌ User not found with email 'adib@example.com'" . PHP_EOL;
    echo "Checking all users:" . PHP_EOL;
    $allUsers = User::all();
    foreach ($allUsers as $u) {
        echo "  - {$u->nama} ({$u->email}) - Poin: {$u->total_poin}" . PHP_EOL;
    }
    exit;
}

echo "👤 User Found: " . $user->nama . PHP_EOL;
echo "📧 Email: " . $user->email . PHP_EOL;
echo "💰 Current Total Poin: " . $user->total_poin . PHP_EOL;
echo PHP_EOL;

// Check tabung_sampah records for this user
echo "🗂️ TabungSampah Records:" . PHP_EOL;
$tabungRecords = TabungSampah::where('user_id', $user->id)->get();

if ($tabungRecords->isEmpty()) {
    echo "❌ No tabung_sampah records found for this user" . PHP_EOL;
} else {
    echo "✅ Found " . $tabungRecords->count() . " records:" . PHP_EOL;
    foreach ($tabungRecords as $record) {
        echo "  ID: {$record->id}" . PHP_EOL;
        echo "    Status: {$record->status}" . PHP_EOL;
        echo "    Berat (kg): {$record->berat_kg}" . PHP_EOL;
        echo "    Poin Didapat: {$record->poin_didapat}" . PHP_EOL;
        echo "    Created: {$record->created_at}" . PHP_EOL;
        echo PHP_EOL;
    }
}

// Calculate total poin that should have been awarded
$approvedRecords = TabungSampah::where('user_id', $user->id)
    ->where('status', 'approved')
    ->get();

$totalPointsFromApproved = $approvedRecords->sum('poin_didapat');

echo "📊 Summary:" . PHP_EOL;
echo "  Total Approved Tabung Sampah: " . $approvedRecords->count() . PHP_EOL;
echo "  Total Poin from Approved: " . $totalPointsFromApproved . PHP_EOL;
echo "  User's Actual Poin: " . $user->total_poin . PHP_EOL;
echo "  Expected Poin: " . (150 + $totalPointsFromApproved) . " (initial 150 + approved)" . PHP_EOL;
echo PHP_EOL;

if ($user->total_poin != (150 + $totalPointsFromApproved)) {
    echo "⚠️ MISMATCH DETECTED!" . PHP_EOL;
    echo "The user's poin doesn't match the sum of approved tabung_sampah" . PHP_EOL;
} else {
    echo "✅ Poin matches correctly!" . PHP_EOL;
}
