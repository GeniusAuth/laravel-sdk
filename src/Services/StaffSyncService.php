<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Services;

use GeniusAuth\Laravel\Contracts\StaffSyncInterface;
use GeniusAuth\Laravel\Contracts\UserRepositoryInterface;
use GeniusAuth\Laravel\DTOs\OidcClaimsDTO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Synchronizes a staff user from GeniusAuth OIDC claims into the host app's local user model.
 *
 * Delegates persistence to UserRepositoryInterface (infrastructure) so the domain
 * logic is decoupled from the concrete model class and config.
 */
class StaffSyncService implements StaffSyncInterface
{
    public function __construct(
        private UserRepositoryInterface $repository,
    ) {}

    /**
     * Find or create a local user from GeniusAuth OIDC claims.
     *
     * @param array<string, mixed> $claims
     */
    public function syncFromClaims(array $claims): ?Model
    {
        $dto = OidcClaimsDTO::fromArray($claims);

        if (!$dto->isStaff()) {
            return null;
        }

        $localRoles = $this->mapRoles($dto->roles);

        $user = $this->repository->findByEmailOrGeniusId($dto->email, $dto->geniusId);

        if ($user) {
            $user->update([
                'name' => $dto->name,
                'genius_id' => $dto->geniusId ?? $user->genius_id,
            ]);

            if (count($localRoles) > 0) {
                $this->applyRoles($user, $localRoles);
            }

            return $user->fresh();
        }

        $data = [
            'name' => $dto->name,
            'email' => $dto->email,
            'genius_id' => $dto->geniusId,
            'password' => Str::random(32),
        ];

        if (count($localRoles) > 0) {
            $data['role'] = $localRoles[0];
        }

        $user = $this->repository->create($data);

        if (count($localRoles) > 0) {
            $this->applyRoles($user, $localRoles);
        }

        return $user;
    }

    /**
     * Map GeniusAuth roles to local roles via the repository's mapping.
     *
     * @param array<int, string> $roles
     * @return array<int, string>
     */
    private function mapRoles(array $roles): array
    {
        $mapping = $this->repository->getRoleMapping();

        return array_filter(array_map(
            fn (string $role) => $mapping[$role] ?? null,
            $roles,
        ));
    }

    /**
     * Apply roles to the user model — supports both simple 'role' column and Spatie HasRoles.
     */
    private function applyRoles(Model $user, array $roles): void
    {
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles($roles);
            return;
        }

        if (count($roles) > 0) {
            $user->update(['role' => $roles[0]]);
        }
    }
}
