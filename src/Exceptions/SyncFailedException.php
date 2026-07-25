<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Exceptions;

/**
 * Thrown when the GeniusAuth sync API call fails.
 */
class SyncFailedException extends GeniusAuthException
{
    public function __construct(string $message, public ?int $statusCode = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
