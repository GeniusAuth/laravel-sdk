<?php

namespace GeniusAuth\Laravel;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeniusAuthClient
{
    public function redirect(Request $request): RedirectResponse
    {
        $state = $this->randomValue(32);
        $nonce = $this->randomValue(32);
        $verifier = $this->randomValue(64);
        $request->session()->put('geniusauth.pending', compact('state', 'nonce', 'verifier'));
        $discovery = $this->discovery();

        return redirect()->away($discovery['authorization_endpoint'].'?'.http_build_query([
            'client_id' => config('geniusauth.client_id'),
            'redirect_uri' => config('geniusauth.redirect_uri'),
            'response_type' => 'code',
            'scope' => implode(' ', config('geniusauth.scopes')),
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $this->challenge($verifier),
            'code_challenge_method' => 'S256',
        ]));
    }

    public function handleCallback(Request $request): array
    {
        if ($request->filled('error')) {
            abort(401, (string) $request->query('error'));
        }
        $data = $request->validate(['code' => ['required', 'string'], 'state' => ['required', 'string']]);
        $pending = $request->session()->pull('geniusauth.pending');
        abort_unless(is_array($pending) && hash_equals($pending['state'], $data['state']), 400, 'Invalid GeniusAuth state.');
        $discovery = $this->discovery();
        $response = Http::asForm()->acceptJson()->post($discovery['token_endpoint'], array_filter([
            'grant_type' => 'authorization_code',
            'client_id' => config('geniusauth.client_id'),
            'client_secret' => config('geniusauth.client_secret'),
            'redirect_uri' => config('geniusauth.redirect_uri'),
            'code' => $data['code'],
            'code_verifier' => $pending['verifier'],
        ]));
        abort_unless($response->successful(), 401, 'GeniusAuth token exchange failed.');
        $tokens = $response->json();
        $claims = $this->validateIdentityToken($tokens['id_token'], $discovery, $pending['nonce']);
        $user = ['id' => $claims->sub, 'email' => $claims->email ?? null, 'name' => $claims->name ?? null, 'claims' => (array) $claims];
        $request->session()->put(config('geniusauth.session_key'), $user);
        $request->session()->put('geniusauth.tokens', $tokens);

        return $user;
    }

    public function user(Request $request): ?array
    {
        return $request->session()->get(config('geniusauth.session_key'));
    }

    public function logout(Request $request): void
    {
        $request->session()->forget([config('geniusauth.session_key'), 'geniusauth.tokens']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function discovery(): array
    {
        return Cache::remember('geniusauth:discovery:'.sha1(config('geniusauth.issuer')), 3600, function (): array {
            return Http::acceptJson()->get(rtrim(config('geniusauth.issuer'), '/').'/.well-known/openid-configuration')->throw()->json();
        });
    }

    private function validateIdentityToken(string $token, array $discovery, string $nonce): object
    {
        $jwks = Cache::remember('geniusauth:jwks:'.sha1($discovery['jwks_uri']), 3600, fn (): array => Http::acceptJson()->get($discovery['jwks_uri'])->throw()->json());
        $claims = JWT::decode($token, JWK::parseKeySet($jwks));
        abort_unless($claims->iss === config('geniusauth.issuer'), 401, 'Invalid GeniusAuth issuer.');
        abort_unless($this->audienceMatches($claims->aud), 401, 'Invalid GeniusAuth audience.');
        abort_unless(isset($claims->nonce) && hash_equals($nonce, $claims->nonce), 401, 'Invalid GeniusAuth nonce.');

        return $claims;
    }

    private function audienceMatches(string|array $audience): bool
    {
        return in_array(config('geniusauth.client_id'), (array) $audience, true);
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
