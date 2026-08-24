<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Tests\Feature;

use GeniusAuth\Laravel\Contracts\OidcClientInterface;
use GeniusAuth\Laravel\Contracts\SyncClientInterface;
use GeniusAuth\Laravel\Services\LinkFlowService;
use GeniusAuth\Laravel\Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FakeHostUser implements Authenticatable
{
    public function __construct(private string|int $id) {}

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): string|int
    {
        return $this->id;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): ?string
    {
        return null;
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}

class LinkFlowServiceTest extends TestCase
{
    private function requestWithPendingLink(): Request
    {
        $request = Request::create('/auth/genius/link/complete');
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('geniusauth.link_pending', [
            'state' => 'abc123',
            'return_url' => 'https://auth.geniuspay.tech/account/link/callback',
        ]);

        return $request;
    }

    public function test_it_syncs_the_host_apps_own_authenticated_user_id_not_the_geniusauth_id(): void
    {
        // The GeniusAuth-side identity has its OWN id (e.g. the GeniusAuth
        // user's sub claim) — this must NEVER be sent as external_user_id.
        $this->app->instance(OidcClientInterface::class, new class implements OidcClientInterface {
            public function redirect(?Request $request = null, ?string $redirectUri = null): RedirectResponse { return new RedirectResponse('/'); }
            public function handleCallback(Request $request, ?string $redirectUri = null): array
            {
                return [];
            }
            public function user(?Request $request = null): ?array
            {
                return [
                    'id' => 'geniusauth-sub-should-not-be-used',
                    'email' => 'merchant@test.com',
                    'name' => 'Merchant',
                    'claims' => ['phone_number' => '+2250700000000', 'phone_number_verified' => true],
                ];
            }
            public function logout(?Request $request = null): void {}
        });

        $capturedExternalUserId = null;
        $this->app->instance(SyncClientInterface::class, new class($capturedExternalUserId) implements SyncClientInterface {
            public function __construct(private mixed &$captured) {}
            public function syncToGeniusAuth(string $externalUserId, string $phone, ?string $email = null, ?string $name = null, bool $phoneVerified = false): array
            {
                $this->captured = $externalUserId;

                return ['success' => true];
            }
            public function lookupByGeniusId(string $geniusId): ?array
            {
                return null;
            }
        });

        // The host app's OWN authenticated user — GeniusPay's local account,
        // with an id completely different from the GeniusAuth-side id above.
        $this->actingAs(new FakeHostUser(42));

        $service = new LinkFlowService($this->app->make(OidcClientInterface::class), $this->app->make(SyncClientInterface::class));
        $service->completeLink($this->requestWithPendingLink());

        $this->assertSame('42', $capturedExternalUserId);
    }

    public function test_it_refuses_to_sync_when_no_host_app_user_is_authenticated(): void
    {
        $this->app->instance(OidcClientInterface::class, new class implements OidcClientInterface {
            public function redirect(?Request $request = null, ?string $redirectUri = null): RedirectResponse { return new RedirectResponse('/'); }
            public function handleCallback(Request $request, ?string $redirectUri = null): array
            {
                return [];
            }
            public function user(?Request $request = null): ?array
            {
                return ['id' => 'geniusauth-sub', 'email' => null, 'name' => null, 'claims' => []];
            }
            public function logout(?Request $request = null): void {}
        });

        $syncCalled = false;
        $this->app->instance(SyncClientInterface::class, new class($syncCalled) implements SyncClientInterface {
            public function __construct(private bool &$called) {}
            public function syncToGeniusAuth(string $externalUserId, string $phone, ?string $email = null, ?string $name = null, bool $phoneVerified = false): array
            {
                $this->called = true;

                return ['success' => true];
            }
            public function lookupByGeniusId(string $geniusId): ?array
            {
                return null;
            }
        });

        // No actingAs() — nobody is logged into the host app.
        $service = new LinkFlowService($this->app->make(OidcClientInterface::class), $this->app->make(SyncClientInterface::class));
        $response = $service->completeLink($this->requestWithPendingLink());

        $this->assertFalse($syncCalled);
        $this->assertTrue($response->isRedirect());
    }
}
