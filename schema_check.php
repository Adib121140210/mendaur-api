<?php
$conn = new PDO('mysql:host=localhost;dbname=mendaur_api', 'root', '');

echo "\n" . str_repeat('=', 80) . "\n";
echo "TABUNG_SAMPAH TABLE STRUCTURE\n";
echo str_repeat('=', 80) . "\n\n";

$result = $conn->query('DESC tabung_sampah');
$cols = $result->fetchAll(PDO::FETCH_ASSOC);
echo sprintf("%-25s %-30s %-15s %-20s %s\n", "Field", "Type", "Null", "Key", "Default");
echo str_repeat('-', 80) . "\n";
foreach ($cols as $c) {
    $key = $c['Key'] ? $c['Key'] : '';
    $default = $c['Default'] !== NULL ? $c['Default'] : '';
    printf("%-25s %-30s %-15s %-20s %s\n", 
        $c['Field'], $c['Type'], $c['Null'], $key, $default);
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "JADWAL_PENYETORANS TABLE STRUCTURE\n";
echo str_repeat('=', 80) . "\n\n";

$result = $conn->query('DESC jadwal_penyetorans');
$cols = $result->fetchAll(PDO::FETCH_ASSOC);
echo sprintf("%-25s %-30s %-15s %-20s %s\n", "Field", "Type", "Null", "Key", "Default");
echo str_repeat('-', 80) . "\n";
foreach ($cols as $c) {
    $key = $c['Key'] ? $c['Key'] : '';
    $default = $c['Default'] !== NULL ? $c['Default'] : '';
    printf("%-25s %-30s %-15s %-20s %s\n", 
        $c['Field'], $c['Type'], $c['Null'], $key, $default);
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "FOREIGN KEY RELATIONSHIPS\n";
echo str_repeat('=', 80) . "\n\n";

$result = $conn->query("SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_NAME IN ('tabung_sampah', 'jadwal_penyetorans')
AND REFERENCED_TABLE_NAME IS NOT NULL");

$fks = $result->fetchAll(PDO::FETCH_ASSOC);
if (count($fks) > 0) {
    foreach ($fks as $fk) {
        echo sprintf("%s\n  %s.%s → %s.%s\n\n",
            $fk['CONSTRAINT_NAME'],
            $fk['TABLE_NAME'],
            $fk['COLUMN_NAME'],
            $fk['REFERENCED_TABLE_NAME'],
            $fk['REFERENCED_COLUMN_NAME']
        );
    }
} else {
    echo "No foreign keys found\n\n";
}

echo str_repeat('=', 80) . "\n";
