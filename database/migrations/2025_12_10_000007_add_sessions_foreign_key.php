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
     * Add foreign key to sessions.user_id -> users.user_id after user table PK has been renamed
     */
    public function up(): void
    {
        if (!Schema::hasTable('sessions') || !Schema::hasTable('users')) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            DB::statement('ALTER TABLE `sessions` ADD CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE');
        } catch (\Exception $e) {
            // FK might already exist, that's fine
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sessions')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            try {
                DB::statement('ALTER TABLE `sessions` DROP FOREIGN KEY `sessions_user_id_foreign`');
            } catch (\Exception $e) {
                // FK might not exist
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
