<?php
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'mendaur_api';

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check the actual JOIN result
$sql = "SELECT 
    bp.badge_progress_id,
    bp.user_id,
    bp.badge_id,
    bp.current_value,
    bp.target_value,
    bp.progress_percentage,
    bp.is_unlocked,
    bp.unlocked_at,
    bp.created_at as bp_created,
    bp.updated_at as bp_updated,
    b.badge_id as b_badge_id,
    b.nama,
    b.deskripsi,
    b.icon,
    b.syarat_poin,
    b.syarat_setor,
    b.reward_poin,
    b.tipe
FROM badge_progress bp
LEFT JOIN badges b ON bp.badge_id = b.badge_id
WHERE bp.user_id = 3
ORDER BY bp.progress_percentage DESC, bp.badge_id
LIMIT 5";

$result = $conn->query($sql);
echo "=== Actual JOIN result ===\n";
while($row = $result->fetch_assoc()) {
    echo "Badge Progress ID: {$row['badge_progress_id']}, Badge ID: {$row['badge_id']}, Badge Name: {$row['nama']}, Type: {$row['tipe']}\n";
}

// Check if badge_progress has any records
$countResult = $conn->query("SELECT COUNT(*) as cnt FROM badge_progress WHERE user_id = 3");
$count = $countResult->fetch_assoc();
echo "\nTotal badge_progress records for user 3: " . $count['cnt'] . "\n";

// Check for the specific error - does the query return null for tipe?
$sqlWithNull = "SELECT bp.badge_id, b.tipe FROM badge_progress bp LEFT JOIN badges b ON bp.badge_id = b.badge_id WHERE bp.user_id = 3";
$result = $conn->query($sqlWithNull);
echo "\n=== Checking for null tipe values ===\n";
while($row = $result->fetch_assoc()) {
    echo "badge_id: {$row['badge_id']}, tipe: " . ($row['tipe'] ?? 'NULL') . "\n";
}

$conn->close();
