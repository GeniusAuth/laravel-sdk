<?php

namespace GeniusAuth\Laravel\Http\Controllers;

use GeniusAuth\Laravel\Contracts\OidcClientInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GeniusAuthController
{
    public function login(Request $request, OidcClientInterface $client): RedirectResponse
    {
        return $client->redirect($request);
    }

    public function callback(Request $request, OidcClientInterface $client): RedirectResponse
    {
        $client->handleCallback($request);

        return redirect()->intended('/');
    }

    public function logout(Request $request, OidcClientInterface $client): RedirectResponse
    {
        $client->logout($request);

        return redirect('/');
    }
}
