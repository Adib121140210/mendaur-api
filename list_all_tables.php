<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== ALL TABLES IN DATABASE ===\n\n";

$tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?", [env('DB_DATABASE')]);

foreach ($tables as $table) {
    $tableName = $table->TABLE_NAME;
    
    // Get columns
    $columns = DB::select("SELECT COLUMN_NAME, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$tableName, env('DB_DATABASE')]);
    
    echo "📊 TABLE: {$tableName}\n";
    foreach ($columns as $col) {
        if ($col->COLUMN_KEY === 'PRI') {
            echo "   🔑 PRIMARY KEY: {$col->COLUMN_NAME}\n";
        }
    }
    echo "\n";
}
