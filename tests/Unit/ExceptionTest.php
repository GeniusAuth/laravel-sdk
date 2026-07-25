<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Tests\Unit;

use GeniusAuth\Laravel\Exceptions\GeniusAuthException;
use GeniusAuth\Laravel\Exceptions\OidcException;
use GeniusAuth\Laravel\Exceptions\SyncFailedException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function test_genius_auth_exception_is_runtime_exception(): void
    {
        $e = new GeniusAuthException('Something went wrong');

        $this->assertInstanceOf(\RuntimeException::class, $e);
        $this->assertSame('Something went wrong', $e->getMessage());
    }

    public function test_oidc_exception_extends_base(): void
    {
        $e = new OidcException('Invalid nonce');

        $this->assertInstanceOf(GeniusAuthException::class, $e);
        $this->assertSame('Invalid nonce', $e->getMessage());
    }

    public function test_sync_failed_exception_carries_status_code(): void
    {
        $e = new SyncFailedException('API returned 500', 500);

        $this->assertInstanceOf(GeniusAuthException::class, $e);
        $this->assertSame('API returned 500', $e->getMessage());
        $this->assertSame(500, $e->statusCode);
    }

    public function test_sync_failed_exception_allows_null_status_code(): void
    {
        $e = new SyncFailedException('Connection error');

        $this->assertNull($e->statusCode);
    }

    public function test_exceptions_preserve_previous(): void
    {
        $previous = new \RuntimeException('Original cause');
        $e = new OidcException('Wrapped error', $previous);

        $this->assertSame($previous, $e->getPrevious());
    }
}
