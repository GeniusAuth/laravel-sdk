<?php

namespace GeniusAuth\Laravel;

use GeniusAuth\Laravel\Http\Middleware\RequireGeniusAuth;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class GeniusAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/geniusauth.php', 'geniusauth');
        $this->app->singleton(GeniusAuthClient::class);
    }

    public function boot(Router $router): void
    {
        $this->publishes([__DIR__.'/../config/geniusauth.php' => config_path('geniusauth.php')], 'geniusauth-config');
        $this->loadRoutesFrom(__DIR__.'/../routes/geniusauth.php');
        $router->aliasMiddleware('geniusauth', RequireGeniusAuth::class);
    }
}
