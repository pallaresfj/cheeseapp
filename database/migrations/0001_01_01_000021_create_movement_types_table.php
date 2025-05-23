<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('movement_types', function (Blueprint $table) {
            $table->id();
            $table->enum('class', ['income', 'expense']);
            $table->string('description', 100)->nullable();
            $table->enum('type', ['fixed', 'variable'])->default('fixed');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('movement_types');
    }
};