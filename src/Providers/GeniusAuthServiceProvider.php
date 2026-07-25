<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Providers;

use GeniusAuth\Laravel\Contracts\LinkFlowInterface;
use GeniusAuth\Laravel\Contracts\OidcClientInterface;
use GeniusAuth\Laravel\Contracts\StaffSyncInterface;
use GeniusAuth\Laravel\Contracts\SyncClientInterface;
use GeniusAuth\Laravel\Contracts\TokenValidatorInterface;
use GeniusAuth\Laravel\Contracts\UserRepositoryInterface;
use GeniusAuth\Laravel\Http\Middleware\RequireGeniusAuth;
use GeniusAuth\Laravel\Infrastructure\ConfigUserRepository;
use GeniusAuth\Laravel\Infrastructure\GeniusAuthSyncClient;
use GeniusAuth\Laravel\Services\LinkFlowService;
use GeniusAuth\Laravel\Services\OidcClientService;
use GeniusAuth\Laravel\Services\OidcTokenValidator;
use GeniusAuth\Laravel\Services\StaffSyncService;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class GeniusAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/geniusauth.php', 'geniusauth');

        // ── Domain contracts → implementations ──────────────────────────────
        $this->app->singleton(TokenValidatorInterface::class, OidcTokenValidator::class);
        $this->app->singleton(OidcClientInterface::class, OidcClientService::class);
        $this->app->singleton(LinkFlowInterface::class, LinkFlowService::class);
        $this->app->singleton(StaffSyncInterface::class, StaffSyncService::class);

        // ── Infrastructure contracts → implementations ──────────────────────
        $this->app->singleton(SyncClientInterface::class, GeniusAuthSyncClient::class);
        $this->app->singleton(UserRepositoryInterface::class, ConfigUserRepository::class);

        // ── @deprecated backward-compatibility aliases ───────────────────────
        // These allow existing code that injects the concrete class to keep
        // working during migration. They will be removed in v1.0.
        // @deprecated Inject the interface instead.
        $this->app->alias(OidcClientInterface::class, OidcClientService::class);
        $this->app->alias(LinkFlowInterface::class, LinkFlowService::class);
        $this->app->alias(StaffSyncInterface::class, StaffSyncService::class);
        $this->app->alias(SyncClientInterface::class, GeniusAuthSyncClient::class);
        $this->app->alias(TokenValidatorInterface::class, OidcTokenValidator::class);
        $this->app->alias(UserRepositoryInterface::class, ConfigUserRepository::class);
    }

    public function boot(Router $router): void
    {
        $this->publishes([__DIR__ . '/../../config/geniusauth.php' => config_path('geniusauth.php')], 'geniusauth-config');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/geniusauth.php');
        $router->aliasMiddleware('geniusauth', RequireGeniusAuth::class);

        if (config('geniusauth.filament_auth_enabled', false)) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/filament-auth.php');
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'geniusauth');
    }
}
