<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

// Get users and their roles
$users = User::with('role')
    ->whereIn('email', ['admin@test.com', 'superadmin@test.com', 'adib@example.com', 'test@test.com'])
    ->get();

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║            ROLE & PERMISSION VERIFICATION REPORT             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

foreach ($users as $user) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "👤 User: {$user->nama}\n";
    echo "📧 Email: {$user->email}\n";
    echo "🏷️  Role ID: {$user->role_id}\n";
    echo "🔐 Role: " . ($user->role ? $user->role->nama_role : '❌ NONE') . "\n";
    echo "⭐ Level: {$user->level}\n";
    echo "💰 Total Poin: {$user->total_poin}\n";

    if ($user->role) {
        $permissions = $user->role->getInheritedPermissions();
        echo "✅ Permissions Count: {$permissions->count()}\n";
        if ($permissions->count() > 0) {
            echo "   Sample Permissions:\n";
            foreach ($permissions->take(3) as $perm) {
                echo "   • {$perm->permission_code}: {$perm->nama_permission}\n";
            }
        }
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✨ ROLE SUMMARY:\n";
$roles = \App\Models\Role::all();
foreach ($roles as $role) {
    $count = \App\Models\User::where('role_id', $role->id)->count();
    $perms = $role->getInheritedPermissions()->count();
    echo "   • {$role->nama_role}: {$count} user(s), {$perms} permission(s)\n";
}
echo "\n";
?>
