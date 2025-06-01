<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        DB::unprepared("DROP TRIGGER IF EXISTS trg_update_weekly_balance_after_update;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_update_weekly_balance_after_delete;");

        DB::unprepared("
            CREATE TRIGGER trg_update_weekly_balance_after_update
            AFTER UPDATE ON movements
            FOR EACH ROW
            BEGIN
                IF OLD.status = 'reconciled' THEN
                    IF OLD.movement_type_id IN (SELECT id FROM movement_types WHERE class = 'income') THEN
                        UPDATE weekly_balances
                        SET incomes = incomes - OLD.value
                        WHERE id = OLD.weekly_balance_id;
                    ELSE
                        UPDATE weekly_balances
                        SET expenses = expenses - OLD.value
                        WHERE id = OLD.weekly_balance_id;
                    END IF;
                END IF;

                IF NEW.status = 'reconciled' THEN
                    IF NEW.movement_type_id IN (SELECT id FROM movement_types WHERE class = 'income') THEN
                        UPDATE weekly_balances
                        SET incomes = incomes + NEW.value
                        WHERE id = NEW.weekly_balance_id;
                    ELSE
                        UPDATE weekly_balances
                        SET expenses = expenses + NEW.value
                        WHERE id = NEW.weekly_balance_id;
                    END IF;
                END IF;
            END;
        ");

        DB::unprepared("
            CREATE TRIGGER trg_update_weekly_balance_after_delete
            AFTER DELETE ON movements
            FOR EACH ROW
            BEGIN
                IF OLD.status = 'reconciled' THEN
                    IF OLD.movement_type_id IN (SELECT id FROM movement_types WHERE class = 'income') THEN
                        UPDATE weekly_balances
                        SET incomes = incomes - OLD.value
                        WHERE id = OLD.weekly_balance_id;
                    ELSE
                        UPDATE weekly_balances
                        SET expenses = expenses - OLD.value
                        WHERE id = OLD.weekly_balance_id;
                    END IF;
                END IF;
            END;
        ");
    }

    public function down(): void {
        Schema::dropIfExists('movements');
    }
};
