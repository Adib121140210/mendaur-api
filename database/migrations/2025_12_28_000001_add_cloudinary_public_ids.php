<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('foto_profil_public_id')->nullable()->after('foto_profil');
        });
        
        Schema::table('tabung_sampahs', function (Blueprint $table) {
            $table->string('foto_sampah_public_id')->nullable()->after('foto_sampah');
        });
        
        Schema::table('produks', function (Blueprint $table) {
            $table->string('foto_public_id')->nullable()->after('foto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('foto_profil_public_id');
        });
        
        Schema::table('tabung_sampahs', function (Blueprint $table) {
            $table->dropColumn('foto_sampah_public_id');
        });
        
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn('foto_public_id');
        });
    }
};
