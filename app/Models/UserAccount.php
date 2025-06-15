<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'profile_photo_path',
        'role',
        'identification',
        'address',
        'phone',
        'avatar_url',
        'status',
    ];

    /**
     * Scope global para incluir solo usuarios con rol supplier o customer.
     */
    protected static function booted()
    {
        static::addGlobalScope('only_customers_or_suppliers', function ($query) {
            $query->whereIn('role', ['supplier', 'customer']);
        });
    }

    /**
     * Si quieres ocultar algunos atributos al serializar (opcional).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    public function farms() : HasMany
    {
        return $this->hasMany(Farm::class, 'user_id'); 
    }
    public function sales() : HasMany
    {
        return $this->hasMany(Sale::class, 'user_id');
    }
    public function salePayments() : HasMany
    {
        return $this->hasMany(SalePayment::class, 'user_id');
    }
}
