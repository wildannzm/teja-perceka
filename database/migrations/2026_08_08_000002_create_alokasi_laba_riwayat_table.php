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
        Schema::create('alokasi_laba_riwayat', function (Blueprint $table) {
            $table->id();
            $table->string('keterangan');
            $table->decimal('persentase', 5, 2);
            $table->date('berlaku_dari');
            $table->foreignId('unit_wisata_id')->nullable()->constrained('unit_wisata')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alokasi_laba_riwayat');
    }
};
