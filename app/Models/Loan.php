<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'farm_id', 
        'date', 
        'amount', 
        'installments',
        'installment_value', 
        'paid_value', 
        'description', 
        'status'
    ];

    public function user() : BelongsTo
    { 
        return $this->belongsTo(User::class); 
    }
    public function farm() : BelongsTo
    { 
        return $this->belongsTo(Farm::class); 
    }
    public function payments() : HasMany
    { 
        return $this->hasMany(LoanPayment::class); 
    }

    public function scopeActivosDeUsuario($query, $userId) 
    {
        return $query->where('user_id', $userId)
                     ->whereIn('status', ['active', 'overdue', 'suspended']);
    }
}