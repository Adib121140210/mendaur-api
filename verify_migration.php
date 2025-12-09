<?php
$mysqli = new mysqli('localhost', 'root', '', 'mendaur_api');

$tables = ['roles', 'badges', 'produks', 'jenis_sampah', 'kategori_sampah', 'jadwal_penyetorans', 'users', 'role_permissions', 'tabung_sampah', 'penukaran_produk', 'penarikan_tunai', 'poin_transaksis', 'log_aktivitas', 'badge_progress', 'artikels', 'audit_logs', 'notifikasi'];

echo "PRIMARY KEY VERIFICATION:\n";
echo "=".str_repeat("=", 70)."=\n";

foreach ($tables as $table) {
    $result = $mysqli->query("SHOW COLUMNS FROM $table WHERE `Key` = 'PRI'");
    $pk = $result->fetch_assoc();
    
    if ($pk) {
        echo sprintf("%-30s => %-25s %s\n", $table, $pk['Field'], "✓");
    } else {
        echo sprintf("%-30s => %-25s %s\n", $table, "NO PK!", "✗");
    }
}

echo "=".str_repeat("=", 70)."=\n";

$mysqli->close();
?>
