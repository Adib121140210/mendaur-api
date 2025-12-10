<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename jadwal_id to jadwal_penyetoran_id in tabung_sampah table
     * This ensures consistency with the renamed primary key in jadwal_penyetorans table
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Drop the old foreign key
        try {
            Schema::table('tabung_sampah', function (Blueprint $table) {
                $table->dropForeign('tabung_sampah_jadwal_id_foreign');
            });
        } catch (\Exception $e) {
            // Foreign key might not exist
        }

        // Rename the column
        DB::statement('ALTER TABLE `tabung_sampah` CHANGE COLUMN `jadwal_id` `jadwal_penyetoran_id` BIGINT UNSIGNED NOT NULL');

        // Add the correct foreign key
        try {
            Schema::table('tabung_sampah', function (Blueprint $table) {
                $table->foreign('jadwal_penyetoran_id')
                    ->references('jadwal_penyetoran_id')
                    ->on('jadwal_penyetorans')
                    ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Try direct statement if blueprint fails
            DB::statement('ALTER TABLE `tabung_sampah` ADD CONSTRAINT `tabung_sampah_jadwal_penyetoran_id_foreign` FOREIGN KEY (`jadwal_penyetoran_id`) REFERENCES `jadwal_penyetorans` (`jadwal_penyetoran_id`) ON DELETE CASCADE');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Drop the new foreign key
        try {
            Schema::table('tabung_sampah', function (Blueprint $table) {
                $table->dropForeign('tabung_sampah_jadwal_penyetoran_id_foreign');
            });
        } catch (\Exception $e) {
            // Foreign key might not exist
        }

        // Rename the column back
        DB::statement('ALTER TABLE `tabung_sampah` CHANGE COLUMN `jadwal_penyetoran_id` `jadwal_id` BIGINT UNSIGNED NOT NULL');

        // Add the old foreign key back
        try {
            Schema::table('tabung_sampah', function (Blueprint $table) {
                $table->foreign('jadwal_id')
                    ->references('jadwal_penyetoran_id')
                    ->on('jadwal_penyetorans')
                    ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Try direct statement if blueprint fails
            DB::statement('ALTER TABLE `tabung_sampah` ADD CONSTRAINT `tabung_sampah_jadwal_id_foreign` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_penyetorans` (`jadwal_penyetoran_id`) ON DELETE CASCADE');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
