<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Checking personal_access_tokens ===\n";
echo "Table exists: " . (Schema::hasTable('personal_access_tokens') ? "YES" : "NO") . "\n";

echo "\n=== Migrations table entries for personal_access_tokens ===\n";
$migrations = DB::table('migrations')
    ->where('migration', 'like', '%personal%')
    ->orWhere('migration', 'like', '%drop%')
    ->orWhere('migration', 'like', '%recreate%')
    ->get();

foreach ($migrations as $m) {
    echo "- " . $m->migration . " (batch: " . $m->batch . ")\n";
}

echo "\n=== Columns in personal_access_tokens ===\n";
$columns = DB::select('DESCRIBE personal_access_tokens');
foreach ($columns as $col) {
    $key = "Key";
    $isKey = isset($col->$key) && $col->$key === 'PRI' ? ' [PRIMARY KEY]' : '';
    echo "- " . $col->Field . " (" . $col->Type . ")" . $isKey . "\n";
}
