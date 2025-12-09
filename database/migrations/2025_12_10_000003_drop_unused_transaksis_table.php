<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drop the unused 'transaksis' table.
     * The system uses:
     * - poin_transaksis for point transactions
     * - penukaran_produk for product exchanges
     * - penarikan_saldo for balance withdrawals
     * 
     * The 'transaksis' table is a duplicate/legacy table that should not be used.
     */
    public function up(): void
    {
        Schema::dropIfExists('transaksis');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            $table->foreignId('kategori_id')->constrained('kategori_transaksi')->onDelete('cascade');
            $table->integer('jumlah');
            $table->integer('total_poin');
            $table->enum('status', ['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'])->default('pending');
            $table->string('metode_pengiriman')->nullable();
            $table->text('alamat_pengiriman')->nullable();
            $table->timestamps();
        });
    }
};
