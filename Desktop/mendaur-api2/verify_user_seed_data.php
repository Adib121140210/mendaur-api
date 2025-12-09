<?php

require __DIR__ . '/bootstrap/app.php';

use App\Models\User;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n========================================\n";
echo "VERIFIKASI DATA SEED USER\n";
echo "========================================\n\n";

// Get all users
$users = User::all();

if ($users->isEmpty()) {
    echo "❌ Tidak ada user dalam database!\n\n";
    exit(1);
}

echo "Total Users: " . $users->count() . "\n\n";

// Group by type
$konvensional = $users->where('tipe_nasabah', 'konvensional');
$modern = $users->where('tipe_nasabah', 'modern');

echo "KONVENSIONAL USERS: " . $konvensional->count() . "\n";
echo "MODERN USERS: " . $modern->count() . "\n\n";

echo "========== DETAIL DATA ==========\n\n";

foreach ($users as $user) {
    echo "👤 ID: {$user->id} | Nama: {$user->nama}\n";
    echo "   Tipe: {$user->tipe_nasabah}\n";
    echo "   Email: {$user->email}\n";
    echo "   Total Poin: {$user->total_poin} | Poin Tercatat: {$user->poin_tercatat}\n";

    // Check banking info
    $has_bank = !is_null($user->nama_bank);
    $has_no_rek = !is_null($user->nomor_rekening);
    $has_atas_nama = !is_null($user->atas_nama_rekening);

    if ($user->tipe_nasabah === 'konvensional') {
        // Konvensional should NOT have banking info
        if ($has_bank || $has_no_rek || $has_atas_nama) {
            echo "   ❌ ERROR: Konvensional user memiliki data banking!\n";
            echo "      - nama_bank: " . ($has_bank ? $user->nama_bank : "NULL (✓)") . "\n";
            echo "      - nomor_rekening: " . ($has_no_rek ? $user->nomor_rekening : "NULL (✓)") . "\n";
            echo "      - atas_nama_rekening: " . ($has_atas_nama ? $user->atas_nama_rekening : "NULL (✓)") . "\n";
        } else {
            echo "   ✅ Konvensional - NO banking info (benar)\n";
        }
    } else if ($user->tipe_nasabah === 'modern') {
        // Modern SHOULD have banking info
        if ($has_bank && $has_no_rek && $has_atas_nama) {
            echo "   ✅ Modern - HAS banking info:\n";
            echo "      - Bank: {$user->nama_bank}\n";
            echo "      - No Rek: {$user->nomor_rekening}\n";
            echo "      - Atas Nama: {$user->atas_nama_rekening}\n";
        } else {
            echo "   ❌ ERROR: Modern user KURANG data banking!\n";
            echo "      - nama_bank: " . ($has_bank ? $user->nama_bank : "NULL (❌)") . "\n";
            echo "      - nomor_rekening: " . ($has_no_rek ? $user->nomor_rekening : "NULL (❌)") . "\n";
            echo "      - atas_nama_rekening: " . ($has_atas_nama ? $user->atas_nama_rekening : "NULL (❌)") . "\n";
        }
    }
    echo "\n";
}

echo "========================================\n";
echo "SUMMARY\n";
echo "========================================\n\n";

$all_valid = true;

// Check konvensional
foreach ($konvensional as $user) {
    if (!is_null($user->nama_bank) || !is_null($user->nomor_rekening) || !is_null($user->atas_nama_rekening)) {
        $all_valid = false;
        break;
    }
}

// Check modern
foreach ($modern as $user) {
    if (is_null($user->nama_bank) || is_null($user->nomor_rekening) || is_null($user->atas_nama_rekening)) {
        $all_valid = false;
        break;
    }
}

if ($all_valid) {
    echo "✅ SEMUA DATA VALID!\n";
    echo "✅ Konvensional users: NO banking info\n";
    echo "✅ Modern users: HAS banking info\n";
} else {
    echo "❌ ADA DATA YANG INVALID!\n";
    echo "❌ Silakan jalankan: php artisan db:seed --class=UserSeeder\n";
}

echo "\n";
