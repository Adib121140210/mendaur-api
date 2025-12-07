<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalPenyetoranSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('jadwal_penyetorans')->insert([
            [
                'tanggal' => '2025-11-15',
                'waktu_mulai' => '08:00:00',
                'waktu_selesai' => '10:00:00',
                'lokasi' => 'TPS 3R Metro Barat',
                'kapasitas' => 50,
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tanggal' => '2025-11-15',
                'waktu_mulai' => '14:00:00',
                'waktu_selesai' => '16:00:00',
                'lokasi' => 'Bank Sampah Induk Nusa',
                'kapasitas' => 30,
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tanggal' => '2025-11-16',
                'waktu_mulai' => '09:00:00',
                'waktu_selesai' => '11:00:00',
                'lokasi' => 'TPS 3R Metro Selatan',
                'kapasitas' => 40,
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
