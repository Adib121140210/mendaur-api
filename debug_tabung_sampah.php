<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use App\Models\TabungSampah;
use App\Models\User;

// Check all tabung_sampah data
echo "=== ALL TABUNG_SAMPAH DATA ===\n";
$all = TabungSampah::with('user')->get();
foreach ($all as $item) {
    echo "ID: {$item->id}, User ID: {$item->user_id}, User: {$item->user->nama}, Status: {$item->status}\n";
}

echo "\n=== DATA BY USER ===\n";
// Check User 1 (Adib) data
echo "User 1 (Adib) tabung_sampah:\n";
$user1 = TabungSampah::where('user_id', 1)->get();
echo "Count: " . $user1->count() . "\n";
foreach ($user1 as $item) {
    echo "  - ID: {$item->id}, Status: {$item->status}\n";
}

// Check User 3 (Budi) data
echo "\nUser 3 (Budi) tabung_sampah:\n";
$user3 = TabungSampah::where('user_id', 3)->get();
echo "Count: " . $user3->count() . "\n";
foreach ($user3 as $item) {
    echo "  - ID: {$item->id}, Status: {$item->status}\n";
}

echo "\n=== TABLE STRUCTURE ===\n";
$columns = DB::select("DESCRIBE tabung_sampah");
foreach ($columns as $col) {
    echo "{$col->Field}: {$col->Type} (Null: {$col->Null}, Key: {$col->Key})\n";
}

echo "\n=== CHECK ROUTE ===\n";
// Show controller code
echo "Checking TabungSampahController::byUser...\n";
$file = file_get_contents('app/Http/Controllers/TabungSampahController.php');
if (strpos($file, "where('user_id', \$id)") !== false) {
    echo "✓ Controller DOES filter by user_id\n";
} else {
    echo "✗ Controller does NOT filter by user_id - VULNERABILITY!\n";
}

// Check if middleware is set
$routeFile = file_get_contents('routes/api.php');
if (preg_match("/middleware\('auth:sanctum'\).*?users\/\{id\}\/tabung-sampah/s", $routeFile)) {
    echo "✓ Route has auth:sanctum middleware\n";
} else {
    echo "✗ Route does NOT have auth:sanctum middleware\n";
}

echo "\nDone.\n";
