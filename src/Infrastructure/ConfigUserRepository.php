<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Infrastructure;

use GeniusAuth\Laravel\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * Default config-driven implementation of UserRepositoryInterface.
 *
 * Reads the model class and role mapping from config('geniusauth.*').
 * Host apps can override this binding with their own repository implementation.
 */
class ConfigUserRepository implements UserRepositoryInterface
{
    public function findByEmailOrGeniusId(?string $email, ?string $geniusId): ?Model
    {
        $modelClass = $this->modelClass();
        if (!$modelClass) {
            return null;
        }

        $user = null;
        if ($email) {
            $user = $modelClass::query()->where('email', $email)->first();
        }
        if (!$user && $geniusId) {
            $user = $modelClass::query()->where('genius_id', $geniusId)->first();
        }

        return $user;
    }

    public function create(array $data): Model
    {
        $modelClass = $this->modelClass();

        return $modelClass::query()->create($data);
    }

    public function getRoleMapping(): array
    {
        return Config::get('geniusauth.staff_roles_mapping', []);
    }

    private function modelClass(): ?string
    {
        $modelClass = Config::get('geniusauth.staff_model');
        if (!$modelClass || !class_exists($modelClass)) {
            return null;
        }

        return $modelClass;
    }
}
