<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Exceptions;

use RuntimeException;

/**
 * Base exception for GeniusAuth SDK errors.
 */
class GeniusAuthException extends RuntimeException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
