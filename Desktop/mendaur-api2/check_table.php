<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Checking database tables...\n\n";

// Check if penarikan_tunai exists
if (Schema::hasTable('penarikan_tunai')) {
    echo "✅ Table 'penarikan_tunai' EXISTS\n";

    // Get column info
    $columns = DB::select("DESCRIBE penarikan_tunai");
    echo "\nTable Structure:\n";
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
} else {
    echo "❌ Table 'penarikan_tunai' DOES NOT EXIST\n";
    echo "\nYou need to run: php artisan migrate\n";
}

echo "\n\nAll tables in database:\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    echo "  - $tableName\n";
}
