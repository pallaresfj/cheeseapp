<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiquidationSummary extends Model
{
    protected $table = 'liquidation_summaries';
    public $timestamps = false;

    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getBranchNameAttribute()
    {
        return $this->branch?->name;
    }
    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function getFarmDisplayAttribute(): string
    {
        $farmName = $this->farm?->name ?? '';
        $providerName = $this->farm?->user?->name ?? '';
        return trim("{$providerName} - {$farmName}", ' -');
    }
}