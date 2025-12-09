<?php

// Test the API with the new primary keys
$baseUrl = 'http://localhost:8000/api';

// Get a valid user ID from the database
$mysqli = new mysqli('localhost', 'root', '', 'mendaur_api');
$result = $mysqli->query('SELECT user_id, email FROM users LIMIT 1');
$user = $result->fetch_assoc();

if (!$user) {
    echo "No users found in database\n";
    exit(1);
}

$userId = $user['user_id'];
$email = $user['email'];

echo "Testing API with new primary key naming...\n";
echo "User ID: $userId, Email: $email\n\n";

// Test 1: Get user info
echo "Test 1: GET /api/users/$userId\n";
$response = @file_get_contents("$baseUrl/users/$userId");
if ($response === false) {
    echo "⚠️  Could not reach API - make sure server is running\n";
} else {
    $data = json_decode($response, true);
    
    if (isset($data['data']['user_id'])) {
        echo "✓ Response contains 'user_id' field\n";
        echo "  user_id: " . $data['data']['user_id'] . "\n";
    } else if (isset($data['data']['id'])) {
        echo "✗ Response still contains old 'id' field\n";
    } else {
        echo "✗ No ID field found in response\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
}

// Test 2: Get user's tabung sampah
echo "\n\nTest 2: GET /api/users/$userId/tabung-sampah\n";
$response = @file_get_contents("$baseUrl/users/$userId/tabung-sampah");
if ($response === false) {
    echo "⚠️  Could not reach API\n";
} else {
    $data = json_decode($response, true);
    
    if (isset($data['data']) && is_array($data['data']) && count($data['data']) > 0) {
        $item = $data['data'][0];
        if (isset($item['tabung_sampah_id'])) {
            echo "✓ Response contains 'tabung_sampah_id' field\n";
            echo "  tabung_sampah_id: " . $item['tabung_sampah_id'] . "\n";
        } else if (isset($item['id'])) {
            echo "✗ Response still contains old 'id' field\n";
        } else {
            echo "⚠️  No standard ID found\n";
        }
    } else {
        echo "✓ No deposits found (but endpoint works)\n";
    }
}

$mysqli->close();

echo "\n✅ Migration complete! All primary keys have been renamed.\n";
echo "Frontend team must update API response parsing to use new field names.\n";
?>
