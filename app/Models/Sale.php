<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 
        'user_id', 
        'classification_id', 
        'sale_date',
        'kilos', 
        'price_per_kilo',
        'status'
    ];

    // amount_paid es una columna calculada (stored generated column en MySQL)

    public function branch() : BelongsTo
    { 
        return $this->belongsTo(Branch::class); 
    }
    public function user() : BelongsTo
    { 
        return $this->belongsTo(User::class); 
    }
    public function classification() : BelongsTo
    { 
        return $this->belongsTo(CustomerClassification::class, 'classification_id'); 
    }
}
