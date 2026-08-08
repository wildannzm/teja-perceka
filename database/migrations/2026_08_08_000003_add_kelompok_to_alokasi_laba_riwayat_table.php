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
        Schema::table('alokasi_laba_riwayat', function (Blueprint $table) {
            // 'pengurang' = dihitung dari Laba Bersih asli
            // 'ad_art'    = dihitung dari Laba Bersih setelah Pengurang
            $table->string('kelompok', 20)->default('pengurang')->after('persentase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alokasi_laba_riwayat', function (Blueprint $table) {
            $table->dropColumn('kelompok');
        });
    }
};
