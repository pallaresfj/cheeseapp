<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class LoanPayment extends Model
{
    use HasFactory;

    protected $fillable = ['loan_id', 'date', 'amount'];

    public function loan() : BelongsTo
    { 
        return $this->belongsTo(Loan::class); 
    }

    protected static function booted()
    {
        static::created(function ($payment) {
            DB::statement("CALL update_paid_value(?)", [$payment->loan_id]);
        });

        static::updated(function ($payment) {
            DB::statement("CALL update_paid_value(?)", [$payment->loan_id]);
        });

        static::deleted(function ($payment) {
            DB::statement("CALL update_paid_value(?)", [$payment->loan_id]);
        });
    }
}