<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone', 'active'];

    public function farms() : HasMany
    {
        return $this->hasMany(Farm::class);
    }
    public function milkPurchases() : HasMany
    { 
        return $this->hasMany(MilkPurchase::class); 
    }
    public function liquidations() : HasMany
    { 
        return $this->hasMany(Liquidation::class); 
    }
    public function sales() : HasMany
    { 
        return $this->hasMany(Sale::class); 
    }
    public function cheeseProductions() : HasMany
    { 
        return $this->hasMany(CheeseProduction::class); 
    }
}
