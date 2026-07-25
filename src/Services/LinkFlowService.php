<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Services;

use GeniusAuth\Laravel\Contracts\LinkFlowInterface;
use GeniusAuth\Laravel\Contracts\OidcClientInterface;
use GeniusAuth\Laravel\Contracts\SyncClientInterface;
use GeniusAuth\Laravel\DTOs\AuthenticatedUserDTO;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Application-layer service that orchestrates the identity linking HTTP flow.
 *
 * Delegates API calls to SyncClientInterface (infrastructure)
 * and session/user lookups to OidcClientInterface.
 */
class LinkFlowService implements LinkFlowInterface
{
    public function __construct(
        private OidcClientInterface $client,
        private SyncClientInterface $syncClient,
    ) {}

    public function handleLinkRequest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'state' => ['required', 'string', 'max:64'],
            'return_url' => ['required', 'url', 'max:2048'],
        ]);

        $request->session()->put('geniusauth.link_pending', [
            'state' => $data['state'],
            'return_url' => $data['return_url'],
        ]);

        $user = $this->client->user($request);

        if (!$user) {
            return redirect()->route('geniusauth.login');
        }

        return $this->completeLink($request);
    }

    public function completeLink(Request $request): ?RedirectResponse
    {
        $pending = $request->session()->pull('geniusauth.link_pending');

        if (!$pending) {
            return null;
        }

        $user = $this->client->user($request);

        if (!$user) {
            return redirect()->route('geniusauth.login');
        }

        $userDto = new AuthenticatedUserDTO(
            id: $user['id'],
            email: $user['email'] ?? null,
            name: $user['name'] ?? null,
            claims: $user['claims'] ?? [],
        );

        $result = $this->syncClient->syncToGeniusAuth(
            $userDto->id,
            $userDto->phone(),
            $userDto->email,
            $userDto->name,
            $userDto->phoneVerified(),
        );

        $status = $result['success'] ? 'success' : 'error';
        $returnUrl = $pending['return_url'];
        $separator = str_contains($returnUrl, '?') ? '&' : '?';

        return redirect()->away($returnUrl . $separator . http_build_query([
            'state' => $pending['state'],
            'status' => $status,
        ]));
    }
}
