<?php
// Test the jadwal penyetoran API response

$ch = curl_init('http://localhost:8000/api/jadwal-penyetoran');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "JADWAL PENYETORAN API RESPONSE - VERIFICATION\n";
echo str_repeat('=', 80) . "\n\n";

if ($data && isset($data['data'])) {
    echo "HTTP Status: 200 OK\n";
    echo "Total schedules: " . count($data['data']) . "\n\n";
    
    if (count($data['data']) > 0) {
        echo "Sample Schedule Response:\n";
        echo str_repeat('-', 80) . "\n";
        echo json_encode($data['data'][0], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
        
        echo "Field Verification:\n";
        echo str_repeat('-', 80) . "\n";
        
        $first = $data['data'][0];
        
        // Check required fields
        $fields = ['id', 'jadwal_penyetoran_id', 'tanggal', 'waktu_mulai', 'waktu_selesai', 'lokasi'];
        $all_good = true;
        
        foreach ($fields as $field) {
            if (isset($first[$field])) {
                echo "✅ Field '$field' is present\n";
            } else {
                echo "❌ Field '$field' is MISSING\n";
                $all_good = false;
            }
        }
        
        echo "\n";
        if ($all_good) {
            echo "✅ ALL REQUIRED FIELDS PRESENT\n";
            echo "✅ Frontend can now use 'id' field for schedule selection\n";
        } else {
            echo "❌ MISSING FIELDS - Frontend will have issues\n";
        }
    }
} else {
    echo "❌ No response or error in API\n";
}

echo "\n";
echo str_repeat('=', 80) . "\n";
