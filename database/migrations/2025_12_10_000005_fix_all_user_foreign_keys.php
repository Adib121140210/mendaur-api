<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix all foreign key constraints that reference users.id
     * They should reference users.user_id instead
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        $constraints = [
            'badge_progress' => 'badge_progress_user_id_foreign',
            'log_aktivitas' => 'log_aktivitas_user_id_foreign',
            'notifikasi' => 'notifikasi_user_id_foreign',
            'penarikan_tunai' => ['penarikan_tunai_user_id_foreign', 'penarikan_tunai_processed_by_foreign'],
            'penukaran_produk' => 'penukaran_produk_user_id_foreign',
            'poin_transaksis' => 'poin_transaksis_user_id_foreign',
            'tabung_sampah' => 'tabung_sampah_user_id_foreign',
        ];
        
        foreach ($constraints as $table => $fkNames) {
            $fkArray = is_array($fkNames) ? $fkNames : [$fkNames];
            
            foreach ($fkArray as $fkName) {
                try {
                    // Drop old FK
                    DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
                } catch (\Exception $e) {
                    // FK might not exist with this exact name, try to find it
                }
            }
            
            // Recreate FKs with correct reference
            if ($table === 'penarikan_tunai') {
                // This table has two FKs to users
                try {
                    DB::statement('ALTER TABLE `penarikan_tunai` ADD CONSTRAINT `penarikan_tunai_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE');
                } catch (\Exception $e) {}
                
                try {
                    DB::statement('ALTER TABLE `penarikan_tunai` ADD CONSTRAINT `penarikan_tunai_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL');
                } catch (\Exception $e) {}
            } else {
                // All other tables have single user_id FK
                try {
                    DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkNames}_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE");
                } catch (\Exception $e) {}
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        $constraints = [
            ['table' => 'badge_progress', 'fk' => 'badge_progress_user_id_foreign'],
            ['table' => 'log_aktivitas', 'fk' => 'log_aktivitas_user_id_foreign'],
            ['table' => 'notifikasi', 'fk' => 'notifikasi_user_id_foreign'],
            ['table' => 'penarikan_tunai', 'fk' => 'penarikan_tunai_user_id_foreign'],
            ['table' => 'penarikan_tunai', 'fk' => 'penarikan_tunai_processed_by_foreign'],
            ['table' => 'penukaran_produk', 'fk' => 'penukaran_produk_user_id_foreign'],
            ['table' => 'poin_transaksis', 'fk' => 'poin_transaksis_user_id_foreign'],
            ['table' => 'tabung_sampah', 'fk' => 'tabung_sampah_user_id_foreign'],
        ];
        
        foreach ($constraints as $constraint) {
            try {
                DB::statement("ALTER TABLE `{$constraint['table']}` DROP FOREIGN KEY `{$constraint['fk']}`");
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        // Recreate with old references (users.id)
        try {
            DB::statement('ALTER TABLE `badge_progress` ADD CONSTRAINT `badge_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
        } catch (\Exception $e) {}
        
        try {
            DB::statement('ALTER TABLE `log_aktivitas` ADD CONSTRAINT `log_aktivitas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
        } catch (\Exception $e) {}
        
        try {
            DB::statement('ALTER TABLE `notifikasi` ADD CONSTRAINT `notifikasi_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
        } catch (\Exception $e) {}
        
        try {
            DB::statement('ALTER TABLE `penarikan_tunai` ADD CONSTRAINT `penarikan_tunai_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
        } catch (\Exception $e) {}
        
        try {
            DB::statement('ALTER TABLE `penarikan_tunai` ADD CONSTRAINT `penarikan_tunai_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL');
        } catch (\Exception $e) {}
        
        try {
            DB::statement('ALTER TABLE `penukaran_produk` ADD CONSTRAINT `penukaran_produk_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
        } catch (\Exception $e) {}
        
        try {
            DB::statement('ALTER TABLE `poin_transaksis` ADD CONSTRAINT `poin_transaksis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
        } catch (\Exception $e) {}
        
        try {
            DB::statement('ALTER TABLE `tabung_sampah` ADD CONSTRAINT `tabung_sampah_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
        } catch (\Exception $e) {}
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
