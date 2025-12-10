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

echo "Token obtained successfully\n\n";

$endpoints = [
    'GET /api/dashboard/stats/3',
    'GET /api/users/3/badges',
    'GET /api/users/3/aktivitas',
    'GET /api/users/3/badges-list?filter=all',
];

foreach ($endpoints as $endpoint) {
    list($method, $path) = explode(' ', $endpoint);
    
    echo "=== Testing: $endpoint ===\n";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "http://127.0.0.1:8000$path",
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
    
    if ($httpCode === 200) {
        echo "✅ SUCCESS\n";
        if (isset($data['status'])) echo "Status: " . $data['status'] . "\n";
        if (isset($data['message'])) echo "Message: " . $data['message'] . "\n";
        if (isset($data['data'])) {
            if (is_array($data['data']) && !empty($data['data'])) {
                echo "Data count: " . count($data['data']) . "\n";
            }
        }
    } else {
        echo "❌ FAILED\n";
        echo "Response: ";
        print_r($data);
    }
    echo "\n";
    
    curl_close($curl);
}
