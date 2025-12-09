<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test UserResource
$user = \App\Models\User::first();
if ($user) {
    $resource = new \App\Http\Resources\UserResource($user);
    echo "✅ UserResource created successfully\n";
    echo "Fields returned:\n";
    $arr = $resource->toArray(new \Illuminate\Http\Request());
    echo json_encode($arr, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No users found in database\n";
}
?>
