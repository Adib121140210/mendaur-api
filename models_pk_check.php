<?php
/**
 * Script to add primary key declarations to all Eloquent models
 * This helps transition to table-specific primary key naming convention
 */

$modelsDirectory = 'app/Models';
$primaryKeyMapping = [
    'User' => 'user_id',
    'Badge' => 'badge_id',
    'TabungSampah' => 'tabung_sampah_id',
    'PenukaranProduk' => 'penukaran_produk_id',
    'PenarikanTunai' => 'penarikan_tunai_id',
    'BadgeProgress' => 'badge_progress_id',
    'Artikel' => 'artikel_id',
    'Produk' => 'produk_id',
    'JadwalPenyetoran' => 'jadwal_penyetoran_id',
    'JenisSampah' => 'jenis_sampah_id',
    'KategoriSampah' => 'kategori_sampah_id',
    'Role' => 'role_id',
    'RolePermission' => 'role_permission_id',
    'AuditLog' => 'audit_log_id',
    'LogPoin' => 'log_poin_id',
    'LogAktivitas' => 'log_user_activity_id',
];

echo "=== MODELS TO UPDATE ===\n\n";

foreach ($primaryKeyMapping as $modelName => $primaryKey) {
    $filePath = $modelsDirectory . '/' . $modelName . '.php';
    
    if (file_exists($filePath)) {
        echo "✅ $modelName.php → $primaryKey\n";
    } else {
        echo "⚠️  $modelName.php (NOT FOUND)\n";
    }
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Run migration: php artisan migrate\n";
echo "2. Update models: Run this script or manually add \$primaryKey\n";
echo "3. Update controllers: Change \$id to specific key names\n";
echo "4. Test all endpoints\n";
