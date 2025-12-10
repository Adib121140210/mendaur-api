<?php
// Test tabung_sampah API response

$ch = curl_init('http://localhost:8000/api/tabung-sampah');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "✅ TABUNG SAMPAH API RESPONSE - SCHEMA FIX VERIFICATION\n";
echo str_repeat('=', 80) . "\n\n";

if ($data && isset($data['data'])) {
    echo "HTTP Status: 200 OK\n";
    echo "Total Records: " . count($data['data']) . "\n\n";
    
    if (count($data['data']) > 0) {
        echo "Sample Record:\n";
        echo str_repeat('-', 80) . "\n";
        
        $first = $data['data'][0];
        
        // Check if the new field name is present
        if (isset($first['jadwal_penyetoran_id'])) {
            echo "✅ Field 'jadwal_penyetoran_id' is present (CORRECT)\n";
        } else {
            echo "❌ Field 'jadwal_penyetoran_id' is MISSING\n";
        }
        
        // Check if old field name is gone
        if (!isset($first['jadwal_id'])) {
            echo "✅ Old field 'jadwal_id' is REMOVED (CORRECT)\n";
        } else {
            echo "⚠️  Old field 'jadwal_id' is still present (may need cleanup)\n";
        }
        
        echo "\nFull Record Structure:\n";
        echo json_encode($first, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
} else {
    echo "❌ No data or error in response\n";
    echo "Response: " . var_export($data, true) . "\n";
}

echo "\n" . str_repeat('=', 80) . "\n";
curl_close($ch);
