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
     * Fix personal_access_tokens table:
     * 1. If table doesn't exist, create it with personal_access_token_id as PK
     * 2. If table exists with 'id' column, rename it to 'personal_access_token_id'
     */
    public function up(): void
    {
        // First, drop if exists from the drop migration that ran later
        if (Schema::hasTable('personal_access_tokens')) {
            Schema::drop('personal_access_tokens');
        }

        // Create the table with correct primary key name
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->bigIncrements('personal_access_token_id');
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
