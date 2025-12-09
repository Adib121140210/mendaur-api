<?php
require 'vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $pdo = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'],
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD']
    );
    
    echo "=== Users Table Structure ===\n";
    $result = $pdo->query("SHOW COLUMNS FROM users");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " => " . $row['Type'] . " (Key: " . ($row['Key'] ?: 'NONE') . ")\n";
    }
    
    echo "\n=== Sessions Table Structure ===\n";
    $result = $pdo->query("SHOW COLUMNS FROM sessions");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " => " . $row['Type'] . " (Key: " . ($row['Key'] ?: 'NONE') . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
