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
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 1. users -> user_id
        DB::statement('ALTER TABLE users CHANGE COLUMN id user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 2. badges -> badge_id
        DB::statement('ALTER TABLE badges CHANGE COLUMN id badge_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 3. tabung_sampah -> tabung_sampah_id
        DB::statement('ALTER TABLE tabung_sampah CHANGE COLUMN id tabung_sampah_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 4. penukaran_produk -> penukaran_produk_id
        DB::statement('ALTER TABLE penukaran_produk CHANGE COLUMN id penukaran_produk_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 5. penarikan_tunai -> penarikan_tunai_id
        DB::statement('ALTER TABLE penarikan_tunai CHANGE COLUMN id penarikan_tunai_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 6. log_poin -> log_poin_id
        DB::statement('ALTER TABLE log_poin CHANGE COLUMN id log_poin_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 7. log_user_activity -> log_user_activity_id
        DB::statement('ALTER TABLE log_user_activity CHANGE COLUMN id log_user_activity_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 8. badge_progress -> badge_progress_id
        DB::statement('ALTER TABLE badge_progress CHANGE COLUMN id badge_progress_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 9. artikels -> artikel_id
        DB::statement('ALTER TABLE artikels CHANGE COLUMN id artikel_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 10. produks -> produk_id
        DB::statement('ALTER TABLE produks CHANGE COLUMN id produk_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 11. jadwal_penyetorans -> jadwal_penyetoran_id
        DB::statement('ALTER TABLE jadwal_penyetorans CHANGE COLUMN id jadwal_penyetoran_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 12. jenis_sampahs -> jenis_sampah_id
        DB::statement('ALTER TABLE jenis_sampahs CHANGE COLUMN id jenis_sampah_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 13. kategori_sampahs -> kategori_sampah_id
        DB::statement('ALTER TABLE kategori_sampahs CHANGE COLUMN id kategori_sampah_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 14. roles -> role_id
        DB::statement('ALTER TABLE roles CHANGE COLUMN id role_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 15. role_permissions -> role_permission_id
        DB::statement('ALTER TABLE role_permissions CHANGE COLUMN id role_permission_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 16. audit_logs -> audit_log_id
        DB::statement('ALTER TABLE audit_logs CHANGE COLUMN id audit_log_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // 17. notifikasis -> notifikasi_id
        DB::statement('ALTER TABLE notifikasis CHANGE COLUMN id notifikasi_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // Update foreign key columns to match new naming convention
        // tabung_sampah.user_id (already correct)
        // penukaran_produk.user_id (already correct)
        // penarikan_tunai.user_id (already correct)
        // log_poin.user_id (already correct)
        // log_user_activity.user_id (already correct)
        // badge_progress.user_id, badge_id (need to update badge_id)
        DB::statement('ALTER TABLE badge_progress CHANGE COLUMN badge_id badge_id INT NOT NULL');

        // user_badges needs to be updated
        if (Schema::hasTable('user_badges')) {
            DB::statement('ALTER TABLE user_badges CHANGE COLUMN user_id user_id INT NOT NULL');
            DB::statement('ALTER TABLE user_badges CHANGE COLUMN badge_id badge_id INT NOT NULL');
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Revert all changes back to 'id'
        $tables = [
            'users' => 'user_id',
            'badges' => 'badge_id',
            'tabung_sampah' => 'tabung_sampah_id',
            'penukaran_produk' => 'penukaran_produk_id',
            'penarikan_tunai' => 'penarikan_tunai_id',
            'log_poin' => 'log_poin_id',
            'log_user_activity' => 'log_user_activity_id',
            'badge_progress' => 'badge_progress_id',
            'artikels' => 'artikel_id',
            'produks' => 'produk_id',
            'jadwal_penyetorans' => 'jadwal_penyetoran_id',
            'jenis_sampahs' => 'jenis_sampah_id',
            'kategori_sampahs' => 'kategori_sampah_id',
            'roles' => 'role_id',
            'role_permissions' => 'role_permission_id',
            'audit_logs' => 'audit_log_id',
            'notifikasis' => 'notifikasi_id',
        ];

        foreach ($tables as $table => $column) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE $table CHANGE COLUMN $column id INT NOT NULL AUTO_INCREMENT PRIMARY KEY");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
