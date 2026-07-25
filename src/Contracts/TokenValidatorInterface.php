<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Contracts;

/**
 * Domain contract for validating OIDC ID tokens.
 *
 * This encapsulates the pure domain logic of token validation
 * (issuer, audience, nonce) without coupling to HTTP or cache.
 */
interface TokenValidatorInterface
{
    /**
     * Validate and decode an OIDC ID token.
     *
     * @param string $token The raw JWT id_token.
     * @param array{authorization_endpoint?: string, token_endpoint?: string, jwks_uri?: string, issuer?: string, ...} $discovery
     * @param string $nonce The expected nonce from the auth request.
     *
     * @throws \GeniusAuth\Laravel\Exceptions\OidcException
     *
     * @return object The decoded JWT claims.
     */
    public function validateIdentityToken(string $token, array $discovery, string $nonce): object;
}
