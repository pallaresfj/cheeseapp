<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRegistration extends Model
{
    protected $fillable = [
        'branch_id',
        'farm_id',
        'date',
        'liters',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'liters' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
