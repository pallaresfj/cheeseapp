<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW user_sales_balances AS
            SELECT
                users.id AS user_id,
                users.name AS customer_name,
                COALESCE(s.total_sales, 0) AS total_sales,
                COALESCE(p.total_payments, 0) AS total_payments,
                COALESCE(s.total_sales, 0) - COALESCE(p.total_payments, 0) AS balance,
                CASE 
                    WHEN COALESCE(s.total_sales, 0) - COALESCE(p.total_payments, 0) = 0 THEN 'up_to_date'
                    ELSE 'pending'
                END AS status
            FROM users
            LEFT JOIN (
                SELECT user_id, SUM(amount_paid) AS total_sales
                FROM sales
                GROUP BY user_id
            ) AS s ON users.id = s.user_id
            LEFT JOIN (
                SELECT user_id, SUM(amount) AS total_payments
                FROM sale_payments
                GROUP BY user_id
            ) AS p ON users.id = p.user_id
            WHERE users.role = 'customer' AND users.status = true
            ORDER BY customer_name ASC
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS user_sales_balances");
    }
};
