<?php
$conn = new PDO('mysql:host=localhost;dbname=mendaur_api', 'root', '');

echo "\nJadwal Penyetorans Table Schema:\n";
echo str_repeat('=', 60) . "\n";

$result = $conn->query('DESC jadwal_penyetorans');
$cols = $result->fetchAll(PDO::FETCH_ASSOC);

printf("%-20s %-20s %-10s %s\n", "Column", "Type", "Null", "Key");
echo str_repeat('-', 60) . "\n";

foreach ($cols as $col) {
    $key = $col['Key'] ?: '';
    printf("%-20s %-20s %-10s %s\n",
        $col['Field'],
        $col['Type'],
        $col['Null'],
        $key
    );
}

echo "\n";
echo str_repeat('=', 60) . "\n";

// Check first record
echo "\nFirst record in table:\n";
echo str_repeat('-', 60) . "\n";

$result = $conn->query('SELECT * FROM jadwal_penyetorans LIMIT 1');
$record = $result->fetch(PDO::FETCH_ASSOC);

echo json_encode($record, JSON_PRETTY_PRINT) . "\n";
