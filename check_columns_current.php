<?php
$mysqli = new mysqli('localhost', 'root', '', 'mendaur_api');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$tables = ['users', 'badges', 'tabung_sampah', 'penukaran_produk', 'penarikan_tunai'];

foreach ($tables as $table) {
    $result = $mysqli->query("SHOW COLUMNS FROM $table");
    echo "\n=== $table ===\n";
    while($row = $result->fetch_assoc()) {
        $key = $row['Key'] === 'PRI' ? ' [PRIMARY]' : '';
        echo '- ' . $row['Field'] . ' (' . $row['Type'] . ')' . $key . PHP_EOL;
    }
}

$mysqli->close();
?>
