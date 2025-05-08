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
        Schema::create('liquidations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('total_liters', 10, 2);
            $table->decimal('price_per_liter', 10, 2);
            $table->decimal('loan_amount', 10, 2)->default(0);
            $table->decimal('previous_balance', 10, 2)->default(0);
            $table->decimal('discounts', 10, 2)->default(0);
            $table->json('details')->nullable();
            $table->enum('status', ['pending', 'liquidated', 'annulled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidations');
    }
};
