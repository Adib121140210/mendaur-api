<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing categories with correct colors
        DB::table('kategori_sampah')->where('kategori_sampah_id', 1)->update([
            'warna' => '#2196F3', // Light Blue for Plastik
        ]);

        DB::table('kategori_sampah')->where('kategori_sampah_id', 2)->update([
            'warna' => '#8B4513', // Saddle Brown for Kertas
        ]);

        DB::table('kategori_sampah')->where('kategori_sampah_id', 3)->update([
            'warna' => '#A9A9A9', // Dark Gray for Logam
        ]);

        DB::table('kategori_sampah')->where('kategori_sampah_id', 4)->update([
            'warna' => '#00BCD4', // Cyan for Kaca
        ]);

        DB::table('kategori_sampah')->where('kategori_sampah_id', 5)->update([
            'warna' => '#FF9800', // Deep Orange for Elektronik
        ]);

        // Insert new categories
        $newCategories = [
            [
                'kategori_sampah_id' => 6,
                'nama_kategori' => 'Tekstil',
                'deskripsi' => 'Sampah tekstil dan bahan pakaian',
                'icon' => '👕',
                'warna' => '#E91E63', // Pink for Tekstil
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kategori_sampah_id' => 7,
                'nama_kategori' => 'Pecah Belah',
                'deskripsi' => 'Barang pecah belah dan item yang rusak',
                'icon' => '🪟',
                'warna' => '#00BCD4', // Cyan for Pecah Belah
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kategori_sampah_id' => 8,
                'nama_kategori' => 'Lainnya',
                'deskripsi' => 'Sampah campuran dan kategori lainnya',
                'icon' => '🗑️',
                'warna' => '#607D8B', // Blue Gray for Lainnya
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('kategori_sampah')->insert($newCategories);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove new categories
        DB::table('kategori_sampah')->whereIn('kategori_sampah_id', [6, 7, 8])->delete();

        // Revert color changes
        DB::table('kategori_sampah')->where('kategori_sampah_id', 2)->update([
            'warna' => '#FF9800',
        ]);

        DB::table('kategori_sampah')->where('kategori_sampah_id', 3)->update([
            'warna' => '#9E9E9E',
        ]);

        DB::table('kategori_sampah')->where('kategori_sampah_id', 5)->update([
            'warna' => '#4CAF50',
        ]);
    }
};
