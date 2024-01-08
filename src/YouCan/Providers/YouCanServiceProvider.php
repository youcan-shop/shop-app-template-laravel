<?php

namespace YouCan\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class YouCanServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->booted(function () {
            Route::middleware('web')
                ->group(base_path('src/YouCan/routes/web.php'));
        });

        View::addNamespace('youcan', __DIR__ . '/../views');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->mergeConfigFrom(__DIR__.'/../config/youcan.php', 'youcan');
    }
}
