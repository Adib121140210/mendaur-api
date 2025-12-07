<?php
require 'vendor/autoload.php';

$app = require_once('bootstrap/app.php');
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Role;
use App\Models\RolePermission;

echo "===== ROLES CREATED =====\n";
$roles = Role::all();
foreach ($roles as $role) {
    echo "  - {$role->nama_role} (Level {$role->level_akses})\n";
}

echo "\n===== PERMISSIONS BY ROLE =====\n";
foreach ($roles as $role) {
    $perms = $role->permissions()->pluck('permission_code')->toArray();
    echo "\n{$role->nama_role} ({$role->level_akses}):\n";
    echo "  Total permissions: " . count($perms) . "\n";
    echo "  Permissions: \n";
    foreach (array_chunk($perms, 5) as $chunk) {
        echo "    - " . implode(", ", $chunk) . "\n";
    }
}

echo "\n===== SUMMARY =====\n";
echo "Total Roles: " . Role::count() . "\n";
echo "Total Permission Records: " . RolePermission::count() . "\n";
