<?php

$mysqli = new mysqli('localhost', 'root', '', 'mendaur_api');

if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error;
    exit(1);
}

echo "=== Users Table Structure ===\n";
$result = $mysqli->query('DESCRIBE users');
while ($row = $result->fetch_assoc()) {
    $key = $row['Key'] ? " (KEY: " . $row['Key'] . ")" : "";
    echo $row['Field'] . ": " . $row['Type'] . $key . "\n";
}

echo "\n=== Primary Key Info ===\n";
$result = $mysqli->query('SHOW KEYS FROM users WHERE Key_name = "PRIMARY"');
if ($row = $result->fetch_assoc()) {
    echo "Primary Key Column: " . $row['Column_name'] . "\n";
    echo "Type: " . ($row['Seq_in_index'] ? "Composite" : "Single") . "\n";
}

echo "\n=== Foreign Keys Sample ===\n";
$result = $mysqli->query("
    SELECT
        TABLE_NAME,
        COLUMN_NAME,
        CONSTRAINT_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'mendaur_api'
    AND COLUMN_NAME = 'user_id'
    LIMIT 5
");

while ($row = $result->fetch_assoc()) {
    echo $row['TABLE_NAME'] . ".{$row['COLUMN_NAME']} -> {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
}

echo "\nMigration successful! ✅\n";
$mysqli->close();
