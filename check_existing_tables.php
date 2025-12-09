<?php
$mysqli = new mysqli('localhost', 'root', '', 'mendaur_api');

$result = $mysqli->query('SHOW TABLES');
echo 'Tables in database:' . PHP_EOL;
$tables = [];
while($row = $result->fetch_row()) {
    echo '- ' . $row[0] . PHP_EOL;
    $tables[] = $row[0];
}

echo "\nMissing tables:\n";
$expected = ['roles', 'badges', 'produks', 'jenis_sampahs', 'kategori_sampahs', 'jadwal_penyetorans', 'users', 'role_permissions', 'tabung_sampah', 'penukaran_produk', 'penarikan_tunai', 'log_poin', 'log_user_activity', 'badge_progress', 'artikels', 'audit_logs', 'notifikasis'];

foreach ($expected as $table) {
    if (!in_array($table, $tables)) {
        echo "- $table (MISSING)\n";
    }
}

$mysqli->close();
?>
