<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Exceptions;

/**
 * Thrown when the OIDC flow fails (state mismatch, token exchange, ID token validation).
 */
class OidcException extends GeniusAuthException {}
