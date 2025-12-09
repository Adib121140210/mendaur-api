<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$admin = \App\Models\User::where('email', 'admin@test.com')->with('role')->first();

if ($admin) {
    echo "✓ Admin User Found\n";
    echo "  ID: " . $admin->id . "\n";
    echo "  Name: " . $admin->nama . "\n";
    echo "  Email: " . $admin->email . "\n";
    echo "  Role ID: " . $admin->role_id . "\n";
    if ($admin->role) {
        echo "  Role Name: " . $admin->role->nama_role . "\n";
        echo "  Role Level: " . $admin->role->level_akses . "\n";
    } else {
        echo "  ERROR: Role relationship not found!\n";
    }
} else {
    echo "✗ Admin user not found\n";
}
