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
        Schema::create('kategori_harga_riwayat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_transaksi_id')->constrained('kategori_transaksi')->cascadeOnDelete();
            $table->decimal('harga', 15, 2);
            $table->date('berlaku_dari');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_harga_riwayat');
    }
};
