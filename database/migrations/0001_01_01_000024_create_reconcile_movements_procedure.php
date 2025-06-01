<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS reconcile_movements');

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
                    WHERE branch_id = ? 
                      AND date BETWEEN ? AND ? 
                      AND movement_type_id IN (", v_income_ids, ")");

                SET @sql_expenses = CONCAT("SELECT COALESCE(SUM(value),0) INTO @v_expenses 
                    FROM movements 
                    WHERE branch_id = ? 
                      AND date BETWEEN ? AND ? 
                      AND movement_type_id IN (", v_expense_ids, ")");

                PREPARE stmt_incomes FROM @sql_incomes;
                EXECUTE stmt_incomes USING @p_branch_id, @p_start_date, @p_end_date;
                DEALLOCATE PREPARE stmt_incomes;

                PREPARE stmt_expenses FROM @sql_expenses;
                EXECUTE stmt_expenses USING @p_branch_id, @p_start_date, @p_end_date;
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
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS reconcile_movements');
    }
};
