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

echo "Login Response:\n";
print_r($loginData);

if (!$token) {
    echo "Failed to get token\n";
    exit(1);
}

// Now test the badges-list endpoint
echo "\n\n=== Testing /api/users/3/badges-list ===\n";
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'http://127.0.0.1:8000/api/users/3/badges-list?filter=all',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
    ],
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
echo "HTTP Code: $httpCode\n";
echo "Response:\n";
print_r(json_decode($response, true));

curl_close($curl);
