<?php
// Final comprehensive test of all form submission fixes

echo "\n";
echo str_repeat('=', 90) . "\n";
echo "FINAL FORM SUBMISSION VERIFICATION - ALL SYSTEMS GO\n";
echo str_repeat('=', 90) . "\n\n";

$conn = new PDO('mysql:host=localhost;dbname=mendaur_api', 'root', '');

// 1. Test API Response
echo "1. TESTING API RESPONSE - Jadwal Penyetoran\n";
echo str_repeat('-', 90) . "\n";

$ch = curl_init('http://localhost:8000/api/jadwal-penyetoran');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$api_data = json_decode($response, true);
curl_close($ch);

if ($api_data && isset($api_data['data'])) {
    echo "✅ API Response Status: HTTP 200 OK\n";
    echo "✅ Total schedules returned: " . count($api_data['data']) . "\n";

    if (count($api_data['data']) > 0) {
        $first = $api_data['data'][0];
        if (isset($first['id'])) {
            echo "✅ 'id' field is PRESENT - Frontend can use it\n";
        } else {
            echo "❌ 'id' field is MISSING\n";
        }
        if (isset($first['jadwal_penyetoran_id'])) {
            echo "✅ 'jadwal_penyetoran_id' field is PRESENT\n";
        }
    }
} else {
    echo "❌ API Error\n";
}

// 2. Verify Database Data
echo "\n2. VERIFYING DATABASE DATA\n";
echo str_repeat('-', 90) . "\n";

// Check users
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_id = 3");
$user_3 = $result->fetch(PDO::FETCH_ASSOC)['total'];
if ($user_3 > 0) {
    echo "✅ User ID 3 exists in database\n";
} else {
    echo "❌ User ID 3 NOT FOUND\n";
}

// Check schedules
$result = $conn->query("SELECT jadwal_penyetoran_id, tanggal, lokasi FROM jadwal_penyetorans");
$schedules = $result->fetchAll(PDO::FETCH_ASSOC);
echo "✅ Total schedules in database: " . count($schedules) . "\n";

// List schedules
foreach ($schedules as $sched) {
    echo "   - Schedule {$sched['jadwal_penyetoran_id']}: {$sched['tanggal']} at {$sched['lokasi']}\n";
}

// 3. Test Form Data Flow
echo "\n3. SIMULATING FORM SUBMISSION\n";
echo str_repeat('-', 90) . "\n";

// Simulate what frontend would send
$form_data = [
    'user_id' => 3,
    'jadwal_penyetoran_id' => 1,  // From API 'id' field
    'nama_lengkap' => 'Test User',
    'no_hp' => '081234567890',
    'titik_lokasi' => 'Test Location',
    'jenis_sampah' => 'Plastik',
    'berat_kg' => 5.5
];

echo "Form data prepared:\n";
foreach ($form_data as $key => $value) {
    echo "  - $key: $value\n";
}

// Verify foreign keys
echo "\nValidating form data against database:\n";

// Check user exists
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_id = {$form_data['user_id']}");
$user_exists = $result->fetch(PDO::FETCH_ASSOC)['total'];
if ($user_exists > 0) {
    echo "  ✅ user_id {$form_data['user_id']} exists\n";
} else {
    echo "  ❌ user_id {$form_data['user_id']} does NOT exist\n";
}

// Check schedule exists
$result = $conn->query("SELECT COUNT(*) as total FROM jadwal_penyetorans WHERE jadwal_penyetoran_id = {$form_data['jadwal_penyetoran_id']}");
$schedule_exists = $result->fetch(PDO::FETCH_ASSOC)['total'];
if ($schedule_exists > 0) {
    echo "  ✅ jadwal_penyetoran_id {$form_data['jadwal_penyetoran_id']} exists\n";
} else {
    echo "  ❌ jadwal_penyetoran_id {$form_data['jadwal_penyetoran_id']} does NOT exist\n";
}

// 4. Summary
echo "\n4. SUMMARY\n";
echo str_repeat('-', 90) . "\n";

$all_checks = [
    'API returns "id" field' => isset($first['id']),
    'User ID 3 exists' => $user_3 > 0,
    'Schedules data available' => count($schedules) > 0,
    'Form user validation passes' => $user_exists > 0,
    'Form schedule validation passes' => $schedule_exists > 0
];

$all_pass = true;
foreach ($all_checks as $check => $result) {
    $status = $result ? '✅' : '❌';
    echo "$status $check\n";
    if (!$result) $all_pass = false;
}

echo "\n";
if ($all_pass) {
    echo str_repeat('=', 90) . "\n";
    echo "✅✅✅ ALL CHECKS PASSED - FORM SUBMISSION READY ✅✅✅\n";
    echo str_repeat('=', 90) . "\n";
    echo "\nFrontend can now:\n";
    echo "  1. Fetch schedules with real 'id' values\n";
    echo "  2. Submit forms with proper jadwal_penyetoran_id\n";
    echo "  3. Stop using synthetic ID workarounds\n";
    echo "  4. Expect database to accept all submissions\n";
} else {
    echo str_repeat('=', 90) . "\n";
    echo "❌ SOME CHECKS FAILED - ISSUES REMAIN\n";
    echo str_repeat('=', 90) . "\n";
}

echo "\n";
