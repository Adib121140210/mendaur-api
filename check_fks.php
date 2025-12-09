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
    
    echo "=== Foreign Keys Referencing Users Table ===\n";
    $query = "
        SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = ? 
        AND REFERENCED_TABLE_NAME = 'users'
        ORDER BY TABLE_NAME
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_ENV['DB_DATABASE']]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $issue = $row['REFERENCED_COLUMN_NAME'] === 'id' ? ' ⚠️ WRONG!' : ' ✓ OK';
        echo $row['TABLE_NAME'] . "." . $row['COLUMN_NAME'] . 
             " -> " . $row['REFERENCED_TABLE_NAME'] . "." . $row['REFERENCED_COLUMN_NAME'] . 
             $issue . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
