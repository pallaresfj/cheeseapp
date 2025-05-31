<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiquidationSummary extends Model
{
    protected $table = 'liquidation_summaries';
    public $timestamps = false;

    protected $guarded = [];

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
    public function getBranchNameAttribute()
    {
        return $this->branch?->name;
    }
    public function getFarmDisplayAttribute(): string
    {
        $farmName = $this->farm?->name ?? '';
        $providerName = $this->farm?->user?->name ?? '';
        return trim("{$providerName} - {$farmName}", ' -');
    }
}