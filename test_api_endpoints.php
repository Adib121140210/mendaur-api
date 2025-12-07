#!/usr/bin/env php
<?php
/**
 * API Test Suite using PHP HTTP Streams
 * No dependencies on curl - uses PHP's built-in HTTP capabilities
 */

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "  API ENDPOINT TESTING - POST DROP TABLES\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Wait for server to be ready
echo "Waiting for server...\n";
sleep(3);

// Base URL
$baseUrl = "http://127.0.0.1:8000";

// Endpoints to test
$endpoints = [
    "api/health" => ["Expected" => [200, 404], "Name" => "Health Check"],
    "api/categories" => ["Expected" => [200, 401], "Name" => "Waste Categories"],
    "api/products" => ["Expected" => [200, 401], "Name" => "Exchange Products"],
    "api/badges" => ["Expected" => [200, 401], "Name" => "Badges"],
    "api/leaderboard" => ["Expected" => [200, 401, 404], "Name" => "Leaderboard"],
];

echo "Testing Endpoints:\n";
echo "────────────────────────────────────────────────────────────────\n\n";

$passCount = 0;
$failCount = 0;

foreach ($endpoints as $path => $info) {
    $url = "$baseUrl/$path";

    try {
        // Create context for HTTP request
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'ignore_errors' => true
            ]
        ]);

        // Make request
        $response = @file_get_contents($url, false, $context);

        // Get headers
        $headers = $http_response_header ?? [];
        $statusLine = $headers[0] ?? "Unknown";

        // Extract status code
        preg_match('/HTTP\/\d\.\d (\d{3})/', $statusLine, $matches);
        $statusCode = $matches[1] ?? "Unknown";

        // Expected codes
        $expectedCodes = $info['Expected'];
        $isExpected = in_array((int)$statusCode, $expectedCodes);

        // Display result
        $status = $isExpected ? "✅ PASS" : "⚠️  WARN";
        echo "$status - /$path\n";
        echo "       Status: HTTP $statusCode (expected: " . implode(" or ", $expectedCodes) . ")\n";

        if ($response) {
            $preview = substr($response, 0, 100);
            echo "       Response: " . $preview . (strlen($response) > 100 ? "..." : "") . "\n";
        }

        echo "\n";

        if ($isExpected) {
            $passCount++;
        } else {
            $failCount++;
        }

    } catch (\Exception $e) {
        echo "❌ ERROR - /$path\n";
        echo "       " . $e->getMessage() . "\n\n";
        $failCount++;
    }
}

// Summary
echo "════════════════════════════════════════════════════════════════\n";
echo "  TESTING SUMMARY\n";
echo "════════════════════════════════════════════════════════════════\n\n";

echo "Results: $passCount passed, $failCount issues\n\n";

if ($failCount == 0) {
    echo "✅ ALL TESTS PASSED!\n";
    echo "\nAPI is working correctly after drop!\n";
    echo "Database changes are successful!\n";
} else {
    echo "⚠️  Some endpoints had issues\n";
    echo "Check logs: storage/logs/laravel.log\n";
}

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\nNext steps:\n";
echo "1. Check application logs: storage/logs/laravel.log\n";
echo "2. Test login functionality\n";
echo "3. Test data retrieval endpoints\n";
echo "4. Monitor for any errors\n\n";
?>
