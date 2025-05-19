<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('liters', 10, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        DB::unprepared("DROP PROCEDURE IF EXISTS sp_registrar_compras;");
        DB::unprepared("
            CREATE PROCEDURE sp_registrar_compras(
                IN p_branch_id INT,
                IN p_date DATE,
                IN p_user_id INT
            )
            BEGIN
                DELETE FROM purchase_registrations
                WHERE user_id = p_user_id AND date = p_date;

                INSERT INTO purchase_registrations (branch_id, farm_id, date, liters, user_id, created_at, updated_at)
                SELECT f.branch_id, f.id, p_date, 0, p_user_id, NOW(), NOW()
                FROM farms f
                WHERE f.branch_id = p_branch_id
                AND f.status = true
                AND NOT EXISTS (
                    SELECT 1 FROM milk_purchases mp
                    WHERE mp.farm_id = f.id
                    AND mp.date = p_date
                    AND mp.status = 'pending'
                );

                INSERT INTO purchase_registrations (branch_id, farm_id, date, liters, user_id, created_at, updated_at)
                SELECT f.branch_id, f.id, p_date, mp.liters, p_user_id, NOW(), NOW()
                FROM farms f
                JOIN milk_purchases mp ON mp.farm_id = f.id AND mp.date = p_date AND mp.status = 'pending'
                WHERE f.branch_id = p_branch_id AND f.status = true;
            END
        ");

        DB::unprepared("DROP PROCEDURE IF EXISTS sp_transferir_compras;");
        DB::unprepared("
            CREATE PROCEDURE sp_transferir_compras(
                IN p_user_id INT
            )
            BEGIN
                INSERT INTO milk_purchases (branch_id, farm_id, date, liters, status, created_at, updated_at)
                SELECT rc.branch_id, rc.farm_id, rc.date, rc.liters, 'pending', NOW(), NOW()
                FROM purchase_registrations rc
                WHERE rc.user_id = p_user_id
                ON DUPLICATE KEY UPDATE liters = VALUES(liters), updated_at = NOW();

                DELETE FROM purchase_registrations WHERE user_id = p_user_id;
            END
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_registrations');
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_registrar_compras");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_transferir_compras");
    }
};
