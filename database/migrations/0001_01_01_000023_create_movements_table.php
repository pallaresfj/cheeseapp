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

        DB::unprepared("DROP PROCEDURE IF EXISTS reconcile_movements;");

        DB::unprepared('
            CREATE PROCEDURE reconcile_movements(
                IN p_branch_id INT,
                IN p_start_date DATE,
                IN p_end_date DATE,
                IN p_balance_date DATE
            )
            BEGIN
                DECLARE v_incomes DECIMAL(10,2) DEFAULT 0;
                DECLARE v_expenses DECIMAL(10,2) DEFAULT 0;
                DECLARE v_weekly_balance_id INT;

                DECLARE v_income_ids TEXT;
                DECLARE v_expense_ids TEXT;

                SELECT GROUP_CONCAT(id) INTO v_income_ids
                FROM movement_types
                WHERE class = "income";

                SELECT GROUP_CONCAT(id) INTO v_expense_ids
                FROM movement_types
                WHERE class = "expense";

                SET @sql_incomes = CONCAT("SELECT COALESCE(SUM(value),0) INTO @v_incomes 
                    FROM movements 
                    WHERE branch_id = ", p_branch_id, " 
                    AND date BETWEEN \'", p_start_date, "\' AND \'", p_end_date, "\' 
                    AND movement_type_id IN (", v_income_ids, ")");

                SET @sql_expenses = CONCAT("SELECT COALESCE(SUM(value),0) INTO @v_expenses 
                    FROM movements 
                    WHERE branch_id = ", p_branch_id, " 
                    AND date BETWEEN \'", p_start_date, "\' AND \'", p_end_date, "\' 
                    AND movement_type_id IN (", v_expense_ids, ")");

                PREPARE stmt_incomes FROM @sql_incomes;
                EXECUTE stmt_incomes;
                DEALLOCATE PREPARE stmt_incomes;

                PREPARE stmt_expenses FROM @sql_expenses;
                EXECUTE stmt_expenses;
                DEALLOCATE PREPARE stmt_expenses;

                SET v_incomes = @v_incomes;
                SET v_expenses = @v_expenses;

                INSERT INTO weekly_balances (branch_id, date, incomes, expenses, created_at, updated_at)
                VALUES (p_branch_id, p_balance_date, v_incomes, v_expenses, NOW(), NOW());

                SET v_weekly_balance_id = LAST_INSERT_ID();

                UPDATE movements
                SET status = "reconciled",
                    weekly_balance_id = v_weekly_balance_id,
                    updated_at = NOW()
                WHERE branch_id = p_branch_id
                  AND date BETWEEN p_start_date AND p_end_date;
            END;
        ');
    }

    public function down(): void {
        Schema::dropIfExists('movements');
    }
};
