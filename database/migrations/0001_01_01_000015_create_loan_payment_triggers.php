
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
            -- Crear procedimiento para actualizar préstamos
            DROP PROCEDURE IF EXISTS update_loan_status;
            CREATE PROCEDURE update_loan_status(IN loanId BIGINT)
            BEGIN
                DECLARE total_paid DECIMAL(10,2);
                DECLARE loan_amount DECIMAL(10,2);

                SELECT COALESCE(SUM(amount), 0) INTO total_paid
                FROM loan_payments
                WHERE loan_id = loanId;

                SELECT amount INTO loan_amount
                FROM loans
                WHERE id = loanId;

                IF total_paid > loan_amount THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El valor pagado excede el monto del préstamo.';
                END IF;

                UPDATE loans
                SET paid_value = total_paid,
                    status = CASE
                                WHEN total_paid = loan_amount THEN 'paid'
                                ELSE 'active'
                             END
                WHERE id = loanId;
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_after_insert_loan_payment;
            CREATE TRIGGER trg_after_insert_loan_payment
            AFTER INSERT ON loan_payments
            FOR EACH ROW
            BEGIN
                CALL update_loan_status(NEW.loan_id);
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_after_update_loan_payment;
            CREATE TRIGGER trg_after_update_loan_payment
            AFTER UPDATE ON loan_payments
            FOR EACH ROW
            BEGIN
                CALL update_loan_status(NEW.loan_id);
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_after_delete_loan_payment;
            CREATE TRIGGER trg_after_delete_loan_payment
            AFTER DELETE ON loan_payments
            FOR EACH ROW
            BEGIN
                CALL update_loan_status(OLD.loan_id);
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_after_insert_loan_payment;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_after_update_loan_payment;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_after_delete_loan_payment;");
        DB::unprepared("DROP PROCEDURE IF EXISTS update_loan_status;");
    }
};
