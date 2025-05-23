<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('movement_type_id')->constrained('movement_types')->onDelete('cascade')->onUpdate('cascade');
            $table->date('date');
            $table->decimal('value', 10, 2);
            $table->enum('status', ['pending', 'reconciled'])->default('pending');
            $table->foreignId('weekly_balance_id')->nullable()->constrained('weekly_balances')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('movements');
    }
};
