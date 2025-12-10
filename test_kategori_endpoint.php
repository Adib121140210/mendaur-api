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

echo "=== Testing /api/kategori-sampah ===\n";
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'http://127.0.0.1:8000/api/kategori-sampah',
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
echo "Status: " . $data['success'] . "\n";
echo "Message: " . $data['message'] . "\n\n";

if (!empty($data['data'])) {
    echo "First kategori: \n";
    print_r($data['data'][0]);
} else {
    echo "No data returned\n";
}

curl_close($curl);
