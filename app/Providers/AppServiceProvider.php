<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('settings')) {
            $settings = Cache::rememberForever('app_settings', function () {
                $data = Setting::pluck('value', 'key')->toArray();
                return $data;
            });

            foreach ($settings as $key => $value) {
                Config::set("app_settings.$key", $value);
            }

            View::share('settings', $settings);
        }
    }
}
