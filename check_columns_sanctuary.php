<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$columns = \Illuminate\Support\Facades\DB::select('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "personal_access_tokens" AND TABLE_SCHEMA = "mendaur" ORDER BY ORDINAL_POSITION');

echo "=== Columns in personal_access_tokens table ===\n";
if (empty($columns)) {
    echo "No columns found! Table might not exist.\n";
} else {
    foreach ($columns as $col) {
        echo "  - " . $col->COLUMN_NAME . "\n";
    }
}

// Check primary key
$pk = \Illuminate\Support\Facades\DB::select('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = "personal_access_tokens" AND CONSTRAINT_NAME = "PRIMARY" AND TABLE_SCHEMA = "mendaur"');
if (empty($pk)) {
    echo "\nNo primary key found!\n";
} else {
    echo "\nPrimary Key Column: " . $pk[0]->COLUMN_NAME . "\n";
}
