#!/usr/bin/env php
<?php

/**
 * Verifikasi Data Seed User
 * Memastikan:
 * - Konvensional users: NO banking info (nama_bank, nomor_rekening, atas_nama_rekening = NULL)
 * - Modern users: HAS banking info
 */

use App\Models\User;

// Bootstrap Laravel
$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n========================================\n";
echo "VERIFIKASI DATA SEED USER\n";
echo "========================================\n\n";

try {
    // Get all users
    $users = User::all();

    if ($users->isEmpty()) {
        echo "❌ Tidak ada user dalam database!\n";
        echo "   Jalankan: php artisan db:seed --class=UserSeeder\n\n";
        exit(1);
    }

    echo "Total Users: " . $users->count() . "\n";

    $konvensional = $users->where('tipe_nasabah', 'konvensional');
    $modern = $users->where('tipe_nasabah', 'modern');

    echo "Konvensional: " . $konvensional->count() . " users\n";
    echo "Modern: " . $modern->count() . " users\n\n";

    $issues = [];

    echo "========== CEK KONVENSIONAL USERS ==========\n\n";
    foreach ($konvensional as $user) {
        $status = "✅";
        $issues_user = [];

        if (!is_null($user->nama_bank)) {
            $status = "❌";
            $issues_user[] = "nama_bank = '{$user->nama_bank}' (harus NULL)";
        }
        if (!is_null($user->nomor_rekening)) {
            $status = "❌";
            $issues_user[] = "nomor_rekening = '{$user->nomor_rekening}' (harus NULL)";
        }
        if (!is_null($user->atas_nama_rekening)) {
            $status = "❌";
            $issues_user[] = "atas_nama_rekening = '{$user->atas_nama_rekening}' (harus NULL)";
        }

        echo "{$status} ID: {$user->id} | {$user->nama} ({$user->email})\n";
        if ($issues_user) {
            foreach ($issues_user as $issue) {
                echo "   ❌ {$issue}\n";
                $issues[] = "ID {$user->id} ({$user->nama}): {$issue}";
            }
        } else {
            echo "   ✅ NO banking info (benar)\n";
        }
    }

    echo "\n========== CEK MODERN USERS ==========\n\n";
    foreach ($modern as $user) {
        $status = "✅";
        $issues_user = [];

        if (is_null($user->nama_bank)) {
            $status = "❌";
            $issues_user[] = "nama_bank = NULL (harus ada bank name)";
        }
        if (is_null($user->nomor_rekening)) {
            $status = "❌";
            $issues_user[] = "nomor_rekening = NULL (harus ada account number)";
        }
        if (is_null($user->atas_nama_rekening)) {
            $status = "❌";
            $issues_user[] = "atas_nama_rekening = NULL (harus ada account name)";
        }

        echo "{$status} ID: {$user->id} | {$user->nama} ({$user->email})\n";
        if ($issues_user) {
            foreach ($issues_user as $issue) {
                echo "   ❌ {$issue}\n";
                $issues[] = "ID {$user->id} ({$user->nama}): {$issue}";
            }
        } else {
            echo "   ✅ HAS banking info:\n";
            echo "      - Bank: {$user->nama_bank}\n";
            echo "      - No Rek: {$user->nomor_rekening}\n";
            echo "      - Atas Nama: {$user->atas_nama_rekening}\n";
        }
    }

    echo "\n========================================\n";
    echo "HASIL\n";
    echo "========================================\n\n";

    if (empty($issues)) {
        echo "✅ SEMUA DATA VALID!\n\n";
        echo "Summary:\n";
        echo "  ✅ Konvensional users ({$konvensional->count()}): NO banking info\n";
        echo "  ✅ Modern users ({$modern->count()}): HAS banking info\n";
        echo "\n✅ Data seed sudah benar sesuai dual-nasabah logic!\n\n";
    } else {
        echo "❌ DITEMUKAN " . count($issues) . " ISSUES:\n\n";
        foreach ($issues as $issue) {
            echo "  ❌ {$issue}\n";
        }
        echo "\nSilakan jalankan:\n";
        echo "  php artisan db:seed --class=UserSeeder\n\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}
