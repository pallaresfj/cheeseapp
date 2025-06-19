<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE TRIGGER before_delete_milk_purchase
            BEFORE DELETE ON milk_purchases
            FOR EACH ROW
            BEGIN
                IF OLD.status = 'liquidated' THEN
                    UPDATE milk_purchases
                    SET status = 'pending',
                        liquidation_id = NULL
                    WHERE liquidation_id = OLD.liquidation_id;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_delete_milk_purchase');
    }
};
