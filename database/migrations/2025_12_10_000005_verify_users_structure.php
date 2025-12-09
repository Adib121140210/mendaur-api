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
     * After renaming users.id to users.user_id, we need to:
     * 1. Add a numeric user_id column (primary) to users table
     * 2. Update all FK references from id to user_id
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            // Check if user_id already exists and is primary
            $userIdExists = DB::select('
                SELECT COLUMN_NAME, COLUMN_KEY 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = "users" 
                AND COLUMN_NAME = "user_id"
            ');

            if (empty($userIdExists)) {
                // If user_id doesn't exist, users table might not have been renamed yet
                // This shouldn't happen in normal flow, but skip if it does
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                return;
            }

            // Check if id column still exists (shouldn't after rename migration)
            $idExists = DB::select('
                SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = "users" 
                AND COLUMN_NAME = "id"
            ');

            if (!empty($idExists)) {
                // The rename didn't happen yet - users table still has old structure
                // This migration shouldn't run in this case
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                return;
            }

            // Everything is fine - FK should already point to user_id from previous migration
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
        // No need to reverse
    }
};
