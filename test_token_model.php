<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Testing Personal Access Token Lookup ===\n\n";

// Get any existing token
$token = DB::table('personal_access_tokens')->first();

if (!$token) {
    echo "❌ No tokens found in database\n";
    exit;
}

echo "Sample token from database:\n";
echo "  - personal_access_token_id: " . $token->personal_access_token_id . "\n";
echo "  - tokenable_type: " . $token->tokenable_type . "\n";
echo "  - tokenable_id: " . $token->tokenable_id . "\n";
echo "  - token: " . substr($token->token, 0, 20) . "...\n";

// Test Model lookup
echo "\nTesting Sanctum PersonalAccessToken Model:\n";

try {
    $model = \App\Models\PersonalAccessToken::find($token->personal_access_token_id);
    
    if ($model) {
        echo "✅ Model found successfully!\n";
        echo "  - Model class: " . get_class($model) . "\n";
        echo "  - Primary key: " . $model->getKeyName() . "\n";
        echo "  - Primary key value: " . $model->getKey() . "\n";
    } else {
        echo "❌ Model returned null\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test accessing via primary key attribute
echo "\nTesting primary key access:\n";
try {
    $model = \App\Models\PersonalAccessToken::first();
    if ($model) {
        echo "✅ Personal Access Token model attributes:\n";
        echo "  - personal_access_token_id: " . $model->personal_access_token_id . "\n";
        echo "  - getKey(): " . $model->getKey() . "\n";
        echo "  - Accessing via 'id': " . ($model->id ?? "N/A - getAttribute('id')") . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
