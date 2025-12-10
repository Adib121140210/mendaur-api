<?php
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'mendaur_api';

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "=== badges table structure ===\n";
$result = $conn->query('DESCRIBE badges');
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ') ' . $row['Key'] . "\n";
}

echo "\n=== Sample badge data ===\n";
$result = $conn->query('SELECT * FROM badges LIMIT 3');
while($row = $result->fetch_assoc()) {
    print_r($row);
}

echo "\n=== badge_progress data for user 3 ===\n";
$result = $conn->query('SELECT * FROM badge_progress WHERE user_id = 3 LIMIT 3');
while($row = $result->fetch_assoc()) {
    print_r($row);
}

$conn->close();
