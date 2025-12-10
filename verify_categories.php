<?php
// Verify category colorization in database
use Illuminate\Support\Facades\DB;

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = \Illuminate\Http\Request::capture()
);

$categories = DB::table('kategori_sampah')
    ->orderBy('kategori_sampah_id')
    ->get(['kategori_sampah_id', 'nama_kategori', 'warna', 'icon']);

echo "\n";
echo str_repeat('=', 75) . "\n";
echo "✅ CATEGORY COLORIZATION TEST - VERIFICATION\n";
echo str_repeat('=', 75) . "\n\n";

echo sprintf("%-2s | %-20s | %-12s | %s\n", "ID", "Name", "Color", "Icon");
echo str_repeat('-', 75) . "\n";

$expectedColors = [
    1 => '#2196F3',
    2 => '#8B4513',
    3 => '#A9A9A9',
    4 => '#00BCD4',
    5 => '#FF9800',
    6 => '#E91E63',
    7 => '#00BCD4',
    8 => '#607D8B',
];

$allCorrect = true;
foreach ($categories as $cat) {
    $isCorrect = isset($expectedColors[$cat->kategori_sampah_id]) && 
                 $cat->warna === $expectedColors[$cat->kategori_sampah_id];
    $status = $isCorrect ? '✅' : '❌';
    
    echo sprintf("%-2d | %-20s | %-12s | %s %s\n", 
        $cat->kategori_sampah_id,
        $cat->nama_kategori,
        $cat->warna,
        $cat->icon,
        $status
    );
    
    if (!$isCorrect) {
        $allCorrect = false;
    }
}

echo str_repeat('-', 75) . "\n";
echo sprintf("Total Categories: %d\n\n", count($categories));

if ($allCorrect && count($categories) == 8) {
    echo "✅ ALL TESTS PASSED!\n";
    echo "✅ All 8 categories have correct colors\n";
    echo "✅ Database migration successful\n\n";
    echo str_repeat('=', 75) . "\n";
    exit(0);
} else {
    echo "❌ TESTS FAILED\n";
    echo "❌ Check color mappings above\n\n";
    echo str_repeat('=', 75) . "\n";
    exit(1);
}
