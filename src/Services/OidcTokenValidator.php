<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GeniusAuth\Laravel\Contracts\TokenValidatorInterface;
use GeniusAuth\Laravel\Exceptions\OidcException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Domain service for validating OIDC ID tokens.
 *
 * Contains the pure validation logic: JWKS fetch + JWT decode + issuer/audience/nonce checks.
 * Infrastructure concerns (HTTP, Cache) are isolated here so the domain rules are testable.
 */
class OidcTokenValidator implements TokenValidatorInterface
{
    public function validateIdentityToken(string $token, array $discovery, string $nonce): object
    {
        $jwks = $this->fetchJwks($discovery['jwks_uri']);
        $claims = JWT::decode($token, JWK::parseKeySet($jwks));

        $this->assertIssuerMatches($claims);
        $this->assertAudienceMatches($claims);
        $this->assertNonceMatches($claims, $nonce);

        return $claims;
    }

    private function fetchJwks(string $jwksUri): array
    {
        return Cache::remember(
            'geniusauth:jwks:' . sha1($jwksUri),
            3600,
            fn (): array => Http::acceptJson()->get($jwksUri)->throw()->json(),
        );
    }

    private function assertIssuerMatches(object $claims): void
    {
        if ($claims->iss !== config('geniusauth.issuer')) {
            throw new OidcException('Invalid GeniusAuth issuer.');
        }
    }

    private function assertAudienceMatches(object $claims): void
    {
        $clientId = config('geniusauth.client_id');

        if (!in_array($clientId, (array) $claims->aud, true)) {
            throw new OidcException('Invalid GeniusAuth audience.');
        }
    }

    private function assertNonceMatches(object $claims, string $expectedNonce): void
    {
        if (!isset($claims->nonce) || !hash_equals($expectedNonce, $claims->nonce)) {
            throw new OidcException('Invalid GeniusAuth nonce.');
        }
    }
}
