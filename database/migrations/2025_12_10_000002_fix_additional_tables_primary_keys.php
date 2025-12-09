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
     * Fixes primary key naming for:
     * 1. personal_access_tokens: id -> personal_access_token_id
     * 2. user_badges: id -> user_badge_id
     * 3. sessions: user_id FK reference (already correct in users table)
     */
    public function up(): void
    {
        // 1. Fix personal_access_tokens table
        if (Schema::hasTable('personal_access_tokens')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            try {
                // Remove AUTO_INCREMENT before dropping PK
                DB::statement('ALTER TABLE `personal_access_tokens` MODIFY `id` bigint unsigned NOT NULL');
                DB::statement('ALTER TABLE `personal_access_tokens` DROP PRIMARY KEY');
                DB::statement('ALTER TABLE `personal_access_tokens` CHANGE `id` `personal_access_token_id` bigint unsigned NOT NULL');
                DB::statement('ALTER TABLE `personal_access_tokens` ADD PRIMARY KEY (`personal_access_token_id`), MODIFY `personal_access_token_id` bigint unsigned NOT NULL AUTO_INCREMENT');
            } catch (\Exception $e) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                throw $e;
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // 2. Fix user_badges table
        if (Schema::hasTable('user_badges')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            try {
                // Remove AUTO_INCREMENT before dropping PK
                DB::statement('ALTER TABLE `user_badges` MODIFY `id` bigint unsigned NOT NULL');
                DB::statement('ALTER TABLE `user_badges` DROP PRIMARY KEY');
                DB::statement('ALTER TABLE `user_badges` CHANGE `id` `user_badge_id` bigint unsigned NOT NULL');
                DB::statement('ALTER TABLE `user_badges` ADD PRIMARY KEY (`user_badge_id`), MODIFY `user_badge_id` bigint unsigned NOT NULL AUTO_INCREMENT');
            } catch (\Exception $e) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                throw $e;
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // 3. Verify sessions table has correct FK reference to users.user_id
        // The sessions table has a string 'id' as primary key, not a numeric id
        // FK reference to user_id is already correct in migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Reverse personal_access_tokens
        if (Schema::hasTable('personal_access_tokens')) {
            try {
                DB::statement('ALTER TABLE `personal_access_tokens` MODIFY `personal_access_token_id` bigint unsigned NOT NULL');
                DB::statement('ALTER TABLE `personal_access_tokens` DROP PRIMARY KEY');
                DB::statement('ALTER TABLE `personal_access_tokens` CHANGE `personal_access_token_id` `id` bigint unsigned NOT NULL');
                DB::statement('ALTER TABLE `personal_access_tokens` ADD PRIMARY KEY (`id`), MODIFY `id` bigint unsigned NOT NULL AUTO_INCREMENT');
            } catch (\Exception $e) {
                // Continue with other reversals
            }
        }

        // Reverse user_badges
        if (Schema::hasTable('user_badges')) {
            try {
                DB::statement('ALTER TABLE `user_badges` MODIFY `user_badge_id` bigint unsigned NOT NULL');
                DB::statement('ALTER TABLE `user_badges` DROP PRIMARY KEY');
                DB::statement('ALTER TABLE `user_badges` CHANGE `user_badge_id` `id` bigint unsigned NOT NULL');
                DB::statement('ALTER TABLE `user_badges` ADD PRIMARY KEY (`id`), MODIFY `id` bigint unsigned NOT NULL AUTO_INCREMENT');
            } catch (\Exception $e) {
                // Continue
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
