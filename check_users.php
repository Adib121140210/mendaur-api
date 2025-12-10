<?php
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'mendaur_api';

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$result = $conn->query('SELECT user_id, nama, email FROM users LIMIT 3');
while($row = $result->fetch_assoc()) {
    echo "User {$row['user_id']}: {$row['nama']} ({$row['email']})\n";
}

$conn->close();
