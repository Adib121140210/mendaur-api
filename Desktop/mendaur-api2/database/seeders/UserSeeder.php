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
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('users')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
                'level' => 'Admin',
                'role_id' => $adminRole ? $adminRole->id : null,
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
                'level' => 'Superadmin',
                'role_id' => $superadminRole ? $superadminRole->id : null,
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
                'level' => 'Bronze',
                'role_id' => $nasabahRole ? $nasabahRole->id : null,
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,  // Konvensional: NO bank info
                'nomor_rekening' => null,  // Konvensional: NO account number
                'atas_nama_rekening' => null,  // Konvensional: NO account name
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
                'level' => 'Silver',
                'role_id' => $nasabahRole ? $nasabahRole->id : null,
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,  // Konvensional: NO bank info
                'nomor_rekening' => null,  // Konvensional: NO account number
                'atas_nama_rekening' => null,  // Konvensional: NO account name
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
                'level' => 'Pemula',
                'role_id' => $nasabahRole ? $nasabahRole->id : null,
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,  // Konvensional: NO bank info
                'nomor_rekening' => null,  // Konvensional: NO account number
                'atas_nama_rekening' => null,  // Konvensional: NO account name
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
                'total_poin' => 0,  // Modern: blocked from direct poin usage
                'poin_tercatat' => 500,  // Modern: records poin for audit only
                'total_setor_sampah' => 8,
                'level' => 'Gold',
                'role_id' => $nasabahRole ? $nasabahRole->id : null,
                'tipe_nasabah' => 'modern',
                'nama_bank' => 'BNI',  // Modern: HAS bank info
                'nomor_rekening' => '1234567890',  // Modern: HAS account number
                'atas_nama_rekening' => 'Reno Wijaya',  // Modern: HAS account name
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
                'total_poin' => 0,  // Modern: blocked from direct poin usage
                'poin_tercatat' => 1200,  // Modern: records poin for audit only
                'total_setor_sampah' => 15,
                'level' => 'Platinum',
                'role_id' => $nasabahRole ? $nasabahRole->id : null,
                'tipe_nasabah' => 'modern',
                'nama_bank' => 'MANDIRI',  // Modern: HAS bank info
                'nomor_rekening' => '9876543210',  // Modern: HAS account number
                'atas_nama_rekening' => 'Rina Kusuma',  // Modern: HAS account name
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
                'level' => 'Bronze',
                'role_id' => $nasabahRole ? $nasabahRole->id : null,
                'tipe_nasabah' => 'konvensional',
                'nama_bank' => null,  // Konvensional: NO bank info
                'nomor_rekening' => null,  // Konvensional: NO account number
                'atas_nama_rekening' => null,  // Konvensional: NO account name
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
