<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Liquidation extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 
        'farm_id', 
        'date', 
        'total_liters', 
        'price_per_liter',
        'loan_amount', 
        'previous_balance', 
        'discounts', 
        'details', 
        'status'
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function branch() : BelongsTo
    { 
        return $this->belongsTo(Branch::class); 
    }
    public function farm() : BelongsTo
    { 
        return $this->belongsTo(Farm::class); 
    }
    public function user() 
    { 
        return $this->farm->user; 
    }
}