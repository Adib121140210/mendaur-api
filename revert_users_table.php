<?php
$mysqli = new mysqli('localhost', 'root', '', 'mendaur_api');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Revert user_id back to id
echo "Reverting users table...\n";
$mysqli->query("ALTER TABLE users CHANGE COLUMN user_id id INT NOT NULL");

if ($mysqli->error) {
    echo "Error: " . $mysqli->error . "\n";
} else {
    echo "✓ Users table reverted\n";
}

// Verify
$result = $mysqli->query("SHOW COLUMNS FROM users");
echo "\nUsers table columns after revert:\n";
while($row = $result->fetch_assoc()) {
    $key = $row['Key'] === 'PRI' ? ' [PRIMARY]' : '';
    echo '- ' . $row['Field'] . $key . PHP_EOL;
}

$mysqli->close();
?>
