<?php

namespace GeniusAuth\Laravel\Http\Controllers;

use GeniusAuth\Laravel\GeniusAuthClient;
use GeniusAuth\Laravel\IdentityLinkingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IdentityLinkController
{
    public function __construct(
        private IdentityLinkingService $linking,
        private GeniusAuthClient $client,
    ) {}

    public function link(Request $request): RedirectResponse
    {
        return $this->linking->handleLinkRequest($request);
    }

    public function callback(Request $request): RedirectResponse
    {
        $this->client->handleCallback($request);

        $redirect = $this->linking->completeLink($request);

        return $redirect ?? redirect()->intended('/');
    }
}
