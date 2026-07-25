<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\DTOs;

/**
 * Immutable DTO representing OIDC claims returned by GeniusAuth ID token.
 */
final readonly class OidcClaimsDTO
{
    public function __construct(
        public ?string $sub,
        public ?string $email,
        public ?string $name,
        public string $userType,
        public ?string $geniusId,
        public array $roles,
        public ?string $nonce,
    ) {}

    /**
     * @param array<string, mixed> $claims
     */
    public static function fromArray(array $claims): self
    {
        return new self(
            sub: $claims['sub'] ?? null,
            email: $claims['email'] ?? null,
            name: $claims['name'] ?? 'Staff',
            userType: $claims['user_type'] ?? 'end_user',
            geniusId: $claims['genius_id'] ?? null,
            roles: $claims['roles'] ?? [],
            nonce: $claims['nonce'] ?? null,
        );
    }

    public function isStaff(): bool
    {
        return $this->userType === 'staff';
    }

    public function toArray(): array
    {
        return [
            'sub' => $this->sub,
            'email' => $this->email,
            'name' => $this->name,
            'user_type' => $this->userType,
            'genius_id' => $this->geniusId,
            'roles' => $this->roles,
            'nonce' => $this->nonce,
        ];
    }
}
