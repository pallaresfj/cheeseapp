<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('weekly_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade')->onUpdate('cascade');
            $table->date('date');
            $table->decimal('incomes', 10, 2);
            $table->decimal('expenses', 10, 2);
            $table->decimal('net_balance', 10, 2)->virtualAs('incomes - expenses');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('weekly_balances');
    }
};
