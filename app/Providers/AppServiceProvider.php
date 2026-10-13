<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
//        $ipAddress = $_SERVER['SERVER_ADDR'];
//        if ($ipAddress != '127.0.0.1')
//        {
           //\URL::forceScheme('https');
//        }
    }

}
