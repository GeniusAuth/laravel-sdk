<?php

namespace GeniusAuth\Laravel\Tests\Feature;

use GeniusAuth\Laravel\Facades\GeniusAuth;
use GeniusAuth\Laravel\GeniusAuthClient;
use GeniusAuth\Laravel\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_it_registers_the_client_and_configuration(): void
    {
        $this->assertInstanceOf(GeniusAuthClient::class, $this->app->make(GeniusAuthClient::class));
        $this->assertSame('https://auth.geniuspay.tech', config('geniusauth.issuer'));
    }

    public function test_it_registers_the_facade_alias(): void
    {
        $this->assertSame(GeniusAuthClient::class, GeniusAuth::getFacadeRoot()::class);
    }
}
