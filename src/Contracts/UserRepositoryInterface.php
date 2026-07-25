<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Repository contract for finding and creating local users from GeniusAuth claims.
 *
 * The host app implements this to decouple StaffSyncService from the concrete model class
 * and config-driven lookup. This follows the DDD repository pattern.
 */
interface UserRepositoryInterface
{
    /**
     * Find an existing user by email or genius_id.
     *
     * @return Model|null
     */
    public function findByEmailOrGeniusId(?string $email, ?string $geniusId): ?Model;

    /**
     * Create a new local user.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Model;

    /**
     * Get the configured GeniusAuth → local role mapping.
     *
     * @return array<string, string>
     */
    public function getRoleMapping(): array;
}
