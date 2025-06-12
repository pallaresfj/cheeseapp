<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Farm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 
        'branch_id', 
        'user_id', 
        'farm_type_id', 
        'location', 
        'status'
    ];

    public function branch() : BelongsTo
    { 
        return $this->belongsTo(Branch::class); 
    }
    public function user() : BelongsTo
    { 
        return $this->belongsTo(User::class); 
    }
    public function userAccount() : BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'user_id'); // especifica la clave foránea también
    }
    public function farmType() : BelongsTo
    { 
        return $this->belongsTo(FarmType::class); 
    }
    public function milkPurchases() : HasMany
    { 
        return $this->hasMany(MilkPurchase::class); 
    }
    public function liquidations() : HasMany
    { 
        return $this->hasMany(Liquidation::class); 
    }
    public function loans() : HasMany
    { 
        return $this->hasMany(Loan::class); 
    }
}