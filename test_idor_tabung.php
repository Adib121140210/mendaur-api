<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Models\TabungSampah;
use Illuminate\Http\Request;
use App\Http\Controllers\TabungSampahController;
use Sanctum;

echo "=== SIMULATING API CALL ===\n\n";

// Simulate login as User 3 (Adib Surya)
$user3 = User::find(3);
echo "Logged in as: {$user3->nama} (ID: {$user3->id})\n";

// Create a fake request as if user 3 is authenticated
$request = new Request();
$request->setUserResolver(function () use ($user3) {
    return $user3;
});

echo "Authenticated User: {$request->user()->nama} (ID: {$request->user()->id})\n";

// Test 1: Call with own ID (should work)
echo "\n--- Test 1: User 3 calls GET /users/3/tabung-sampah ---\n";
$controller = new TabungSampahController();
$response = $controller->byUser($request, 3);
$data = json_decode($response->getContent(), true);
if ($data['status'] === 'success') {
    echo "✓ SUCCESS: Got " . count($data['data']) . " records\n";
    foreach ($data['data'] as $record) {
        echo "  - Record User ID: {$record['user_id']}, Status: {$record['status']}\n";
    }
} else {
    echo "✗ ERROR: " . $data['message'] . "\n";
}

// Test 2: Try to call with different ID (should be blocked)
echo "\n--- Test 2: User 3 tries GET /users/5/tabung-sampah (Budi's data) ---\n";
$response = $controller->byUser($request, 5);
$data = json_decode($response->getContent(), true);
if ($data['status'] === 'error') {
    echo "✓ BLOCKED: " . $data['message'] . " (Status: 403)\n";
} else {
    echo "✗ VULNERABILITY: Got data! Status: " . ($data['status'] ?? 'unknown') . "\n";
    if (isset($data['data'])) {
        foreach ($data['data'] as $record) {
            echo "  - Record User ID: {$record['user_id']}\n";
        }
    }
}

// Test 3: Check raw database
echo "\n--- Test 3: Raw Database Check ---\n";
echo "Tabung Sampah for User 3:\n";
$user3Data = TabungSampah::where('user_id', 3)->get();
foreach ($user3Data as $item) {
    echo "  - ID: {$item->id}, User ID: {$item->user_id}, Status: {$item->status}\n";
}

echo "\nTabung Sampah for User 5:\n";
$user5Data = TabungSampah::where('user_id', 5)->get();
echo "  Count: " . $user5Data->count() . "\n";
