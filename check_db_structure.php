<?php
// Check actual users table structure

$conn = new PDO('mysql:host=localhost;dbname=mendaur_api', 'root', '');

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "DATABASE DIAGNOSTIC\n";
echo str_repeat('=', 80) . "\n\n";

// Check users table structure
echo "1. USERS TABLE STRUCTURE\n";
echo str_repeat('-', 80) . "\n";

$result = $conn->query("DESC users");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

printf("%-20s %-30s %-15s %s\n", "Field", "Type", "Null", "Key");
echo str_repeat('-', 80) . "\n";
foreach ($columns as $col) {
    $key = $col['Key'] ? $col['Key'] : '';
    printf("%-20s %-30s %-15s %s\n", $col['Field'], $col['Type'], $col['Null'], $key);
}

// Count users
echo "\n2. USER COUNT\n";
echo str_repeat('-', 80) . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM users");
$total = $result->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total users: $total\n";

// List users with available columns
echo "\n3. AVAILABLE USERS\n";
echo str_repeat('-', 80) . "\n";

$result = $conn->query("SELECT * FROM users LIMIT 5");
$users = $result->fetchAll(PDO::FETCH_ASSOC);

if (count($users) > 0) {
    echo "Sample user data:\n";
    echo json_encode($users[0], JSON_PRETTY_PRINT) . "\n";
    
    echo "\nUser IDs present:\n";
    foreach ($users as $user) {
        echo "  - User ID: " . (isset($user['user_id']) ? $user['user_id'] : 'N/A') . "\n";
    }
}

// Check jadwal_penyetorans table
echo "\n4. JADWAL PENYETORANS TABLE\n";
echo str_repeat('-', 80) . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM jadwal_penyetorans");
$total = $result->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total schedules: $total\n\n";

$result = $conn->query("SELECT * FROM jadwal_penyetorans LIMIT 3");
$schedules = $result->fetchAll(PDO::FETCH_ASSOC);

if (count($schedules) > 0) {
    printf("%-20s %-12s %-12s %-12s %-30s\n", "Schedule ID", "Date", "Start", "End", "Location");
    echo str_repeat('-', 80) . "\n";
    foreach ($schedules as $sched) {
        printf("%-20s %-12s %-12s %-12s %-30s\n",
            isset($sched['jadwal_penyetoran_id']) ? $sched['jadwal_penyetoran_id'] : (isset($sched['id']) ? $sched['id'] : 'N/A'),
            $sched['tanggal'],
            $sched['waktu_mulai'],
            $sched['waktu_selesai'],
            substr($sched['lokasi'], 0, 28)
        );
    }
}

// Check tabung_sampah table
echo "\n5. TABUNG SAMPAH TABLE\n";
echo str_repeat('-', 80) . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM tabung_sampah");
$total = $result->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total deposits: $total\n\n";

$result = $conn->query("SELECT tabung_sampah_id, user_id, jadwal_penyetoran_id, jenis_sampah FROM tabung_sampah LIMIT 3");
$deposits = $result->fetchAll(PDO::FETCH_ASSOC);

if (count($deposits) > 0) {
    printf("%-15s %-10s %-15s %-20s\n", "Deposit ID", "User ID", "Schedule ID", "Waste Type");
    echo str_repeat('-', 80) . "\n";
    foreach ($deposits as $dep) {
        printf("%-15s %-10s %-15s %-20s\n",
            $dep['tabung_sampah_id'],
            $dep['user_id'],
            $dep['jadwal_penyetoran_id'],
            $dep['jenis_sampah']
        );
    }
}

echo "\n";
echo str_repeat('=', 80) . "\n";
