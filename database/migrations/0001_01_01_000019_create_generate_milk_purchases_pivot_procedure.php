<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS generate_milk_purchases_pivot_view');

        DB::unprepared("
            CREATE PROCEDURE generate_milk_purchases_pivot_view(
                IN p_branch_id INT,
                IN p_start_date DATE,
                IN p_days INT,
                IN p_user_id INT
            )
            BEGIN
                DECLARE i INT DEFAULT 0;
                DECLARE pivot_date DATE;
                DECLARE sql_text LONGTEXT DEFAULT '';
                SET @view_name := CONCAT('milk_purchases_pivot_view_user_', p_user_id);

                -- Drop the user-specific view if it already exists
                SET @drop_view_sql := CONCAT('DROP VIEW IF EXISTS ', 'milk_purchases_pivot_view_user_', p_user_id);
                PREPARE drop_stmt FROM @drop_view_sql;
                EXECUTE drop_stmt;
                DEALLOCATE PREPARE drop_stmt;

                SET sql_text = CONCAT('CREATE OR REPLACE VIEW ', @view_name, ' AS SELECT f.id AS farm_id, f.branch_id, f.user_id, CONCAT(u.name, '' - '', f.name) AS proveedor_finca, ft.base_price, ');

                WHILE i < p_days DO
                    SET pivot_date = DATE_ADD(p_start_date, INTERVAL i DAY);
                    SET sql_text = CONCAT(
                        sql_text,
                        'COALESCE((',
                            'SELECT mp.liters FROM milk_purchases mp ',
                            'WHERE mp.farm_id = f.id ',
                            'AND mp.date = \"', pivot_date, '\" ',
                            'AND mp.status = \"pending\" ',
                            'LIMIT 1',
                        '), 0) AS `', DATE_FORMAT(pivot_date, '%Y_%m_%d'), '`, '
                    );
                    SET i = i + 1;
                END WHILE;

                SET sql_text = CONCAT(
                    sql_text,
                    'COALESCE((',
                        'SELECT SUM(mp2.liters) FROM milk_purchases mp2 ',
                        'WHERE mp2.farm_id = f.id ',
                        'AND mp2.date BETWEEN CAST(\'', p_start_date, '\' AS DATE) AND DATE_ADD(CAST(\'', p_start_date, '\' AS DATE), INTERVAL ', p_days, ' - 1 DAY) ',
                        'AND mp2.status = \"pending\"',
                    '), 0) AS total_litros, ',
                    'ft.base_price * COALESCE((',
                        'SELECT SUM(mp2.liters) FROM milk_purchases mp2 ',
                        'WHERE mp2.farm_id = f.id ',
                        'AND mp2.date BETWEEN CAST(\'', p_start_date, '\' AS DATE) AND DATE_ADD(CAST(\'', p_start_date, '\' AS DATE), INTERVAL ', p_days, ' - 1 DAY) ',
                        'AND mp2.status = \"pending\"',
                    '), 0) AS producido ',
                    'FROM farms f ',
                    'JOIN farm_types ft ON ft.id = f.farm_type_id ',
                    'JOIN users u ON u.id = f.user_id ',
                    'WHERE f.status = true AND f.branch_id = ', p_branch_id, ' ORDER BY proveedor_finca ASC'
                );

                SET @s := sql_text;
                PREPARE stmt FROM @s;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS generate_milk_purchases_pivot_view');
    }
};