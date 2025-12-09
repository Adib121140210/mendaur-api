<?php
$mysqli = new mysqli('localhost', 'root', '', 'mendaur_api');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check foreign keys in users table
echo "Foreign keys referencing tables:\n";
$result = $mysqli->query("
    SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_NAME = 'users' AND REFERENCED_TABLE_NAME IS NOT NULL
");

while($row = $result->fetch_assoc()) {
    echo $row['COLUMN_NAME'] . " -> " . $row['REFERENCED_TABLE_NAME'] . "." . $row['REFERENCED_COLUMN_NAME'] . "\n";
}

// Check all tables that have roles
echo "\n\nChecking roles table columns:\n";
$result = $mysqli->query("SHOW COLUMNS FROM roles");
while($row = $result->fetch_assoc()) {
    $key = $row['Key'] === 'PRI' ? ' [PRIMARY]' : '';
    echo '- ' . $row['Field'] . $key . PHP_EOL;
}

$mysqli->close();
?>
