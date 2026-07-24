<?php

namespace GeniusAuth\Laravel;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IdentityLinkingService
{
    public function __construct(
        private GeniusAuthClient $client,
    ) {}

    public function handleLinkRequest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'state' => ['required', 'string', 'max:64'],
            'return_url' => ['required', 'url', 'max:2048'],
        ]);

        $request->session()->put('geniusauth.link_pending', [
            'state' => $data['state'],
            'return_url' => $data['return_url'],
        ]);

        $user = $this->client->user($request);

        if (! $user) {
            return redirect()->route('geniusauth.login');
        }

        return $this->completeLink($request);
    }

    public function completeLink(Request $request): ?RedirectResponse
    {
        $pending = $request->session()->pull('geniusauth.link_pending');

        if (! $pending) {
            return null;
        }

        $user = $this->client->user($request);

        if (! $user) {
            return redirect()->route('geniusauth.login');
        }

        $externalUserId = $user['id'];
        $email = $user['email'] ?? null;
        $name = $user['name'] ?? null;
        $phone = $user['claims']['phone_number'] ?? $user['claims']['phone'] ?? '';
        $phoneVerified = (bool) ($user['claims']['phone_number_verified'] ?? $user['claims']['phone_verified'] ?? false);

        $result = $this->syncToGeniusAuth($externalUserId, $phone, $email, $name, $phoneVerified);

        $status = $result['success'] ? 'success' : 'error';
        $returnUrl = $pending['return_url'];
        $separator = str_contains($returnUrl, '?') ? '&' : '?';

        return redirect()->away($returnUrl.$separator.http_build_query([
            'state' => $pending['state'],
            'status' => $status,
        ]));
    }

    public function syncToGeniusAuth(string $externalUserId, string $phone, ?string $email = null, ?string $name = null, bool $phoneVerified = false): array
    {
        $apiKey = config('geniusauth.sync_api_key');
        $issuer = config('geniusauth.issuer');

        if (! $apiKey) {
            Log::error('GeniusAuth sync API key not configured');
            return ['success' => false, 'message' => 'GeniusAuth sync API key not configured.'];
        }

        $payload = [
            'external_user_id' => $externalUserId,
            'phone' => $phone,
            'email' => $email,
            'name' => $name,
            'phone_verified' => $phoneVerified,
        ];

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
            ])->timeout(15)->post(rtrim($issuer, '/').'/api/sync', $payload);

            if ($response->successful()) {
                Log::info('GeniusAuth sync successful', [
                    'external_user_id' => $externalUserId,
                ]);

                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('GeniusAuth sync failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['success' => false, 'message' => 'Sync failed.'];
        } catch (\Exception $e) {
            Log::error('GeniusAuth sync exception', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Connection error to GeniusAuth.'];
        }
    }

    public function lookupByGeniusId(string $geniusId): ?array
    {
        $apiKey = config('geniusauth.sync_api_key');
        $issuer = config('geniusauth.issuer');

        if (! $apiKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
            ])->timeout(10)->get(rtrim($issuer, '/').'/api/user/'.urlencode($geniusId));

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('GeniusAuth lookup exception', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
