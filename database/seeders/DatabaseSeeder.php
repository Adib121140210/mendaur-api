<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // RBAC System - Must run first as other seeders may depend on roles
            RolePermissionSeeder::class,

            UserSeeder::class,
            KategoriSampahSeeder::class,  // ⭐ Must run BEFORE JenisSampahSeeder (FK constraint)
            JenisSampahSeeder::class,
            KategoriTransaksiSeeder::class,
            JadwalPenyetoranSeeder::class,
            TabungSampahSeeder::class,
            ProdukSeeder::class,
            BadgeSeeder::class,
            ArtikelSeeder::class, // Add artikel seed data
            LogAktivitasSeeder::class,
            BadgeProgressSeeder::class, // Initialize badge progress for all users
            // TransaksiSeeder::class, // Comment out if not updated yet
        ]);
    }
}
