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
        Schema::create('profit_allocation_history', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('percentage', 5, 2);
            $table->date('effective_from');
            $table->foreignId('business_unit_id')->nullable()->constrained('business_units')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_allocation_history');
    }
};
