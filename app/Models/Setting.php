<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'category', 'value'];

    public $timestamps = true;

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('app_settings');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('app_settings');
        });
    }
}
