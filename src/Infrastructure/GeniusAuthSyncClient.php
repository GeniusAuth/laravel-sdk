<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Infrastructure;

use GeniusAuth\Laravel\Contracts\SyncClientInterface;
use GeniusAuth\Laravel\DTOs\UserSyncDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Infrastructure client for the GeniusAuth sync API.
 * Handles HTTP communication only — no domain logic.
 */
class GeniusAuthSyncClient implements SyncClientInterface
{
    public function syncToGeniusAuth(
        string $externalUserId,
        string $phone,
        ?string $email = null,
        ?string $name = null,
        bool $phoneVerified = false,
    ): array {
        $apiKey = config('geniusauth.sync_api_key');
        $issuer = config('geniusauth.issuer');

        if (!$apiKey) {
            Log::error('GeniusAuth sync API key not configured');
            return ['success' => false, 'message' => 'GeniusAuth sync API key not configured.'];
        }

        $dto = new UserSyncDTO($externalUserId, $phone, $email, $name, $phoneVerified);

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
            ])->timeout(15)->post(rtrim($issuer, '/') . '/api/sync', $dto->toPayload());

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

        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
            ])->timeout(10)->get(rtrim($issuer, '/') . '/api/user/' . urlencode($geniusId));

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('GeniusAuth lookup exception', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
