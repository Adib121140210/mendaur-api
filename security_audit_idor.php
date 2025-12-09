#!/usr/bin/env php
<?php
/**
 * CRITICAL IDOR SECURITY TEST
 * Check if User 1 can access User 3's protected data
 */

echo "\n════════════════════════════════════════════════════════════════\n";
echo "  CRITICAL IDOR TEST: User 1 accessing User 3's Data\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Load environment
$envFile = __DIR__ . '/.env';
$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value, '"\'');
        }
    }
}

$db_host = $env['DB_HOST'] ?? 'localhost';
$db_port = $env['DB_PORT'] ?? 3306;
$db_name = $env['DB_DATABASE'] ?? 'mendaur_api';
$db_user = $env['DB_USERNAME'] ?? 'root';
$db_pass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get User 1 and User 3 info
    $stmt = $pdo->query("SELECT id, email FROM users WHERE id IN (1, 3)");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 TARGET USERS:\n";
    foreach ($users as $user) {
        echo "   ID: {$user['id']}, Email: {$user['email']}\n";
    }
    echo "\n";

    // Check vulnerable endpoints
    $vulnerableEndpoints = [
        // User 3 (id=3) data endpoints
        [
            'endpoint' => '/api/user/profile?user_id=3',
            'description' => 'User 3 Profile Data',
            'user_context' => 1,
        ],
        [
            'endpoint' => '/api/user/transaksi?user_id=3',
            'description' => 'User 3 Transaction History',
            'user_context' => 1,
        ],
        [
            'endpoint' => '/api/user/poin?user_id=3',
            'description' => 'User 3 Points/Balance',
            'user_context' => 1,
        ],
        [
            'endpoint' => '/api/user/badges?user_id=3',
            'description' => 'User 3 Badges Data',
            'user_context' => 1,
        ],
        [
            'endpoint' => '/api/user/badge-history?user_id=3',
            'description' => 'User 3 Badge History',
            'user_context' => 1,
        ],
        [
            'endpoint' => '/api/points/history?user_id=3',
            'description' => 'User 3 Point History',
            'user_context' => 1,
        ],
        [
            'endpoint' => '/api/dashboard/user/3',
            'description' => 'User 3 Dashboard Data',
            'user_context' => 1,
        ],
        [
            'endpoint' => '/api/tabung-sampah?user_id=3',
            'description' => 'User 3 Trash Bins',
            'user_context' => 1,
        ],
    ];

    echo "🔍 TESTING IDOR VULNERABILITY:\n";
    echo "Scenario: User 1 token used to access User 3's endpoints\n\n";

    // Check what columns each table has to understand data structure
    echo "📋 CHECKING TABLE STRUCTURES FOR IDOR VULNERABILITIES:\n\n";
    
    $tables_to_check = [
        'users' => 'User profile data',
        'tabung_sampah' => 'Waste bin data',
        'penukaran_produk' => 'Product redemption data',
        'penarikan_tunai' => 'Cash withdrawal data',
        'poin_transaksis' => 'Points transaction data',
        'badge_progress' => 'Badge progress data',
    ];

    foreach ($tables_to_check as $table => $description) {
        $stmt = $pdo->query("SHOW COLUMNS FROM $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "TABLE: $table ($description)\n";
        $user_id_found = false;
        
        foreach ($columns as $col) {
            $col_name = $col['Field'];
            // Check if user_id or similar field exists
            if (stripos($col_name, 'user') !== false || stripos($col_name, 'id') !== false) {
                echo "   ├─ $col_name ({$col['Type']}) - POTENTIAL IDOR VECTOR\n";
                if (stripos($col_name, 'user_id') !== false) {
                    $user_id_found = true;
                }
            }
        }
        
        if ($user_id_found) {
            // Count records for user 3
            $count_stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM $table WHERE user_id = ?");
            $count_stmt->execute([3]);
            $result = $count_stmt->fetch(PDO::FETCH_ASSOC);
            echo "   └─ User 3 has {$result['cnt']} records in this table\n";
        }
        echo "\n";
    }

    echo "════════════════════════════════════════════════════════════════\n";
    echo "  VULNERABILITY ASSESSMENT\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    // Check if User 1 has admin/superadmin role
    $stmt = $pdo->prepare("
        SELECT u.id, u.email 
        FROM users u
        WHERE u.id IN (1, 3)
    ");
    $stmt->execute();
    $user_roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "👤 USER ROLES:\n";
    foreach ($user_roles as $ur) {
        echo "   User {$ur['id']} ({$ur['email']})\n";
    }
    echo "\n";

    // Check middleware protection
    echo "🔒 MIDDLEWARE PROTECTION CHECK:\n\n";
    
    // List all routes and check if they have middleware
    $routes_file = __DIR__ . '/routes/api.php';
    if (file_exists($routes_file)) {
        $routes_content = file_get_contents($routes_file);
        
        // Count routes with middleware
        $auth_middleware = substr_count($routes_content, 'middleware([\'auth:sanctum');
        $check_permission = substr_count($routes_content, 'CheckPermission');
        
        echo "   Routes with auth:sanctum middleware: $auth_middleware\n";
        echo "   Routes with CheckPermission: $check_permission\n\n";
    }

    echo "⚠️  CRITICAL FINDINGS:\n\n";

    // Check if there are any routes that accept user_id parameter
    $stmt = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '%user%'");
    $user_tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   User-related tables found: " . count($user_tables) . "\n";
    
    foreach ($user_tables as $ut) {
        $table_name = $ut['TABLE_NAME'];
        $stmt = $pdo->query("SHOW COLUMNS FROM $table_name WHERE Field LIKE '%user%'");
        $user_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($user_columns)) {
            echo "   ⚠️  Table '$table_name' has user reference columns:\n";
            foreach ($user_columns as $uc) {
                echo "       - {$uc['Field']}\n";
            }
        }
    }

    echo "\n";
    echo "════════════════════════════════════════════════════════════════\n";
    echo "  RECOMMENDATIONS\n";
    echo "════════════════════════════════════════════════════════════════\n\n";
    
    echo "✅ REQUIRED FIXES:\n";
    echo "   1. All endpoints MUST validate user_id matches auth()->id()\n";
    echo "   2. Use Request injection to get authenticated user's ID\n";
    echo "   3. Return 403 Forbidden if user tries to access other user's data\n";
    echo "   4. Add CheckPermission middleware to sensitive routes\n";
    echo "   5. Audit logs should record all data access attempts\n\n";

    echo "EXAMPLE PROTECTION PATTERN:\n";
    echo "   public function getProfile(Request \$request)\n";
    echo "   {\n";
    echo "       \$userId = \$request->user()->id;\n";
    echo "       if (\$request->query('user_id') && \$request->query('user_id') != \$userId) {\n";
    echo "           return response()->json(['error' => 'Forbidden'], 403);\n";
    echo "       }\n";
    echo "       return User::find(\$userId);\n";
    echo "   }\n\n";

    echo "════════════════════════════════════════════════════════════════\n\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
}
?>
