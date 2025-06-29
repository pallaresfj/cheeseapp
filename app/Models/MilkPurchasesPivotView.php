<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkPurchasesPivotView extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable('milk_purchases_pivot_view_user_' . Auth::id());
    }

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
