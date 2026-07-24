<?php

namespace GeniusAuth\Laravel\Tests\Unit;

use GeniusAuth\Laravel\GeniusAuthClient;
use PHPUnit\Framework\TestCase;

class GeniusAuthClientTest extends TestCase
{
    public function test_the_client_can_be_instantiated(): void
    {
        $this->assertInstanceOf(GeniusAuthClient::class, new GeniusAuthClient);
    }
}
