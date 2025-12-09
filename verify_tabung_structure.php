<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TabungSampah;
use App\Models\User;

echo "\n=== USERS TABLE ===\n";
$users = User::all();
foreach ($users as $u) {
    echo "ID: {$u->id}, Nama: {$u->nama}, Email: {$u->email}\n";
}

echo "\n=== TABUNG_SAMPAH TABLE (FULL DETAILS) ===\n";
$deposits = TabungSampah::with('user')->get();
foreach ($deposits as $d) {
    echo "Deposit ID (PK): {$d->id}\n";
    echo "  → user_id (FK): {$d->user_id}\n";
    $userName = $d->user ? $d->user->nama : 'NOT FOUND';
    echo "  → User Name: {$userName}\n";
    echo "  → Status: {$d->status}\n";
    echo "  → Created: {$d->created_at}\n\n";
}

echo "\n=== POTENTIAL CONFUSION ISSUES ===\n";
echo "When frontend calls: GET /api/users/3/tabung-sampah\n";
echo "Backend should return deposits WHERE user_id = 3\n\n";

$user3Deposits = TabungSampah::where('user_id', 3)->get();
echo "Deposits for user_id=3:\n";
foreach ($user3Deposits as $d) {
    echo "  Deposit ID: {$d->id}, User ID: {$d->user_id}, User: {$d->user->nama}\n";
}

echo "\n✅ Verification: User 3 data belongs to: " . ($user3Deposits->first()?->user->nama ?? 'NONE') . "\n";
