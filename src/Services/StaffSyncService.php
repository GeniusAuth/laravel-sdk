<?php

namespace GeniusAuth\Laravel\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * Synchronizes a staff user from GeniusAuth OIDC claims into the host app's local user model.
 *
 * The host app configures:
 *  - geniusauth.staff_model  (e.g. App\Models\User)
 *  - geniusauth.staff_roles_mapping  (e.g. ['superadmin' => 'superadmin', ...])
 */
class StaffSyncService
{
    /**
     * Find or create a local user from GeniusAuth OIDC claims.
     *
     * @param array $claims  The decoded ID token claims (sub, email, name, genius_id, user_type, roles)
     * @return Model|null  The local user model, or null if not a staff user
     */
    public function syncFromClaims(array $claims): ?Model
    {
        $userType = $claims['user_type'] ?? 'end_user';

        if ($userType !== 'staff') {
            return null;
        }

        $modelClass = Config::get('geniusauth.staff_model');
        if (! $modelClass || ! class_exists($modelClass)) {
            return null;
        }

        $email = $claims['email'] ?? null;
        $geniusId = $claims['genius_id'] ?? null;
        $name = $claims['name'] ?? 'Staff';
        $roles = $claims['roles'] ?? [];

        // Map GeniusAuth roles to local roles
        $mapping = Config::get('geniusauth.staff_roles_mapping', []);
        $localRoles = array_filter(array_map(
            fn (string $role) => $mapping[$role] ?? null,
            $roles,
        ));

        // Find existing user by email or genius_id
        $user = null;
        if ($email) {
            $user = $modelClass::query()->where('email', $email)->first();
        }
        if (! $user && $geniusId) {
            $user = $modelClass::query()->where('genius_id', $geniusId)->first();
        }

        if ($user) {
            // Update existing user
            $user->update([
                'name' => $name,
                'genius_id' => $geniusId ?? $user->genius_id,
            ]);

            // Update role field if the model uses a simple 'role' column
            if (count($localRoles) > 0) {
                $this->applyRoles($user, $localRoles);
            }

            return $user->fresh();
        }

        // Create new user
        $data = [
            'name' => $name,
            'email' => $email,
            'genius_id' => $geniusId,
            'password' => \Illuminate\Support\Str::random(32),
        ];

        // Set role field if model uses simple 'role' column
        if (count($localRoles) > 0) {
            $data['role'] = $localRoles[0];
        }

        $user = $modelClass::query()->create($data);

        // If model uses Spatie HasRoles, sync roles
        if (count($localRoles) > 0) {
            $this->applyRoles($user, $localRoles);
        }

        return $user;
    }

    /**
     * Apply roles to the user model — supports both simple 'role' column and Spatie HasRoles.
     */
    private function applyRoles(Model $user, array $roles): void
    {
        // Check if model uses Spatie HasRoles trait
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles($roles);
            return;
        }

        // Simple 'role' column — set first mapped role
        if (count($roles) > 0) {
            $user->update(['role' => $roles[0]]);
        }
    }
}
