<?php
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'mendaur_api';

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "=== All tables containing 'badge' ===\n";
$result = $conn->query("SHOW TABLES LIKE '%badge%'");
while($row = $result->fetch_assoc()) {
    echo array_values($row)[0] . "\n";
}

echo "\n=== All tables ===\n";
$result = $conn->query("SHOW TABLES");
while($row = $result->fetch_assoc()) {
    echo array_values($row)[0] . "\n";
}

$conn->close();
