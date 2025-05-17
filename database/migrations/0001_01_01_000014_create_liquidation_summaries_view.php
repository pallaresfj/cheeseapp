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
        DB::statement(<<<SQL
            CREATE OR REPLACE VIEW liquidation_summaries AS
            SELECT
                L.id,
                L.date,
                L.branch_id,
                L.farm_id,
                L.details,
                L.total_liters,
                L.price_per_liter,
                P.id AS loan_id,
                (L.total_liters * L.price_per_liter) AS total_paid,

                COALESCE(
                    CASE
                        WHEN P.status IN ('active', 'overdue', 'suspended') THEN P.amount
                        ELSE 0
                    END, 0
                ) AS loan_amount,

                COALESCE(
                    CASE
                        WHEN P.status IN ('active', 'overdue', 'suspended') THEN (P.amount - P.paid_value)
                        ELSE 0
                    END, 0
                ) AS loan_balance,

                COALESCE(
                    CASE
                        WHEN P.status IN ('active', 'overdue', 'suspended') THEN P.installment_value
                        ELSE 0
                    END, 0
                ) AS installment_value,

                COALESCE(
                    CASE
                        WHEN P.id IS NULL OR P.status = 'paid' THEN 0
                        WHEN P.status = 'suspended' THEN 0
                        WHEN (P.amount - P.paid_value) >= P.installment_value AND (L.total_liters * L.price_per_liter) >= P.installment_value THEN P.installment_value
                        WHEN (P.amount - P.paid_value) < P.installment_value AND (L.total_liters * L.price_per_liter) >= (P.amount - P.paid_value) THEN (P.amount - P.paid_value)
                        WHEN P.installment_value > (L.total_liters * L.price_per_liter) THEN (L.total_liters * L.price_per_liter)
                        ELSE 0
                    END, 0
                ) AS discount,

                (L.total_liters * L.price_per_liter) -
                COALESCE(
                    CASE
                        WHEN P.id IS NULL OR P.status = 'paid' THEN 0
                        WHEN P.status = 'suspended' THEN 0
                        WHEN (P.amount - P.paid_value) >= P.installment_value AND (L.total_liters * L.price_per_liter) >= P.installment_value THEN P.installment_value
                        WHEN (P.amount - P.paid_value) < P.installment_value AND (L.total_liters * L.price_per_liter) >= (P.amount - P.paid_value) THEN (P.amount - P.paid_value)
                        WHEN P.installment_value > (L.total_liters * L.price_per_liter) THEN (L.total_liters * L.price_per_liter)
                        ELSE 0
                    END, 0
                ) AS net_amount,
                ((CASE
                    WHEN P.status IN ('active', 'overdue', 'suspended') THEN (P.amount - P.paid_value)
                    ELSE 0
                END) -
                COALESCE(
                    CASE
                        WHEN P.id IS NULL OR P.status = 'paid' THEN 0
                        WHEN P.status = 'suspended' THEN 0
                        WHEN (P.amount - P.paid_value) >= P.installment_value AND (L.total_liters * L.price_per_liter) >= P.installment_value THEN P.installment_value
                        WHEN (P.amount - P.paid_value) < P.installment_value AND (L.total_liters * L.price_per_liter) >= (P.amount - P.paid_value) THEN (P.amount - P.paid_value)
                        WHEN P.installment_value > (L.total_liters * L.price_per_liter) THEN (L.total_liters * L.price_per_liter)
                        ELSE 0
                    END, 0)
                ) AS new_balance

            FROM liquidations L
            LEFT JOIN (
                SELECT *
                FROM loans
                WHERE status IN ('active', 'overdue', 'suspended')
                AND created_at IN (
                    SELECT MAX(created_at)
                    FROM loans
                    WHERE status IN ('active', 'overdue', 'suspended')
                    GROUP BY farm_id
                )
            ) P ON P.farm_id = L.farm_id
            WHERE L.status = 'pending';
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS liquidation_summaries');
    }
};
