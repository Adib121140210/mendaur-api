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
        Schema::create('penarikan_tunai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('jumlah_poin')->comment('Points deducted');
            $table->decimal('jumlah_rupiah', 15, 2)->comment('Cash amount in Rupiah');
            $table->string('nomor_rekening', 50)->comment('Bank account number');
            $table->string('nama_bank', 100)->comment('Bank name');
            $table->string('nama_penerima', 255)->comment('Account holder name');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_admin')->nullable()->comment('Admin notes for rejection');
            $table->foreignId('processed_by')->nullable()->constrained('users', 'id')->onDelete('set null')->comment('Admin user ID who processed');
            $table->timestamp('processed_at')->nullable()->comment('When approved/rejected');
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
        Schema::dropIfExists('penarikan_tunai');
    }
};
