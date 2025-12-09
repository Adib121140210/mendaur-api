<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
$tables = ['users', 'sessions', 'personal_access_tokens', 'user_badges'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW COLUMNS FROM $table");
    echo "\n=== $table ===\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ") [" . ($row['Key'] ? $row['Key'] : 'FK') . "]\n";
    }
}
?>
