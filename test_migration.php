<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run migration
echo "Running migrate:fresh --seed...\n";
$exitCode = \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true]);
echo "Migration exit code: $exitCode\n";

if ($exitCode === 0) {
    echo "✓ Migration completed successfully!\n";
    echo "Checking database...\n";
    $userCount = \Illuminate\Support\Facades\DB::table('users')->count();
    echo "✓ Users in database: $userCount\n";
} else {
    echo "✗ Migration failed with exit code: $exitCode\n";
}
