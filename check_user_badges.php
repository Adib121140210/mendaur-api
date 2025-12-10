<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== user_badges table columns ===\n";
$columns = DB::select('DESCRIBE user_badges');
foreach ($columns as $col) {
    $pk = $col->Key === 'PRI' ? ' [PRIMARY KEY]' : '';
    echo "- " . $col->Field . " (" . $col->Type . ")" . $pk . "\n";
}
