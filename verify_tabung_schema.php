<?php
// Direct database test for tabung_sampah schema

$conn = new PDO('mysql:host=localhost;dbname=mendaur_api', 'root', '');

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "TABUNG_SAMPAH SCHEMA VERIFICATION\n";
echo str_repeat('=', 80) . "\n\n";

// Check column names
$result = $conn->query("DESC tabung_sampah");
$cols = $result->fetchAll(PDO::FETCH_COLUMN);

echo "Column Names in tabung_sampah:\n";
echo str_repeat('-', 80) . "\n";
foreach ($cols as $col) {
    $status = ($col === 'jadwal_penyetoran_id') ? ' ✅' : (($col === 'jadwal_id') ? ' ❌' : '');
    echo "  - $col$status\n";
}

// Check foreign keys
echo "\n\nForeign Key Constraints:\n";
echo str_repeat('-', 80) . "\n";

$result = $conn->query("SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_NAME = 'tabung_sampah'
AND REFERENCED_TABLE_NAME IS NOT NULL");

$fks = $result->fetchAll(PDO::FETCH_ASSOC);
if (count($fks) > 0) {
    foreach ($fks as $fk) {
        $status = ($fk['COLUMN_NAME'] === 'jadwal_penyetoran_id') ? ' ✅' : '';
        echo sprintf("  %s: %s → %s.%s%s\n",
            $fk['CONSTRAINT_NAME'],
            $fk['COLUMN_NAME'],
            $fk['REFERENCED_TABLE_NAME'],
            $fk['REFERENCED_COLUMN_NAME'],
            $status
        );
    }
} else {
    echo "  No foreign keys found\n";
}

// Check data
echo "\n\nSample Data:\n";
echo str_repeat('-', 80) . "\n";

$result = $conn->query("SELECT tabung_sampah_id, user_id, jadwal_penyetoran_id, jenis_sampah, status FROM tabung_sampah LIMIT 3");
$rows = $result->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) > 0) {
    printf("%-15s %-10s %-20s %-20s %-15s\n", "ID", "User ID", "Jadwal Penyetoran", "Jenis Sampah", "Status");
    echo str_repeat('-', 80) . "\n";
    foreach ($rows as $row) {
        printf("%-15s %-10s %-20s %-20s %-15s\n",
            $row['tabung_sampah_id'],
            $row['user_id'],
            $row['jadwal_penyetoran_id'],
            $row['jenis_sampah'],
            $row['status']
        );
    }
} else {
    echo "  No data found. Table might be empty or data needs to be reseeded.\n";
}

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "✅ SCHEMA VERIFICATION COMPLETE\n";
echo str_repeat('=', 80) . "\n\n";
