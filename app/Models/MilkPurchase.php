<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 
        'farm_id', 
        'date', 
        'liters', 
        'status'
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