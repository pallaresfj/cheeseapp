<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'amount',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'valor' => 'decimal:2',
    ];

    /**
     * Relación: El pago pertenece a un usuario (cliente).
     */
    /* public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    } */
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    /**
     * Relación: Acceso a las ventas del mismo usuario de este pago.
     */
    /* public function sales() : HasMany
    {
        return $this->hasMany(Sale::class, 'user_id', 'user_id');
    } */
}