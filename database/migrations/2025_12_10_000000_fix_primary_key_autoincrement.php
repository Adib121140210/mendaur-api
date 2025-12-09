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
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Fix primary keys to be auto_increment
        // This is necessary because renaming didn't preserve the auto_increment attribute
        
        // For tables that should have auto_increment
        $tables = [
            'roles',
            'badges', 
            'produks',
            'jenis_sampah',
            'kategori_sampah',
            'jadwal_penyetorans',
            'role_permissions',
            'tabung_sampah',
            'penukaran_produk',
            'penarikan_tunai',
            'poin_transaksis',
            'log_aktivitas',
            'badge_progress',
            'artikels',
            'audit_logs',
            'notifikasi'
        ];

        // Get the primary key column name for each table
        $pkColumns = [
            'roles' => 'role_id',
            'badges' => 'badge_id',
            'produks' => 'produk_id',
            'jenis_sampah' => 'jenis_sampah_id',
            'kategori_sampah' => 'kategori_sampah_id',
            'jadwal_penyetorans' => 'jadwal_penyetoran_id',
            'role_permissions' => 'role_permission_id',
            'tabung_sampah' => 'tabung_sampah_id',
            'penukaran_produk' => 'penukaran_produk_id',
            'penarikan_tunai' => 'penarikan_tunai_id',
            'poin_transaksis' => 'poin_transaksi_id',
            'log_aktivitas' => 'log_user_activity_id',
            'badge_progress' => 'badge_progress_id',
            'artikels' => 'artikel_id',
            'audit_logs' => 'audit_log_id',
            'notifikasi' => 'notifikasi_id'
        ];

        foreach ($tables as $table) {
            $pkCol = $pkColumns[$table];
            try {
                // Use MODIFY to add AUTO_INCREMENT
                DB::statement("ALTER TABLE `$table` MODIFY `$pkCol` bigint unsigned NOT NULL AUTO_INCREMENT");
            } catch (\Exception $e) {
                \Log::warning("Could not modify $table.$pkCol: " . $e->getMessage());
            }
        }

        // Special case for users table (int unsigned instead of bigint)
        try {
            DB::statement("ALTER TABLE `users` MODIFY `user_id` int unsigned NOT NULL AUTO_INCREMENT");
        } catch (\Exception $e) {
            \Log::warning("Could not modify users.user_id: " . $e->getMessage());
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For rollback, we would remove auto_increment, but it's generally safe to leave it
        // This migration is non-destructive in reverse
    }
};
