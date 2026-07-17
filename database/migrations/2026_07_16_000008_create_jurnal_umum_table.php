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
        Schema::create('jurnal_umum', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_bukti')->index();
            $table->date('tanggal');
            $table->string('keterangan');
            $table->foreignId('kode_akun_id')->constrained('kode_akun')->cascadeOnDelete();
            $table->decimal('debet', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->foreignId('transaksi_harian_id')->nullable()->constrained('transaksi_harian')->nullOnDelete();
            $table->foreignId('unit_wisata_id')->constrained('unit_wisata')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_umum');
    }
};
