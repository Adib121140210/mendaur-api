<?php
// Comprehensive diagnostic for form submission issues

$conn = new PDO('mysql:host=localhost;dbname=mendaur_api', 'root', '');

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "FORM SUBMISSION ISSUES - ROOT CAUSE ANALYSIS\n";
echo str_repeat('=', 80) . "\n\n";

// Check 1: Users Table
echo "1. USERS TABLE - Checking for required users\n";
echo str_repeat('-', 80) . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM users");
$total = $result->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total users: $total\n\n";

$result = $conn->query("SELECT user_id, name, email FROM users LIMIT 10");
$users = $result->fetchAll(PDO::FETCH_ASSOC);

if (count($users) > 0) {
    printf("%-10s %-30s %-30s\n", "User ID", "Name", "Email");
    echo str_repeat('-', 80) . "\n";
    foreach ($users as $user) {
        printf("%-10s %-30s %-30s\n", 
            $user['user_id'],
            substr($user['name'], 0, 28),
            substr($user['email'], 0, 28)
        );
    }
} else {
    echo "❌ NO USERS FOUND IN DATABASE!\n";
}

// Check 2: Jadwal Penyetoran Table
echo "\n\n2. JADWAL PENYETORAN TABLE - Checking schedule data\n";
echo str_repeat('-', 80) . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM jadwal_penyetorans");
$total = $result->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total schedules: $total\n\n";

$result = $conn->query("SELECT jadwal_penyetoran_id, tanggal, waktu_mulai, waktu_selesai, lokasi FROM jadwal_penyetorans LIMIT 10");
$schedules = $result->fetchAll(PDO::FETCH_ASSOC);

if (count($schedules) > 0) {
    printf("%-15s %-12s %-12s %-12s %-30s\n", "Schedule ID", "Date", "Start", "End", "Location");
    echo str_repeat('-', 80) . "\n";
    foreach ($schedules as $sched) {
        printf("%-15s %-12s %-12s %-12s %-30s\n",
            $sched['jadwal_penyetoran_id'],
            $sched['tanggal'],
            $sched['waktu_mulai'],
            $sched['waktu_selesai'],
            substr($sched['lokasi'], 0, 28)
        );
    }
} else {
    echo "❌ NO SCHEDULES FOUND IN DATABASE!\n";
}

// Check 3: TabungSampah Table (existing deposits)
echo "\n\n3. TABUNG SAMPAH TABLE - Checking existing deposits\n";
echo str_repeat('-', 80) . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM tabung_sampah");
$total = $result->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total deposits: $total\n\n";

$result = $conn->query("SELECT tabung_sampah_id, user_id, jadwal_penyetoran_id, jenis_sampah, berat_kg, status FROM tabung_sampah LIMIT 5");
$deposits = $result->fetchAll(PDO::FETCH_ASSOC);

if (count($deposits) > 0) {
    printf("%-15s %-10s %-15s %-20s %-12s %-12s\n", "Deposit ID", "User ID", "Schedule ID", "Waste Type", "Weight", "Status");
    echo str_repeat('-', 80) . "\n";
    foreach ($deposits as $dep) {
        printf("%-15s %-10s %-15s %-20s %-12s %-12s\n",
            $dep['tabung_sampah_id'],
            $dep['user_id'],
            $dep['jadwal_penyetoran_id'],
            $dep['jenis_sampah'],
            $dep['berat_kg'] . 'kg',
            $dep['status']
        );
    }
}

// Check 4: Foreign Key Issues
echo "\n\n4. FOREIGN KEY VALIDATION\n";
echo str_repeat('-', 80) . "\n";

// Check if users in tabung_sampah exist in users table
$result = $conn->query("
    SELECT DISTINCT ts.user_id 
    FROM tabung_sampah ts
    LEFT JOIN users u ON ts.user_id = u.user_id
    WHERE u.user_id IS NULL
");
$orphaned_users = $result->fetchAll(PDO::FETCH_ASSOC);

if (count($orphaned_users) > 0) {
    echo "⚠️  Found orphaned user IDs in tabung_sampah:\n";
    foreach ($orphaned_users as $orphan) {
        echo "   - User ID {$orphan['user_id']}\n";
    }
} else {
    echo "✅ All user IDs in tabung_sampah exist in users table\n";
}

// Check if schedules in tabung_sampah exist
$result = $conn->query("
    SELECT DISTINCT ts.jadwal_penyetoran_id 
    FROM tabung_sampah ts
    LEFT JOIN jadwal_penyetorans jp ON ts.jadwal_penyetoran_id = jp.jadwal_penyetoran_id
    WHERE jp.jadwal_penyetoran_id IS NULL
");
$orphaned_schedules = $result->fetchAll(PDO::FETCH_ASSOC);

if (count($orphaned_schedules) > 0) {
    echo "⚠️  Found orphaned schedule IDs in tabung_sampah:\n";
    foreach ($orphaned_schedules as $orphan) {
        echo "   - Schedule ID {$orphan['jadwal_penyetoran_id']}\n";
    }
} else {
    echo "✅ All schedule IDs in tabung_sampah exist in jadwal_penyetorans table\n";
}

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "ISSUES FOUND:\n";
echo str_repeat('-', 80) . "\n";

// Summary
$issues = [];
if ($total == 0) {
    $issues[] = "❌ No schedules in database";
}

$result = $conn->query("SELECT COUNT(*) as total FROM users");
$user_count = $result->fetch(PDO::FETCH_ASSOC)['total'];
if ($user_count == 0) {
    $issues[] = "❌ No users in database";
} else if ($user_count < 3) {
    $issues[] = "⚠️  Less than 3 users in database (frontend expects user_id=3)";
}

if (count($issues) == 0) {
    echo "✅ No critical issues found\n";
    echo "✅ All data appears to be properly seeded\n";
} else {
    foreach ($issues as $issue) {
        echo "$issue\n";
    }
}

echo "\n" . str_repeat('=', 80) . "\n";
