<?php

try {
    // Connect to MySQL without specifying a database
    $conn = new PDO('mysql:host=127.0.0.1', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database
    $conn->exec('CREATE DATABASE IF NOT EXISTS mendaur_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    echo "✅ Database 'mendaur_api' created successfully!\n";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
