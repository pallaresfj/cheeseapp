<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiquidationSummary extends Model
{
    protected $table = 'liquidation_summaries';
    public $timestamps = false;

    protected $guarded = [];
}
