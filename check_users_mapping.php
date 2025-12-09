<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;

echo "=== ALL USERS ===\n";
$users = User::all();
foreach ($users as $user) {
    echo "ID: {$user->id}, Name: {$user->nama}, Email: {$user->email}\n";
}

echo "\n=== TABUNG_SAMPAH RECORDS ===\n";
$deposits = \App\Models\TabungSampah::with('user')->get();
foreach ($deposits as $deposit) {
    echo "Deposit ID: {$deposit->id}, User ID: {$deposit->user_id}, User Name: {$deposit->user->nama}\n";
}
