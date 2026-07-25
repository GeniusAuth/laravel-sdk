<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Services;

use GeniusAuth\Laravel\Contracts\OidcClientInterface;
use GeniusAuth\Laravel\Contracts\TokenValidatorInterface;
use GeniusAuth\Laravel\DTOs\AuthenticatedUserDTO;
use GeniusAuth\Laravel\Exceptions\OidcException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Infrastructure service for the GeniusAuth OIDC Authorization Code + PKCE flow.
 *
 * Delegates ID token validation to TokenValidatorInterface (domain layer).
 * Handles HTTP discovery, token exchange, and session management.
 */
class OidcClientService implements OidcClientInterface
{
    public function __construct(
        private TokenValidatorInterface $tokenValidator,
    ) {}

    public function redirect(?Request $request = null, ?string $redirectUri = null): RedirectResponse
    {
        $request ??= request();
        $state = $this->randomValue(32);
        $nonce = $this->randomValue(32);
        $verifier = $this->randomValue(64);
        $request->session()->put('geniusauth.pending', compact('state', 'nonce', 'verifier'));
        $discovery = $this->discovery();

        return redirect()->away($discovery['authorization_endpoint'] . '?' . http_build_query([
            'client_id' => config('geniusauth.client_id'),
            'redirect_uri' => $redirectUri ?? config('geniusauth.redirect_uri'),
            'response_type' => 'code',
            'scope' => implode(' ', config('geniusauth.scopes')),
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $this->challenge($verifier),
            'code_challenge_method' => 'S256',
        ]));
    }

    public function handleCallback(Request $request, ?string $redirectUri = null): array
    {
        if ($request->filled('error')) {
            throw new OidcException((string) $request->query('error'));
        }

        $data = $request->validate(['code' => ['required', 'string'], 'state' => ['required', 'string']]);
        $pending = $request->session()->pull('geniusauth.pending');

        if (!is_array($pending) || !hash_equals($pending['state'], $data['state'])) {
            throw new OidcException('Invalid GeniusAuth state.');
        }

        $discovery = $this->discovery();
        $response = Http::asForm()->acceptJson()->post($discovery['token_endpoint'], array_filter([
            'grant_type' => 'authorization_code',
            'client_id' => config('geniusauth.client_id'),
            'client_secret' => config('geniusauth.client_secret'),
            'redirect_uri' => $redirectUri ?? config('geniusauth.redirect_uri'),
            'code' => $data['code'],
            'code_verifier' => $pending['verifier'],
        ]));

        if (!$response->successful()) {
            throw new OidcException('GeniusAuth token exchange failed.');
        }

        $tokens = $response->json();
        $claims = $this->tokenValidator->validateIdentityToken(
            $tokens['id_token'],
            $discovery,
            $pending['nonce'],
        );

        $user = AuthenticatedUserDTO::fromClaims($claims);
        $userArray = $user->toArray();

        $request->session()->put(config('geniusauth.session_key'), $userArray);
        $request->session()->put('geniusauth.tokens', $tokens);

        return $userArray;
    }

    public function user(?Request $request = null): ?array
    {
        return ($request ?? request())->session()->get(config('geniusauth.session_key'));
    }

    public function logout(?Request $request = null): void
    {
        $request ??= request();
        $request->session()->forget([config('geniusauth.session_key'), 'geniusauth.tokens']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function discovery(): array
    {
        return Cache::remember('geniusauth:discovery:' . sha1(config('geniusauth.issuer')), 3600, function (): array {
            return Http::acceptJson()
                ->get(rtrim(config('geniusauth.issuer'), '/') . '/.well-known/openid-configuration')
                ->throw()
                ->json();
        });
    }

    private function challenge(string $verifier): string
    {
        return $this->base64Url(hash('sha256', $verifier, true));
    }

    private function randomValue(int $length): string
    {
        return $this->base64Url(random_bytes($length));
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
