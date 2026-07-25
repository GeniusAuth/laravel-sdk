<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\DTOs;

/**
 * Immutable DTO for user sync requests to GeniusAuth.
 */
final readonly class UserSyncDTO
{
    public function __construct(
        public string $externalUserId,
        public string $phone,
        public ?string $email,
        public ?string $name,
        public bool $phoneVerified,
    ) {}

    public function toPayload(): array
    {
        return [
            'external_user_id' => $this->externalUserId,
            'phone' => $this->phone,
            'email' => $this->email,
            'name' => $this->name,
            'phone_verified' => $this->phoneVerified,
        ];
    }
}
