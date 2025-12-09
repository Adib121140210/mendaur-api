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
     * Fix sessions table:
     * 1. Change user_id column type from bigint to int unsigned to match users.user_id
     * 2. Add foreign key constraint to sessions.user_id -> users.user_id
     */
    public function up(): void
    {
        if (Schema::hasTable('sessions')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Change column type to match users.user_id (int unsigned)
            DB::statement('ALTER TABLE `sessions` MODIFY `user_id` int unsigned NULL');
            
            // Add foreign key constraint
            try {
                Schema::table('sessions', function (Blueprint $table) {
                    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Log but continue - constraint might already exist
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sessions')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            try {
                Schema::table('sessions', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            } catch (\Exception $e) {
                // Ignore if constraint doesn't exist
            }
            
            // Revert to bigint unsigned
            DB::statement('ALTER TABLE `sessions` MODIFY `user_id` bigint unsigned NULL');
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
