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
        Schema::table('profit_allocation_history', function (Blueprint $table) {
            // 'pengurang' = computed from the original net profit (value kept)
            // 'ad_art'    = computed from net profit after deductions (value kept)
            $table->string('allocation_group', 20)->default('pengurang')->after('percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profit_allocation_history', function (Blueprint $table) {
            $table->dropColumn('allocation_group');
        });
    }
};
