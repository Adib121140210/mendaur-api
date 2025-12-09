<?php
$mysqli = new mysqli('localhost', 'root', '', 'mendaur_api');

$result = $mysqli->query('SHOW COLUMNS FROM roles');
echo 'Roles table columns:' . PHP_EOL;
while($row = $result->fetch_assoc()) {
    $key = $row['Key'] === 'PRI' ? ' [PRIMARY]' : '';
    echo '- ' . $row['Field'] . ' (' . $row['Type'] . ')' . $key . PHP_EOL;
}

// Check produks too
echo "\nProduk table columns:\n";
$result = $mysqli->query('SHOW COLUMNS FROM produks');
while($row = $result->fetch_assoc()) {
    $key = $row['Key'] === 'PRI' ? ' [PRIMARY]' : '';
    echo '- ' . $row['Field'] . ' (' . $row['Type'] . ')' . $key . PHP_EOL;
}

$mysqli->close();
?>
