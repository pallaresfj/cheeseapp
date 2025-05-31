<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movement extends Model
{
    protected $fillable = [
        'branch_id',
        'movement_type_id',
        'date',
        'value',
        'status',
        'weekly_balance_id'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    public function movementType(): BelongsTo
    {
        return $this->belongsTo(MovementType::class);
    }
    public function weeklyBalance(): BelongsTo
    {
        return $this->belongsTo(WeeklyBalance::class);
    }
}
