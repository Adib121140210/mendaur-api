<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== log_aktivitas table columns ===\n";
$columns = DB::select('DESCRIBE log_aktivitas');
foreach ($columns as $col) {
    echo "- " . $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== Sample log_aktivitas data ===\n";
$sample = DB::table('log_aktivitas')->first();
if ($sample) {
    echo "Sample record:\n";
    foreach ((array)$sample as $key => $value) {
        echo "  " . $key . " = " . $value . "\n";
    }
} else {
    echo "No records found\n";
}
