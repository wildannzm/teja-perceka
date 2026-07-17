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
        Schema::create('kategori_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_wisata_id')->constrained('unit_wisata')->cascadeOnDelete();
            $table->foreignId('kode_akun_id')->nullable()->constrained('kode_akun')->nullOnDelete();
            $table->string('nama');
            $table->string('tipe');
            $table->string('jenis');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_transaksi');
    }
};
