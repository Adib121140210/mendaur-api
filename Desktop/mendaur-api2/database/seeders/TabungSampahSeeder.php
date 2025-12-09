<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TabungSampahSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tabung_sampah')->insert([
            [
                'user_id' => 1,
                'jadwal_id' => 1,
                'nama_lengkap' => 'Adib Surya',
                'no_hp' => '081234567890',
                'titik_lokasi' => 'Jl. Gatot Subroto No. 123, Metro Barat',
                'jenis_sampah' => 'Plastik',
                'berat_kg' => 5.5,
                'foto_sampah' => 'uploads/sampah_1.jpg',
                'status' => 'approved',
                'poin_didapat' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'jadwal_id' => 2,
                'nama_lengkap' => 'Siti Aminah',
                'no_hp' => '082345678901',
                'titik_lokasi' => 'Jl. Diponegoro No. 456, Metro Timur',
                'jenis_sampah' => 'Kertas',
                'berat_kg' => 8.0,
                'foto_sampah' => 'uploads/sampah_2.jpg',
                'status' => 'approved',
                'poin_didapat' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'jadwal_id' => 1,
                'nama_lengkap' => 'Budi Santoso',
                'no_hp' => '083456789012',
                'titik_lokasi' => 'Jl. Sudirman No. 789, Metro Selatan',
                'jenis_sampah' => 'Logam',
                'berat_kg' => 3.2,
                'foto_sampah' => 'uploads/sampah_3.jpg',
                'status' => 'pending',
                'poin_didapat' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
