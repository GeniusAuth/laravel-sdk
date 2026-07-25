<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Contracts;

use Illuminate\Database\Eloquent\Model;

interface StaffSyncInterface
{
    /**
     * Find or create a local user from GeniusAuth OIDC claims.
     *
     * @param array<string, mixed> $claims
     * @return Model|null The local user model, or null if not a staff user.
     */
    public function syncFromClaims(array $claims): ?Model;
}
