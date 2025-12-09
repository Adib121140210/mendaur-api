<?php
require 'vendor/autoload.php';

$app = require_once('bootstrap/app.php');
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Columns in log_aktivitas table:\n";
if (Schema::hasTable('log_aktivitas')) {
    $columns = Schema::getColumns('log_aktivitas');
    foreach ($columns as $column) {
        echo "  - " . $column['name'] . " (" . $column['type'] . ")\n";
    }
} else {
    echo "  Table does not exist\n";
}

echo "\nColumns in poin_transaksis table:\n";
if (Schema::hasTable('poin_transaksis')) {
    $columns = Schema::getColumns('poin_transaksis');
    foreach ($columns as $column) {
        echo "  - " . $column['name'] . " (" . $column['type'] . ")\n";
    }
} else {
    echo "  Table does not exist\n";
}
