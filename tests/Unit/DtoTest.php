<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Tests\Unit;

use GeniusAuth\Laravel\DTOs\AuthenticatedUserDTO;
use GeniusAuth\Laravel\DTOs\OidcClaimsDTO;
use GeniusAuth\Laravel\DTOs\UserSyncDTO;
use PHPUnit\Framework\TestCase;

class DtoTest extends TestCase
{
    public function test_oidc_claims_dto_from_array_and_is_staff(): void
    {
        $dto = OidcClaimsDTO::fromArray([
            'sub' => 'usr_123',
            'email' => 'staff@test.com',
            'name' => 'Test Staff',
            'user_type' => 'staff',
            'genius_id' => 'gen_abc',
            'roles' => ['admin', 'support'],
            'nonce' => 'nonce_xyz',
        ]);

        $this->assertSame('usr_123', $dto->sub);
        $this->assertSame('staff@test.com', $dto->email);
        $this->assertSame('Test Staff', $dto->name);
        $this->assertSame('staff', $dto->userType);
        $this->assertSame('gen_abc', $dto->geniusId);
        $this->assertSame(['admin', 'support'], $dto->roles);
        $this->assertSame('nonce_xyz', $dto->nonce);
        $this->assertTrue($dto->isStaff());
    }

    public function test_oidc_claims_dto_defaults_to_end_user(): void
    {
        $dto = OidcClaimsDTO::fromArray([]);

        $this->assertSame('end_user', $dto->userType);
        $this->assertFalse($dto->isStaff());
        $this->assertSame('Staff', $dto->name);
    }

    public function test_oidc_claims_dto_to_array_round_trip(): void
    {
        $original = [
            'sub' => 'usr_456',
            'email' => 'alice@test.com',
            'name' => 'Alice',
            'user_type' => 'staff',
            'genius_id' => 'gen_def',
            'roles' => ['developer'],
            'nonce' => 'n123',
        ];

        $dto = OidcClaimsDTO::fromArray($original);
        $this->assertSame($original, $dto->toArray());
    }

    public function test_authenticated_user_dto_from_claims_object(): void
    {
        $claims = (object) [
            'sub' => 'usr_789',
            'email' => 'bob@test.com',
            'name' => 'Bob',
            'phone_number' => '+2250700000000',
            'phone_number_verified' => true,
            'custom_claim' => 'extra',
        ];

        $dto = AuthenticatedUserDTO::fromClaims($claims);

        $this->assertSame('usr_789', $dto->id);
        $this->assertSame('bob@test.com', $dto->email);
        $this->assertSame('Bob', $dto->name);
        $this->assertSame('+2250700000000', $dto->phone());
        $this->assertTrue($dto->phoneVerified());
    }

    public function test_authenticated_user_dto_phone_fallbacks(): void
    {
        $dto = new AuthenticatedUserDTO(
            id: 'usr_001',
            email: null,
            name: null,
            claims: ['phone' => '+123', 'phone_verified' => true],
        );

        $this->assertSame('+123', $dto->phone());
        $this->assertTrue($dto->phoneVerified());
    }

    public function test_authenticated_user_dto_phone_returns_empty_when_missing(): void
    {
        $dto = new AuthenticatedUserDTO(
            id: 'usr_002',
            email: null,
            name: null,
            claims: [],
        );

        $this->assertSame('', $dto->phone());
        $this->assertFalse($dto->phoneVerified());
    }

    public function test_authenticated_user_dto_to_array(): void
    {
        $dto = new AuthenticatedUserDTO(
            id: 'usr_003',
            email: 'carol@test.com',
            name: 'Carol',
            claims: ['custom' => 'value'],
        );

        $this->assertSame([
            'id' => 'usr_003',
            'email' => 'carol@test.com',
            'name' => 'Carol',
            'claims' => ['custom' => 'value'],
        ], $dto->toArray());
    }

    public function test_user_sync_dto_to_payload(): void
    {
        $dto = new UserSyncDTO(
            externalUserId: 'ext_001',
            phone: '+2250700000001',
            email: 'sync@test.com',
            name: 'Sync User',
            phoneVerified: true,
        );

        $this->assertSame([
            'external_user_id' => 'ext_001',
            'phone' => '+2250700000001',
            'email' => 'sync@test.com',
            'name' => 'Sync User',
            'phone_verified' => true,
        ], $dto->toPayload());
    }

    public function test_user_sync_dto_to_payload_with_nulls(): void
    {
        $dto = new UserSyncDTO(
            externalUserId: 'ext_002',
            phone: '+2250700000002',
            email: null,
            name: null,
            phoneVerified: false,
        );

        $payload = $dto->toPayload();

        $this->assertNull($payload['email']);
        $this->assertNull($payload['name']);
        $this->assertFalse($payload['phone_verified']);
    }
}
