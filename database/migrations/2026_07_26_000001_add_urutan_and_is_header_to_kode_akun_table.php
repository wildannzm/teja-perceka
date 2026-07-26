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
        Schema::table('kode_akun', function (Blueprint $table) {
            $table->integer('urutan')->nullable()->after('tipe');
            $table->boolean('is_header')->default(false)->after('urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kode_akun', function (Blueprint $table) {
            $table->dropColumn(['urutan', 'is_header']);
        });
    }
};
