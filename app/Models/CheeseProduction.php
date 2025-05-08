<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheeseProduction extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'branch_id', 'produced_kilos', 'processed_liters'];

    public function branch() : BelongsTo
    { 
        return $this->belongsTo(Branch::class); 
    }
}