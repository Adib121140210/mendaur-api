<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Modify sessions table to explicitly use foreign key to users.user_id
     * This is needed because the initial migration uses foreignId() which assumes 'id'
     */
    public function up(): void
    {
        if (!Schema::hasTable('sessions') || !Schema::hasTable('users')) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            // Check if FK already exists and is correct
            $existingFK = DB::select('
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = "sessions"
                AND COLUMN_NAME = "user_id"
                AND REFERENCED_TABLE_NAME = "users"
                AND REFERENCED_COLUMN_NAME = "user_id"
            ');

            if (empty($existingFK)) {
                // Drop old FK if exists with wrong reference
                $oldFK = DB::select('
                    SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = "sessions"
                    AND COLUMN_NAME = "user_id"
                    AND REFERENCED_TABLE_NAME = "users"
                ');
                
                foreach ($oldFK as $fk) {
                    try {
                        DB::statement('ALTER TABLE `sessions` DROP FOREIGN KEY `' . $fk->CONSTRAINT_NAME . '`');
                    } catch (\Exception $e) {
                        // FK might not exist
                    }
                }
                
                // Create new FK with correct reference
                try {
                    DB::statement('ALTER TABLE `sessions` ADD CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE');
                } catch (\Exception $e) {
                    // FK might already exist with correct name
                }
            }
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw $e;
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reliably reverse as we don't know if users.id still exists
        // This migration should not be reversed in normal circumstances
    }
};
