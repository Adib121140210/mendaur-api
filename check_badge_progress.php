<?php
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'mendaur_api';

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$result = $conn->query('DESCRIBE badge_progress');
echo "=== badge_progress table structure ===\n";
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ') ' . $row['Key'] . "\n";
}

$result = $conn->query('SELECT bp.badge_progress_id, bp.user_id, bp.badge_id, b.badge_id as badge_pk, b.nama FROM badge_progress bp LEFT JOIN badge b ON bp.badge_id = b.badge_id LIMIT 5');
echo "\n=== Sample data with left join ===\n";
while($row = $result->fetch_assoc()) {
    echo "badge_progress_id: {$row['badge_progress_id']}, user_id: {$row['user_id']}, badge_id: {$row['badge_id']}, badge_pk: {$row['badge_pk']}, nama: {$row['nama']}\n";
}

$conn->close();
