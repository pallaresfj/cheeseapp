<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared(<<<SQL
        DROP PROCEDURE IF EXISTS liquidar_compras;
        CREATE PROCEDURE liquidar_compras(
            IN p_branch_id INT,
            IN p_start_date DATE,
            IN p_end_date DATE,
            IN p_farm_ids TEXT
        )
        BEGIN
            DECLARE done INT DEFAULT FALSE;
            DECLARE v_farm_id INT;
            DECLARE v_total_liters DECIMAL(10,2);
            DECLARE v_base_price DECIMAL(10,2);
            DECLARE v_liquidation_id INT;
            DECLARE v_details JSON;

            DECLARE cur CURSOR FOR
                SELECT farm_id
                FROM milk_purchases
                WHERE branch_id = p_branch_id
                AND status = 'pending'
                AND date BETWEEN p_start_date AND p_end_date
                AND FIND_IN_SET(farm_id, p_farm_ids)
                GROUP BY farm_id;

            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

            OPEN cur;

            read_loop: LOOP
                FETCH cur INTO v_farm_id;
                IF done THEN
                    LEAVE read_loop;
                END IF;

                SELECT SUM(liters) INTO v_total_liters
                FROM milk_purchases
                WHERE branch_id = p_branch_id AND farm_id = v_farm_id
                AND status = 'pending'
                AND date BETWEEN p_start_date AND p_end_date;

                SELECT ft.base_price INTO v_base_price
                FROM farms f
                JOIN farm_types ft ON f.farm_type_id = ft.id
                WHERE f.id = v_farm_id;

                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT('date', date, 'liters', liters)
                )
                INTO v_details
                FROM milk_purchases
                WHERE branch_id = p_branch_id AND farm_id = v_farm_id
                AND status = 'pending'
                AND date BETWEEN p_start_date AND p_end_date;

                INSERT INTO liquidations (
                    branch_id, farm_id, date, total_liters, price_per_liter,
                    loan_amount, previous_balance, discounts, details, status,
                    created_at, updated_at
                ) VALUES (
                    p_branch_id, v_farm_id, p_end_date, v_total_liters, v_base_price,
                    0, 0, 0, v_details, 'pending', NOW(), NOW()
                );

                SET v_liquidation_id = LAST_INSERT_ID();

                UPDATE milk_purchases
                SET status = 'liquidated',
                    liquidation_id = v_liquidation_id
                WHERE branch_id = p_branch_id AND farm_id = v_farm_id
                AND status = 'pending'
                AND date BETWEEN p_start_date AND p_end_date;
            END LOOP;

            CLOSE cur;
        END;
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS liquidar_compras;');
    }
};
