<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSalesBalance extends Model
{
    // Nombre de la vista SQL
    public $table = 'user_sales_balances';
    public $primaryKey = 'user_id';

    // Este modelo no tiene timestamps ni ID incremental
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'customer_name',
        'total_sales',
        'total_payments',
        'balance',
        'status',
    ];

    protected $casts = [
        'total_sales' => 'decimal:2',
        'total_payments' => 'decimal:2',
        'balance' => 'decimal:2',
    ];
}