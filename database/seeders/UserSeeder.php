<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET SESSION FOREIGN_KEY_CHECKS = 0');
        DB::table('user_badges')->truncate();
        DB::table('badge_progress')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET SESSION FOREIGN_KEY_CHECKS = 1');

        $adminRole = Role::where('nama_role', 'admin')->first();
        $superadminRole = Role::where('nama_role', 'superadmin')->first();
        $nasabahRole = Role::where('nama_role', 'nasabah')->first();

        DB::table('users')->insert([
            // Admin
            [
                'nama' => 'Admin Mendaur',
                'email' => 'admin@mendaur.id',
                'password' => Hash::make('password123'),
                'no_hp' => '081200000001',
                'alamat' => 'Kantor Mendaur, Jl. Daur Ulang No. 1, Jakarta Pusat',
                'foto_profil' => null,
                'display_poin' => 0,
                'actual_poin' => 0,
                'poin_tercatat' => 0,
                'total_setor_sampah' => 0,
                'level' => 'admin',
                'role_id' => $adminRole?->role_id,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now()->subMonths(6),
                'updated_at' => now(),
            ],

            // Superadmin
            [
                'nama' => 'Superadmin Mendaur',
                'email' => 'superadmin@mendaur.id',
                'password' => Hash::make('password123'),
                'no_hp' => '081200000002',
                'alamat' => 'Kantor Mendaur, Jl. Daur Ulang No. 1, Jakarta Pusat',
                'foto_profil' => null,
                'display_poin' => 0,
                'actual_poin' => 0,
                'poin_tercatat' => 0,
                'total_setor_sampah' => 0,
                'level' => 'superadmin',
                'role_id' => $superadminRole?->role_id,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now()->subMonths(6),
                'updated_at' => now(),
            ],

            // Nasabah 1 - Gold
            [
                'nama' => 'Rina Kusuma',
                'email' => 'rina@gmail.com',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567001',
                'alamat' => 'Jl. Kebayoran Baru No. 45, Jakarta Selatan',
                'foto_profil' => null,
                'display_poin' => 8500,
                'actual_poin' => 8500,
                'poin_tercatat' => 12500,
                'total_setor_sampah' => 30,
                'level' => 'gold',
                'role_id' => $nasabahRole?->role_id,
                'status' => 'active',
                'tipe_nasabah' => 'modern',
                'nama_bank' => 'BCA',
                'nomor_rekening' => '1234567890',
                'atas_nama_rekening' => 'Rina Kusuma',
                'created_at' => now()->subMonths(5),
                'updated_at' => now(),
            ],

            // Nasabah 2 - Gold
            [
                'nama' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@gmail.com',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567002',
                'alamat' => 'Jl. Kemang Raya No. 78, Jakarta Selatan',
                'foto_profil' => null,
                'display_poin' => 5200,
                'actual_poin' => 5200,
                'poin_tercatat' => 7800,
                'total_setor_sampah' => 25,
                'level' => 'gold',
                'role_id' => $nasabahRole?->role_id,
                'status' => 'active',
                'tipe_nasabah' => 'modern',
                'nama_bank' => 'Mandiri',
                'nomor_rekening' => '9876543210',
                'atas_nama_rekening' => 'Ahmad Fauzi',
                'created_at' => now()->subMonths(4),
                'updated_at' => now(),
            ],

            // Nasabah 3 - Silver
            [
                'nama' => 'Dewi Sartika',
                'email' => 'dewi.sartika@gmail.com',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567003',
                'alamat' => 'Jl. Cikini Raya No. 23, Jakarta Pusat',
                'foto_profil' => null,
                'display_poin' => 2800,
                'actual_poin' => 2800,
                'poin_tercatat' => 3500,
                'total_setor_sampah' => 15,
                'level' => 'silver',
                'role_id' => $nasabahRole?->role_id,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now()->subMonths(3),
                'updated_at' => now(),
            ],

            // Nasabah 4 - Bronze
            [
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@gmail.com',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567004',
                'alamat' => 'Jl. Tebet Barat No. 12, Jakarta Selatan',
                'foto_profil' => null,
                'display_poin' => 650,
                'actual_poin' => 650,
                'poin_tercatat' => 850,
                'total_setor_sampah' => 8,
                'level' => 'bronze',
                'role_id' => $nasabahRole?->role_id,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now()->subMonths(2),
                'updated_at' => now(),
            ],

            // Nasabah 5 - Demo Account
            [
                'nama' => 'Demo Nasabah',
                'email' => 'demo@mendaur.id',
                'password' => Hash::make('demo123'),
                'no_hp' => '081200000007',
                'alamat' => 'Jl. Percobaan No. 123, Jakarta',
                'foto_profil' => null,
                'display_poin' => 150,
                'actual_poin' => 150,
                'poin_tercatat' => 200,
                'total_setor_sampah' => 3,
                'level' => 'bronze',
                'role_id' => $nasabahRole?->role_id,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now()->subWeeks(2),
                'updated_at' => now(),
            ],
        ]);
    }
}
