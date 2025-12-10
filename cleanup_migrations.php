<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Cleaning up migrations table ===\n";

// Remove the drop and old fix migrations from the migrations table
$toDelete = [
    '2025_11_28_000005_drop_personal_access_tokens_table',
    '2025_12_10_000008_fix_personal_access_tokens_id',
];

foreach ($toDelete as $migration) {
    DB::table('migrations')->where('migration', $migration)->delete();
    echo "✓ Removed migration record: " . $migration . "\n";
}

echo "\nRemaining personal_access_tokens related entries:\n";
$remaining = DB::table('migrations')
    ->where('migration', 'like', '%personal%')
    ->orWhere('migration', 'like', '%recreate%')
    ->get();

foreach ($remaining as $m) {
    echo "- " . $m->migration . " (batch: " . $m->batch . ")\n";
}
