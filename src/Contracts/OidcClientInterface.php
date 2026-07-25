<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Contracts;

use GeniusAuth\Laravel\DTOs\AuthenticatedUserDTO;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface OidcClientInterface
{
    /**
     * Redirect the user to the GeniusAuth OIDC authorization endpoint with PKCE.
     */
    public function redirect(?Request $request = null, ?string $redirectUri = null): RedirectResponse;

    /**
     * Handle the OIDC callback, exchange the code for tokens, validate the ID token,
     * and store the authenticated user in session.
     *
     * @return array<string, mixed> The authenticated user data.
     *
     * @throws \GeniusAuth\Laravel\Exceptions\OidcException
     */
    public function handleCallback(Request $request, ?string $redirectUri = null): array;

    /**
     * Get the authenticated user from the session.
     *
     * @return array<string, mixed>|null
     */
    public function user(?Request $request = null): ?array;

    /**
     * Log the user out by clearing the session.
     */
    public function logout(?Request $request = null): void;
}
