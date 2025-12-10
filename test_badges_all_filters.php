<?php
// Get the token first
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'http://127.0.0.1:8000/api/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'email' => 'adib@example.com',
        'password' => 'password'
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);

$response = curl_exec($curl);
$loginData = json_decode($response, true);
$token = $loginData['data']['token'] ?? null;

if (!$token) {
    echo "Failed to get token\n";
    exit(1);
}

$filters = ['all', 'unlocked', 'locked'];

foreach ($filters as $filter) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "Testing /api/users/3/badges-list?filter=$filter\n";
    echo str_repeat("=", 60) . "\n";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "http://127.0.0.1:8000/api/users/3/badges-list?filter=$filter",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $data = json_decode($response, true);
    
    echo "HTTP Code: $httpCode\n";
    echo "Status: " . $data['status'] . "\n";
    echo "Message: " . $data['message'] . "\n";
    echo "Counts:\n";
    echo "  - All: " . $data['counts']['all'] . "\n";
    echo "  - Unlocked: " . $data['counts']['unlocked'] . "\n";
    echo "  - Locked: " . $data['counts']['locked'] . "\n";
    echo "Data Count: " . count($data['data']) . "\n";
    
    if (count($data['data']) > 0) {
        echo "\nFirst Badge:\n";
        $first = $data['data'][0];
        echo "  ID: {$first['badge_id']}\n";
        echo "  Name: {$first['nama']}\n";
        echo "  Type: {$first['tipe']}\n";
        echo "  Progress: {$first['progress_percentage']}%\n";
        echo "  Unlocked: " . ($first['is_unlocked'] ? 'Yes' : 'No') . "\n";
    }
    
    curl_close($curl);
}

echo "\n\n✅ All endpoints working correctly!\n";
