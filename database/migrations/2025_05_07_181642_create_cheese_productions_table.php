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
        Schema::create('cheese_production', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->decimal('produced_kilos', 10, 2);
            $table->decimal('processed_liters', 10, 2);
            $table->timestamps();
        
            $table->unique(['date', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheese_productions');
    }
};
