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
        // Disable foreign key checks to allow truncation of users table
        \DB::statement('SET SESSION FOREIGN_KEY_CHECKS = 0');
        \DB::statement('DELETE FROM users');
        \DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
        \DB::statement('SET SESSION FOREIGN_KEY_CHECKS = 1');

        // Get role IDs
        $adminRole = Role::where('nama_role', 'admin')->first();
        $superadminRole = Role::where('nama_role', 'superadmin')->first();
        $nasabahRole = Role::where('nama_role', 'nasabah')->first();

        DB::table('users')->insert([
            // ========== ADMIN & SUPERADMIN (For Testing) ==========
            [
                'nama' => 'Admin Testing',
                'email' => 'admin@test.com',
                'password' => Hash::make('admin123'),
                'no_hp' => '089999999999',
                'alamat' => 'Jl. Admin No. 999, Metro Admin',
                'foto_profil' => null,
                'total_poin' => 0,
                'poin_tercatat' => 0,
                'total_setor_sampah' => 0,
                'level' => 'admin',  // Lowercase untuk konsistensi
                'role_id' => $adminRole ? $adminRole->role_id : null,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Superadmin Testing',
                'email' => 'superadmin@test.com',
                'password' => Hash::make('superadmin123'),
                'no_hp' => '088888888888',
                'alamat' => 'Jl. Superadmin No. 888, Metro Superadmin',
                'foto_profil' => null,
                'total_poin' => 0,
                'poin_tercatat' => 0,
                'total_setor_sampah' => 0,
                'level' => 'superadmin',  // Lowercase untuk konsistensi
                'role_id' => $superadminRole ? $superadminRole->role_id : null,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ========== KONVENSIONAL USERS ==========
            // Konvensional users: NO banking info (they use poin directly)
            [
                'nama' => 'Adib Surya',
                'email' => 'adib@example.com',
                'password' => Hash::make('password'),
                'no_hp' => '081234567890',
                'alamat' => 'Jl. Gatot Subroto No. 123, Metro Barat',
                'foto_profil' => null,
                'total_poin' => 150,
                'poin_tercatat' => 150,
                'total_setor_sampah' => 5,
                'level' => 'bronze',  // Lowercase untuk konsistensi
                'role_id' => $nasabahRole ? $nasabahRole->role_id : null,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Siti Aminah',
                'email' => 'siti@example.com',
                'password' => Hash::make('password'),
                'no_hp' => '082345678901',
                'alamat' => 'Jl. Diponegoro No. 456, Metro Timur',
                'foto_profil' => null,
                'total_poin' => 2000,
                'poin_tercatat' => 2000,
                'total_setor_sampah' => 12,
                'level' => 'silver',  // Lowercase untuk konsistensi
                'role_id' => $nasabahRole ? $nasabahRole->role_id : null,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'password' => Hash::make('password'),
                'no_hp' => '083456789012',
                'alamat' => 'Jl. Sudirman No. 789, Metro Selatan',
                'foto_profil' => null,
                'total_poin' => 50,
                'poin_tercatat' => 50,
                'total_setor_sampah' => 2,
                'level' => 'bronze',  // Lowercase untuk konsistensi (Pemula -> bronze)
                'role_id' => $nasabahRole ? $nasabahRole->role_id : null,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ========== MODERN USERS ==========
            // Modern users: WITH banking info (they need withdrawal via bank transfer)
            [
                'nama' => 'Reno Wijaya',
                'email' => 'reno@example.com',
                'password' => Hash::make('password'),
                'no_hp' => '085555666777',
                'alamat' => 'Jl. Ahmad Yani No. 321, Metro Utara',
                'foto_profil' => null,
                'total_poin' => 0,
                'poin_tercatat' => 500,
                'total_setor_sampah' => 8,
                'level' => 'gold',  // Lowercase untuk konsistensi
                'role_id' => $nasabahRole ? $nasabahRole->role_id : null,
                'status' => 'active',
                'tipe_nasabah' => 'modern',
                'nama_bank' => 'BNI',
                'nomor_rekening' => '1234567890',
                'atas_nama_rekening' => 'Reno Wijaya',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Rina Kusuma',
                'email' => 'rina@example.com',
                'password' => Hash::make('password'),
                'no_hp' => '087777888999',
                'alamat' => 'Jl. Merdeka No. 654, Metro Pusat',
                'foto_profil' => null,
                'total_poin' => 0,
                'poin_tercatat' => 1200,
                'total_setor_sampah' => 15,
                'level' => 'gold',  // Lowercase (Platinum -> gold untuk konsistensi)
                'role_id' => $nasabahRole ? $nasabahRole->role_id : null,
                'status' => 'active',
                'tipe_nasabah' => 'modern',
                'nama_bank' => 'MANDIRI',
                'nomor_rekening' => '9876543210',
                'atas_nama_rekening' => 'Rina Kusuma',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ========== TEST USER (Konvensional) ==========
            [
                'nama' => 'test',
                'email' => 'test@test.com',
                'password' => Hash::make('test'),
                'no_hp' => '121231231231',
                'alamat' => 'Jl. Gatot Subroto No. 123, Metro Barat',
                'foto_profil' => null,
                'total_poin' => 1000,
                'poin_tercatat' => 1000,
                'total_setor_sampah' => 2000,
                'level' => 'bronze',  // Lowercase untuk konsistensi
                'role_id' => $nasabahRole ? $nasabahRole->role_id : null,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ========== TEST USERS FOR CRUD OPERATIONS ==========
            // These users are created to test CRUD operations (status updates, role changes, etc.)
            [
                'nama' => 'Inactive User Test',
                'email' => 'inactive@test.com',
                'password' => Hash::make('password'),
                'no_hp' => '081111111111',
                'alamat' => 'Jl. Test No. 111, Metro Test',
                'foto_profil' => null,
                'total_poin' => 500,
                'poin_tercatat' => 500,
                'total_setor_sampah' => 5,
                'level' => 'bronze',  // Lowercase untuk konsistensi
                'role_id' => $nasabahRole ? $nasabahRole->role_id : null,
                'status' => 'inactive',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Suspended User Test',
                'email' => 'suspended@test.com',
                'password' => Hash::make('password'),
                'no_hp' => '082222222222',
                'alamat' => 'Jl. Test No. 222, Metro Test',
                'foto_profil' => null,
                'total_poin' => 300,
                'poin_tercatat' => 300,
                'total_setor_sampah' => 3,
                'level' => 'bronze',  // Lowercase untuk konsistensi (Pemula -> bronze)
                'role_id' => $nasabahRole ? $nasabahRole->role_id : null,
                'status' => 'suspended',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Premium User Test',
                'email' => 'premium@test.com',
                'password' => Hash::make('password'),
                'no_hp' => '083333333333',
                'alamat' => 'Jl. Test No. 333, Metro Test',
                'foto_profil' => null,
                'total_poin' => 5000,
                'poin_tercatat' => 5000,
                'total_setor_sampah' => 50,
                'level' => 'gold',  // Lowercase untuk konsistensi (Gold -> gold)
                'role_id' => $nasabahRole ? $nasabahRole->role_id : null,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Corporate User Test',
                'email' => 'corporate@test.com',
                'password' => Hash::make('password'),
                'no_hp' => '084444444444',
                'alamat' => 'Jl. Test No. 444, Metro Test',
                'foto_profil' => null,
                'total_poin' => 10000,
                'poin_tercatat' => 10000,
                'total_setor_sampah' => 100,
                'level' => 'gold',  // Lowercase untuk konsistensi (Platinum -> gold)
                'role_id' => $nasabahRole ? $nasabahRole->role_id : null,
                'status' => 'active',
                'tipe_nasabah' => 'modern',
                'nama_bank' => 'BCA',
                'nomor_rekening' => '1111111111',
                'atas_nama_rekening' => 'Corporate User',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Staff User Test',
                'email' => 'staff@test.com',
                'password' => Hash::make('password'),
                'no_hp' => '085555555555',
                'alamat' => 'Jl. Test No. 555, Metro Test',
                'foto_profil' => null,
                'total_poin' => 0,
                'poin_tercatat' => 0,
                'total_setor_sampah' => 0,
                'level' => 'bronze',  // Lowercase untuk konsistensi (Staff -> bronze)
                'role_id' => $nasabahRole ? $nasabahRole->role_id : null,
                'status' => 'active',
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,
                'nomor_rekening' => null,
                'atas_nama_rekening' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
