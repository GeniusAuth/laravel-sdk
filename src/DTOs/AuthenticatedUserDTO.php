<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\DTOs;

/**
 * Immutable DTO representing the authenticated user stored in session.
 */
final readonly class AuthenticatedUserDTO
{
    public function __construct(
        public string $id,
        public ?string $email,
        public ?string $name,
        public array $claims,
    ) {}

    /**
     * @param array<string, mixed> $claims
     */
    public static function fromClaims(object $claims): self
    {
        return new self(
            id: $claims->sub,
            email: $claims->email ?? null,
            name: $claims->name ?? null,
            claims: (array) $claims,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'claims' => $this->claims,
        ];
    }

    public function phone(): string
    {
        return $this->claims['phone_number'] ?? $this->claims['phone'] ?? '';
    }

    public function phoneVerified(): bool
    {
        return (bool) ($this->claims['phone_number_verified'] ?? $this->claims['phone_verified'] ?? false);
    }
}
