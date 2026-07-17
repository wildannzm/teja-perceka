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
        Schema::create('transaksi_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_wisata_id')->constrained('unit_wisata')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->date('tanggal_akhir')->nullable()->comment('Untuk unit mingguan');
            $table->decimal('total_pemasukan', 15, 2)->default(0);
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_harian');
    }
};
