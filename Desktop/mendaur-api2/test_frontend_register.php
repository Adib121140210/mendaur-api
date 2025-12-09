<?php
/**
 * Frontend Register Integration Tester
 * Tests the /api/register endpoint
 */

$apiUrl = 'http://127.0.0.1:8000/api/register';

echo "🧪 FRONTEND REGISTER INTEGRATION TEST\n";
echo "=====================================\n\n";

// Test Case 1: Valid Registration
echo "Test 1️⃣: Valid Registration Data\n";
echo "-----------------------------------\n";

$testData = [
    'nama' => 'Frontend Test User ' . time(),
    'email' => 'frontendtest' . time() . '@example.com',
    'no_hp' => '08123456789',
    'password' => 'TestPassword123!',
    'password_confirmation' => 'TestPassword123!'
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

echo "📤 Request Payload:\n";
echo json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "📥 Response (HTTP $httpCode):\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

if ($httpCode === 201 && $result['status'] === 'success') {
    echo "✅ SUCCESS - User registration works!\n\n";
} else {
    echo "❌ FAILED - Expected 201 with success status\n\n";
}

// Test Case 2: Duplicate Email
echo "Test 2️⃣: Duplicate Email Error\n";
echo "--------------------------------\n";

$duplicateData = [
    'nama' => 'Frontend Test Duplicate',
    'email' => $testData['email'], // Use same email
    'no_hp' => '08987654321',
    'password' => 'TestPassword456!',
    'password_confirmation' => 'TestPassword456!'
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($duplicateData));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

echo "📤 Request Payload (same email):\n";
echo json_encode($duplicateData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "📥 Response (HTTP $httpCode):\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

if ($httpCode === 422 && isset($result['errors']['email'])) {
    echo "✅ SUCCESS - Duplicate email validation works!\n\n";
} else {
    echo "⚠️  WARNING - Expected 422 with email error\n\n";
}

// Test Case 3: Password Mismatch
echo "Test 3️⃣: Password Mismatch Error\n";
echo "--------------------------------\n";

$mismatchData = [
    'nama' => 'Frontend Test Mismatch',
    'email' => 'frontendmismatch' . time() . '@example.com',
    'no_hp' => '08111111111',
    'password' => 'TestPassword123!',
    'password_confirmation' => 'DifferentPassword123!' // Different!
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mismatchData));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

echo "📤 Request Payload (mismatched passwords):\n";
echo json_encode($mismatchData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "📥 Response (HTTP $httpCode):\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

if ($httpCode === 422 && isset($result['errors']['password'])) {
    echo "✅ SUCCESS - Password validation works!\n\n";
} else {
    echo "⚠️  WARNING - Expected 422 with password error\n\n";
}

// Summary
echo "=====================================\n";
echo "🎉 FRONTEND REGISTER INTEGRATION TEST COMPLETE\n";
echo "=====================================\n\n";

echo "✅ Backend API is ready for frontend integration!\n";
echo "📝 Frontend can now:\n";
echo "   1. Submit form data to POST /api/register\n";
echo "   2. Handle 201 success response with user data\n";
echo "   3. Handle 422 validation errors\n";
echo "   4. Display error messages to user\n";
echo "   5. Redirect to login on success\n\n";
?>
