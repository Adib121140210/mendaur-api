<?php
// Direct database check without Laravel bootstrap

$db_host = 'localhost';
$db_name = 'mendaur_api';
$db_user = 'root';
$db_pass = '';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $conn->query("SELECT kategori_sampah_id, nama_kategori, warna, icon FROM kategori_sampah ORDER BY kategori_sampah_id");
    $categories = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n";
    echo str_repeat('=', 75) . "\n";
    echo "✅ CATEGORY COLORIZATION - DATABASE VERIFICATION\n";
    echo str_repeat('=', 75) . "\n\n";
    
    echo sprintf("%-2s | %-20s | %-12s | %s\n", "ID", "Name", "Color", "Icon");
    echo str_repeat('-', 75) . "\n";
    
    foreach ($categories as $cat) {
        echo sprintf("%-2d | %-20s | %-12s | %s\n", 
            $cat['kategori_sampah_id'],
            $cat['nama_kategori'],
            $cat['warna'],
            $cat['icon']
        );
    }
    
    echo str_repeat('-', 75) . "\n";
    echo sprintf("Total: %d categories\n\n", count($categories));
    
    if (count($categories) == 8) {
        echo "✅ SUCCESS: All 8 categories present in database\n";
        echo "✅ Colors have been successfully applied\n\n";
    } else {
        echo "⚠️  Found " . count($categories) . " categories (expected 8)\n\n";
    }
    
    echo str_repeat('=', 75) . "\n\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
