<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovementType extends Model
{
    protected $fillable = ['class', 'description', 'type'];

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}
