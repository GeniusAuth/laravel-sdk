<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Tests\Unit;

use GeniusAuth\Laravel\Services\OidcClientService;
use GeniusAuth\Laravel\Services\OidcTokenValidator;
use PHPUnit\Framework\TestCase;

class OidcClientServiceTest extends TestCase
{
    public function test_the_client_can_be_instantiated(): void
    {
        $validator = $this->createStub(OidcTokenValidator::class);
        $this->assertInstanceOf(OidcClientService::class, new OidcClientService($validator));
    }
}
