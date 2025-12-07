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
        Schema::create('penukaran_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            $table->string('nama_produk');
            $table->integer('poin_digunakan');
            $table->integer('jumlah')->default(1);
            $table->enum('status', ['pending', 'approved', 'cancelled',])->default('pending');
            $table->text('metode_ambil');
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_penukaran')->useCurrent();
            $table->timestamp('tanggal_diambil')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status'], 'idx_user_status');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penukaran_produk');
    }
};
