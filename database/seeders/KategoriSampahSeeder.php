<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSampahSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'nama_kategori' => 'Plastik',
                'deskripsi' => 'Berbagai jenis sampah plastik yang dapat didaur ulang',
                'icon' => '♻️',
                'warna' => '#2196F3',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nama_kategori' => 'Kertas',
                'deskripsi' => 'Sampah kertas dan turunannya',
                'icon' => '📄',
                'warna' => '#FF9800',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nama_kategori' => 'Logam',
                'deskripsi' => 'Sampah logam dan komponen metal',
                'icon' => '🔩',
                'warna' => '#9E9E9E',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nama_kategori' => 'Kaca',
                'deskripsi' => 'Sampah kaca dan material kaca',
                'icon' => '🍾',
                'warna' => '#00BCD4',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'nama_kategori' => 'Elektronik',
                'deskripsi' => 'Sampah elektronik dan komponen elektronik',
                'icon' => '🔌',
                'warna' => '#4CAF50',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('kategori_sampah')->insert($categories);
    }
}
