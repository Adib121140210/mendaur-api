<?php

$ch = curl_init('http://localhost:8000/api/kategori-sampah');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$data = json_decode($response, true);

if ($data && isset($data['data'])) {
    echo "\n✅ CATEGORY COLORIZATION TEST - SUCCESS\n";
    echo str_repeat('=', 70) . "\n\n";
    
    echo "Categories with Colors:\n";
    echo str_repeat('-', 70) . "\n";
    echo sprintf("%-2s | %-15s | %-10s | %s\n", "ID", "Name", "Color", "Icon");
    echo str_repeat('-', 70) . "\n";
    
    foreach ($data['data'] as $cat) {
        echo sprintf("%-2d | %-15s | %-10s | %s\n", 
            $cat['kategori_sampah_id'],
            $cat['nama_kategori'],
            $cat['warna'],
            $cat['icon']
        );
    }
    echo str_repeat('-', 70) . "\n";
    echo sprintf("Total Categories: %d\n\n", count($data['data']));
    
    // Verify expected categories
    $expectedCategories = [
        1 => ['name' => 'Plastik', 'color' => '#2196F3'],
        2 => ['name' => 'Kertas', 'color' => '#8B4513'],
        3 => ['name' => 'Logam', 'color' => '#A9A9A9'],
        4 => ['name' => 'Kaca', 'color' => '#00BCD4'],
        5 => ['name' => 'Elektronik', 'color' => '#FF9800'],
        6 => ['name' => 'Tekstil', 'color' => '#E91E63'],
        7 => ['name' => 'Pecah Belah', 'color' => '#00BCD4'],
        8 => ['name' => 'Lainnya', 'color' => '#607D8B'],
    ];
    
    echo "Verification:\n";
    echo str_repeat('-', 70) . "\n";
    foreach ($expectedCategories as $id => $expected) {
        $found = false;
        foreach ($data['data'] as $cat) {
            if ($cat['kategori_sampah_id'] == $id) {
                $match = ($cat['nama_kategori'] === $expected['name'] && 
                         $cat['warna'] === $expected['color']);
                $status = $match ? '✅' : '❌';
                echo sprintf("%s ID %d: %s (color: %s)\n", $status, $id, 
                    $cat['nama_kategori'], $cat['warna']);
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo sprintf("❌ ID %d: NOT FOUND\n", $id);
        }
    }
    echo str_repeat('=', 70) . "\n\n";
} else {
    echo "❌ Error: Could not retrieve kategori data\n";
    echo "Response: " . var_export($data, true) . "\n";
}

curl_close($ch);
