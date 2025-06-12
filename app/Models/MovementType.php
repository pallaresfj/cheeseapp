<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovementType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'class', 
        'description', 
        'type'
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}
