<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkPurchasesPivotView extends Model
{
    protected $table = 'milk_purchases_pivot_view';

    public $incrementing = false;
    public $timestamps = false;

    protected $primaryKey = 'farm_id';
    protected $keyType = 'string';

    protected $guarded = [];

    public function branch() : BelongsTo
    { 
        return $this->belongsTo(Branch::class); 
    }
    public function farm() : BelongsTo
    { 
        return $this->belongsTo(Farm::class); 
    }
    public function user() : BelongsTo
    { 
        return $this->belongsTo(User::class);
    }
}
