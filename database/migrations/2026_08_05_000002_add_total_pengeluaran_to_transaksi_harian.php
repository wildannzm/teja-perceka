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
        Schema::table('transaksi_harian', function (Blueprint $table) {
            $table->decimal('total_pengeluaran', 15, 2)->default(0)->after('total_pemasukan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_harian', function (Blueprint $table) {
            $table->dropColumn('total_pengeluaran');
        });
    }
};
