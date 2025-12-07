<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Resetting withdrawal data...\n\n";

// Delete all withdrawal records
$deletedWithdrawals = DB::table('penarikan_tunai')->delete();
echo "✅ Deleted {$deletedWithdrawals} withdrawal record(s)\n";

// Delete all notifications
$deletedNotifications = DB::table('notifikasi')->delete();
echo "✅ Deleted {$deletedNotifications} notification(s)\n";

// Reset Siti's points back to 2000
DB::table('users')->where('email', 'siti@example.com')->update([
    'total_poin' => 2000,
    'updated_at' => now()
]);

// Show updated user data
$siti = DB::table('users')->where('email', 'siti@example.com')->first();
echo "\n✨ Siti Aminah's points restored:\n";
echo "   Email: {$siti->email}\n";
echo "   Total Poin: {$siti->total_poin}\n";

echo "\n✅ Reset complete! You can test withdrawal again.\n";
