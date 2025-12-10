<?php
// Check actual database schema for tabung_sampah and jadwal_penyetorans

$db_host = 'localhost';
$db_name = 'mendaur_api';
$db_user = 'root';
$db_pass = '';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "\n";
    echo str_repeat('=', 80) . "\n";
    echo "CURRENT DATABASE SCHEMA - TABUNG_SAMPAH & JADWAL_PENYETORANS\n";
    echo str_repeat('=', 80) . "\n\n";
    
    // Check tabung_sampah columns
    echo "1. TABUNG_SAMPAH TABLE STRUCTURE:\n";
    echo str_repeat('-', 80) . "\n";
    $result = $conn->query("DESC tabung_sampah");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        $key = $col['Key'] ? " [{$col['Key']}]" : '';
        echo sprintf("  %-20s %-20s %-10s %s%s\n", 
            $col['Field'],
            $col['Type'],
            $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL',
            $col['Default'] ? "DEFAULT {$col['Default']}" : '',
            $key
        );
    }
    
    // Check jadwal_penyetorans columns
    echo "\n2. JADWAL_PENYETORANS TABLE STRUCTURE:\n";
    echo str_repeat('-', 80) . "\n";
    $result = $conn->query("DESC jadwal_penyetorans");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        $key = $col['Key'] ? " [{$col['Key']}]" : '';
        echo sprintf("  %-20s %-20s %-10s %s%s\n", 
            $col['Field'],
            $col['Type'],
            $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL',
            $col['Default'] ? "DEFAULT {$col['Default']}" : '',
            $key
        );
    }
    
    // Check foreign keys
    echo "\n3. FOREIGN KEYS:\n";
    echo str_repeat('-', 80) . "\n";
    $result = $conn->query("SELECT 
        CONSTRAINT_NAME,
        TABLE_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_NAME IN ('tabung_sampah', 'jadwal_penyetorans')
    AND REFERENCED_TABLE_NAME IS NOT NULL");
    
    $fks = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($fks as $fk) {
        echo sprintf("  %s: %s.%s → %s.%s\n",
            $fk['CONSTRAINT_NAME'],
            $fk['TABLE_NAME'],
            $fk['COLUMN_NAME'],
            $fk['REFERENCED_TABLE_NAME'],
            $fk['REFERENCED_COLUMN_NAME']
        );
    }
    
    echo "\n";
    echo str_repeat('=', 80) . "\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
