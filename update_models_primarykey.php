<?php
// List of models that need to be updated with primaryKey declaration
$models = [
    'User' => 'user_id',
    'Badge' => 'badge_id',
    'PenukaranProduk' => 'penukaran_produk_id',
    'PenarikanTunai' => 'penarikan_tunai_id',
    'PoinTransaksi' => 'poin_transaksi_id',
    'BadgeProgress' => 'badge_progress_id',
    'Artikel' => 'artikel_id',
    'Produk' => 'produk_id',
    'JadwalPenyetoran' => 'jadwal_penyetoran_id',
    'JenisSampah' => 'jenis_sampah_id',
    'KategoriSampah' => 'kategori_sampah_id',
    'Role' => 'role_id',
    'RolePermission' => 'role_permission_id',
    'AuditLog' => 'audit_log_id',
    'LogAktivitas' => 'log_user_activity_id',
    'Notifikasi' => 'notifikasi_id',
];

$basePath = __DIR__ . '/app/Models/';

foreach ($models as $modelName => $primaryKey) {
    $filePath = $basePath . $modelName . '.php';
    
    if (!file_exists($filePath)) {
        echo "⚠️  $modelName.php not found\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Check if it already has protected $primaryKey
    if (strpos($content, "protected \$primaryKey") !== false) {
        echo "✓ $modelName already has primaryKey\n";
        continue;
    }
    
    // Find the position to insert the primaryKey declaration
    // Look for "protected $table" or "protected $fillable" as anchors
    $insertPos = false;
    
    // Try to find after "protected $table"
    $tablePos = strpos($content, "protected \$table");
    if ($tablePos !== false) {
        // Find the end of that line
        $lineEnd = strpos($content, "\n", $tablePos);
        if ($lineEnd !== false) {
            $insertPos = $lineEnd + 1;
        }
    }
    
    // If not found, try "class definition"
    if ($insertPos === false) {
        $classPos = strpos($content, "class " . $modelName);
        if ($classPos !== false) {
            $bracePos = strpos($content, "{", $classPos);
            $insertPos = strpos($content, "\n", $bracePos) + 1;
        }
    }
    
    if ($insertPos !== false) {
        $insertion = "    protected \$primaryKey = '$primaryKey';\n";
        $newContent = substr_replace($content, $insertion, $insertPos, 0);
        
        file_put_contents($filePath, $newContent);
        echo "✓ Added primaryKey to $modelName\n";
    } else {
        echo "✗ Could not find insertion point for $modelName\n";
    }
}

echo "\nDone!\n";
?>
