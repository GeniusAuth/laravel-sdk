<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Contracts;

/**
 * Infrastructure contract for communicating with the GeniusAuth sync API.
 */
interface SyncClientInterface
{
    /**
     * Sync a user to GeniusAuth via the sync API.
     *
     * @return array{success: bool, message?: string, data?: array}
     */
    public function syncToGeniusAuth(
        string $externalUserId,
        string $phone,
        ?string $email = null,
        ?string $name = null,
        bool $phoneVerified = false,
    ): array;

    /**
     * Look up a user in GeniusAuth by their Genius ID.
     *
     * @return array<string, mixed>|null
     */
    public function lookupByGeniusId(string $geniusId): ?array;
}
