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
                L.total_liters,
                L.price_per_liter,
                (L.total_liters * L.price_per_liter) AS total_paid,

                -- Último préstamo activo/overdue/suspended por finca (si hay)
                COALESCE(
                    CASE
                        WHEN P.status IN ('active', 'overdue', 'suspended') THEN P.amount
                        ELSE 0
                    END, 0
                ) AS loan_amount,

                COALESCE(
                    CASE
                        WHEN P.status IN ('active', 'overdue', 'suspended') THEN P.amount - P.paid_value
                        ELSE 0
                    END, 0
                ) AS loan_balance,

                COALESCE(
                    CASE
                        WHEN P.status IN ('active', 'overdue', 'suspended') THEN P.installment_value
                        ELSE 0
                    END, 0
                ) AS installment_value,

                -- Descuento calculado
                COALESCE(
                    CASE
                        WHEN P.status NOT IN ('active', 'overdue', 'suspended') THEN 0
                        WHEN (P.amount - P.paid_value) >= P.installment_value AND (L.total_liters * L.price_per_liter) >= P.installment_value
                            THEN P.installment_value
                        WHEN (P.amount - P.paid_value) < P.installment_value AND (L.total_liters * L.price_per_liter) >= (P.amount - P.paid_value)
                            THEN (P.amount - P.paid_value)
                        ELSE 0
                    END, 0
                ) AS discount,

                -- Neto
                (L.total_liters * L.price_per_liter) -
                COALESCE(
                    CASE
                        WHEN P.status NOT IN ('active', 'overdue', 'suspended') THEN 0
                        WHEN (P.amount - P.paid_value) >= P.installment_value AND (L.total_liters * L.price_per_liter) >= P.installment_value
                            THEN P.installment_value
                        WHEN (P.amount - P.paid_value) < P.installment_value AND (L.total_liters * L.price_per_liter) >= (P.amount - P.paid_value)
                            THEN (P.amount - P.paid_value)
                        ELSE 0
                    END, 0
                ) AS net_amount

            FROM liquidations L
            LEFT JOIN loans P ON P.farm_id = L.farm_id
            AND P.status IN ('active', 'overdue', 'suspended')
            AND P.created_at = (
                SELECT MAX(created_at)
                FROM loans P2
                WHERE P2.farm_id = L.farm_id
                AND P2.status IN ('active', 'overdue', 'suspended')
            );
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS liquidation_summaries');
    }
};
