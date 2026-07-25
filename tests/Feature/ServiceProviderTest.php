<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Tests\Feature;

use GeniusAuth\Laravel\Contracts\LinkFlowInterface;
use GeniusAuth\Laravel\Contracts\OidcClientInterface;
use GeniusAuth\Laravel\Contracts\StaffSyncInterface;
use GeniusAuth\Laravel\Contracts\SyncClientInterface;
use GeniusAuth\Laravel\Contracts\TokenValidatorInterface;
use GeniusAuth\Laravel\Contracts\UserRepositoryInterface;
use GeniusAuth\Laravel\Facades\GeniusAuth;
use GeniusAuth\Laravel\Infrastructure\ConfigUserRepository;
use GeniusAuth\Laravel\Infrastructure\GeniusAuthSyncClient;
use GeniusAuth\Laravel\Services\LinkFlowService;
use GeniusAuth\Laravel\Services\OidcClientService;
use GeniusAuth\Laravel\Services\OidcTokenValidator;
use GeniusAuth\Laravel\Services\StaffSyncService;
use GeniusAuth\Laravel\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_it_merges_configuration(): void
    {
        $this->assertSame('https://auth.geniuspay.tech', config('geniusauth.issuer'));
    }

    public function test_it_binds_oidc_client_interface(): void
    {
        $this->assertInstanceOf(OidcClientService::class, $this->app->make(OidcClientInterface::class));
    }

    public function test_it_binds_token_validator_interface(): void
    {
        $this->assertInstanceOf(OidcTokenValidator::class, $this->app->make(TokenValidatorInterface::class));
    }

    public function test_it_binds_link_flow_interface(): void
    {
        $this->assertInstanceOf(LinkFlowService::class, $this->app->make(LinkFlowInterface::class));
    }

    public function test_it_binds_staff_sync_interface(): void
    {
        $this->assertInstanceOf(StaffSyncService::class, $this->app->make(StaffSyncInterface::class));
    }

    public function test_it_binds_sync_client_interface(): void
    {
        $this->assertInstanceOf(GeniusAuthSyncClient::class, $this->app->make(SyncClientInterface::class));
    }

    public function test_it_binds_user_repository_interface(): void
    {
        $this->assertInstanceOf(ConfigUserRepository::class, $this->app->make(UserRepositoryInterface::class));
    }

    public function test_it_registers_the_facade_alias(): void
    {
        $this->assertInstanceOf(OidcClientInterface::class, GeniusAuth::getFacadeRoot());
    }

    public function test_it_shares_singleton_between_interface_and_alias(): void
    {
        $viaInterface = $this->app->make(OidcClientInterface::class);
        $viaAlias = $this->app->make(OidcClientService::class);

        $this->assertSame($viaInterface, $viaAlias);
    }

    public function test_it_shares_sync_client_singleton(): void
    {
        $viaInterface = $this->app->make(SyncClientInterface::class);
        $viaAlias = $this->app->make(GeniusAuthSyncClient::class);

        $this->assertSame($viaInterface, $viaAlias);
    }

    public function test_it_shares_user_repository_singleton(): void
    {
        $viaInterface = $this->app->make(UserRepositoryInterface::class);
        $viaAlias = $this->app->make(ConfigUserRepository::class);

        $this->assertSame($viaInterface, $viaAlias);
    }
}
