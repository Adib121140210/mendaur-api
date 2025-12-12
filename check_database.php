<?php

// Quick database check script
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = env('DB_HOST');
$user = env('DB_USERNAME');
$pass = env('DB_PASSWORD');
$db = env('DB_DATABASE');

try {
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    echo "✅ Database connection successful\n\n";

    // Check tables
    $result = $conn->query("SHOW TABLES");
    echo "Tables in database:\n";
    $tables = [];
    while ($row = $result->fetch_array()) {
        echo "  - " . $row[0] . "\n";
        $tables[] = $row[0];
    }

    echo "\n✅ Total tables: " . count($tables) . "\n";

    // Check users table structure
    echo "\n=== Users Table Structure ===\n";
    $result = $conn->query("DESCRIBE users");
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ")" . ($row['Key'] === 'PRI' ? " [PRIMARY KEY]" : "") . "\n";
    }

    // Check if roles table exists and structure
    echo "\n=== Roles Table Structure ===\n";
    $result = $conn->query("DESCRIBE roles");
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ")" . ($row['Key'] === 'PRI' ? " [PRIMARY KEY]" : "") . "\n";
    }

    $conn->close();

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
