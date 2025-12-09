#!/usr/bin/env php
<?php
/**
 * Test IDOR Security Fixes
 * Verifies that 13 fixed endpoints return 401/403 without proper auth
 */

echo "\n════════════════════════════════════════════════════════════════\n";
echo "  IDOR SECURITY FIX VERIFICATION\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Test endpoints that should be protected
$endpoints = [
    // UserController (6 endpoints)
    ['GET', '/api/user/profile', 'UserController - Get Profile'],
    ['GET', '/api/user/badge-history', 'UserController - Badge History'],
    ['PUT', '/api/user/update-profile', 'UserController - Update Profile'],
    ['GET', '/api/user/transaksi', 'UserController - Transaksi History'],
    ['GET', '/api/user/poin', 'UserController - Point Balance'],
    ['GET', '/api/user/badges', 'UserController - User Badges'],
    
    // TabungSampahController (1 endpoint)
    ['GET', '/api/tabung-sampah/my-bins', 'TabungSampahController - My Bins'],
    
    // PointController (5 endpoints)
    ['GET', '/api/points/balance', 'PointController - Balance'],
    ['GET', '/api/points/history', 'PointController - History'],
    ['POST', '/api/points/transfer', 'PointController - Transfer'],
    ['GET', '/api/points/leaderboard', 'PointController - Leaderboard'],
    ['GET', '/api/points/usage', 'PointController - Usage'],
    
    // DashboardController (1 endpoint)
    ['GET', '/api/dashboard/overview', 'DashboardController - Overview'],
];

echo "Testing " . count($endpoints) . " IDOR-fixed endpoints...\n\n";

$passed = 0;
$failed = 0;

foreach ($endpoints as $endpoint) {
    $method = $endpoint[0];
    $path = $endpoint[1];
    $description = $endpoint[2];
    
    // Use file_get_contents with stream context instead of curl
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => "Accept: application/json\r\n",
            'ignore_errors' => true,
        ]
    ]);
    
    try {
        $response = @file_get_contents("http://127.0.0.1:8000{$path}", false, $context);
        $headers = $http_response_header ?? [];
        
        // Extract HTTP status code
        $status = 0;
        foreach ($headers as $header) {
            if (preg_match('/HTTP\/\d\.\d (\d+)/', $header, $matches)) {
                $status = (int)$matches[1];
                break;
            }
        }
        
        if ($status === 401 || $status === 403) {
            echo "✅ $method $path\n";
            echo "   └─ $description → HTTP $status (Protected)\n\n";
            $passed++;
        } else {
            echo "❌ $method $path\n";
            echo "   └─ $description → HTTP $status (Expected 401 or 403)\n\n";
            $failed++;
        }
    } catch (\Exception $e) {
        echo "⚠️  $method $path\n";
        echo "   └─ Connection error: " . $e->getMessage() . "\n\n";
    }
}

echo "════════════════════════════════════════════════════════════════\n";
echo "  RESULTS\n";
echo "════════════════════════════════════════════════════════════════\n\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";
echo "⚠️  Total: " . count($endpoints) . "\n\n";

if ($failed === 0) {
    echo "🎉 All IDOR security fixes verified!\n\n";
} else {
    echo "⚠️  Some endpoints may not be properly protected.\n\n";
}

echo "════════════════════════════════════════════════════════════════\n\n";
?>
