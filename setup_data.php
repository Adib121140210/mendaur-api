<?php
// Quick setup script for test data

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Produk;

// Update User 1 with more points
$user = User::find(1);
if ($user) {
    $user->update([
        'total_poin' => 1000,
        'total_sampah' => 50
    ]);
    echo "✅ Updated User 1 (Adib Surya): Points = 1000, Total Sampah = 50\n";
}

// Add sample products
$products = [
    [
        'nama' => 'Lampu LED',
        'deskripsi' => 'Lampu LED hemat energi 10W',
        'harga_poin' => 500,
        'stok' => 10,
        'kategori' => 'Elektronik',
        'status' => 'tersedia'
    ],
    [
        'nama' => 'Botol Reusable',
        'deskripsi' => 'Botol minum ramah lingkungan 1L',
        'harga_poin' => 150,
        'stok' => 20,
        'kategori' => 'Aksesoris',
        'status' => 'tersedia'
    ],
    [
        'nama' => 'Tas Belanja Kain',
        'deskripsi' => 'Tas belanja dari bahan kain organik',
        'harga_poin' => 200,
        'stok' => 15,
        'kategori' => 'Aksesoris',
        'status' => 'tersedia'
    ],
];

foreach ($products as $product) {
    $existing = Produk::where('nama', $product['nama'])->first();
    if (!$existing) {
        Produk::create($product);
        echo "✅ Created product: {$product['nama']} ({$product['harga_poin']} points)\n";
    } else {
        echo "ℹ️  Product already exists: {$product['nama']}\n";
    }
}

echo "\n🎉 Setup complete!\n";
echo "User ID 1 now has 1000 points\n";
echo "Sample products created\n";
