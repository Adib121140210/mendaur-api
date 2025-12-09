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
        // Get list of foreign keys first
        $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME='audit_logs' AND COLUMN_NAME='admin_id'");
        
        foreach ($constraints as $constraint) {
            try {
                DB::statement("ALTER TABLE `audit_logs` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Ignore if constraint doesn't exist
            }
        }

        // Disable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Step 1: Remove AUTO_INCREMENT dari id column
        DB::statement('ALTER TABLE `users` MODIFY `id` int unsigned NOT NULL');

        // Step 2: Drop the primary key
        DB::statement('ALTER TABLE `users` DROP PRIMARY KEY');

        // Step 3: Rename id ke user_id
        DB::statement('ALTER TABLE `users` CHANGE `id` `user_id` int unsigned NOT NULL');

        // Step 4: Add primary key dan auto_increment kembali
        DB::statement('ALTER TABLE `users` ADD PRIMARY KEY (`user_id`), MODIFY `user_id` int unsigned NOT NULL AUTO_INCREMENT');

        // Enable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Recreate foreign key for audit_logs
        try {
            DB::statement('ALTER TABLE `audit_logs` ADD CONSTRAINT `audit_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`)');
        } catch (\Exception $e) {
            // If constraint already exists, that's fine
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FK
        try {
            DB::statement('ALTER TABLE `audit_logs` DROP FOREIGN KEY `audit_logs_admin_id_foreign`');
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Remove AUTO_INCREMENT
        DB::statement('ALTER TABLE `users` MODIFY `user_id` int unsigned NOT NULL');

        // Drop PK
        DB::statement('ALTER TABLE `users` DROP PRIMARY KEY');

        // Rename back
        DB::statement('ALTER TABLE `users` CHANGE `user_id` `id` int unsigned NOT NULL');

        // Recreate PK dengan AUTO_INCREMENT
        DB::statement('ALTER TABLE `users` ADD PRIMARY KEY (`id`), MODIFY `id` int unsigned NOT NULL AUTO_INCREMENT');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Recreate FK with old column name
        try {
            DB::statement('ALTER TABLE `audit_logs` ADD CONSTRAINT `audit_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`)');
        } catch (\Exception $e) {
            // Ignore if already exists
        }
    }
};
