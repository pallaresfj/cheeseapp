<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FarmType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'base_price'];

    public function farms() : HasMany
    { 
        return $this->hasMany(Farm::class); 
    }
}
