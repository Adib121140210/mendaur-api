<?php
// Test API responses to see what fields are returned

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "API RESPONSE DIAGNOSTIC\n";
echo str_repeat('=', 80) . "\n\n";

// Test 1: Get all users
echo "1. GET /api/users (or similar)\n";
echo str_repeat('-', 80) . "\n";

$ch = curl_init('http://localhost:8000/api/users');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

if ($data) {
    if (isset($data['data'][0])) {
        echo "Sample user response:\n";
        echo json_encode($data['data'][0], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
} else {
    echo "No response or error\n";
}

// Test 2: Get jadwal penyetoran
echo "\n2. GET /api/jadwal-penyetoran\n";
echo str_repeat('-', 80) . "\n";

$ch = curl_init('http://localhost:8000/api/jadwal-penyetoran');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

if ($data) {
    if (isset($data['data'])) {
        echo "Sample schedule response:\n";
        echo json_encode($data['data'][0], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        
        // Check if 'id' field is present
        if (isset($data['data'][0]['id'])) {
            echo "\n✅ 'id' field is present\n";
        } else {
            echo "\n❌ 'id' field is MISSING - Frontend will have issues!\n";
            echo "   Available fields:\n";
            foreach (array_keys($data['data'][0]) as $key) {
                echo "   - $key\n";
            }
        }
    }
} else {
    echo "No response or error\n";
}

// Test 3: Get tabung sampah
echo "\n3. GET /api/tabung-sampah\n";
echo str_repeat('-', 80) . "\n";

$ch = curl_init('http://localhost:8000/api/tabung-sampah');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

if ($data) {
    if (isset($data['data'][0])) {
        echo "Sample deposit response:\n";
        echo json_encode($data['data'][0], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
} else {
    echo "No response or error\n";
}

echo "\n";
echo str_repeat('=', 80) . "\n";
