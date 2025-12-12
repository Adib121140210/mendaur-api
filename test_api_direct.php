<?php
// Direct test of the API endpoint with detailed output

$url = 'http://localhost:8000/api/jadwal-penyetoran';

echo "\nTesting API: $url\n";
echo str_repeat('=', 80) . "\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Cache-Control: no-cache',
    'Pragma: no-cache'
]);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "Raw Response:\n";
echo $response . "\n\n";

$data = json_decode($response, true);

if ($data) {
    echo "Parsed JSON:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

    if (isset($data['data'][0])) {
        echo "\nFirst schedule fields:\n";
        foreach (array_keys($data['data'][0]) as $key) {
            echo "  - $key\n";
        }
    }
}

echo "\n" . str_repeat('=', 80) . "\n";
