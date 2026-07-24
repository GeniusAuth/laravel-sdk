<?php

namespace GeniusAuth\Laravel\Http\Controllers;

use GeniusAuth\Laravel\GeniusAuthClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GeniusAuthController
{
    public function login(Request $request, GeniusAuthClient $client): RedirectResponse
    {
        return $client->redirect($request);
    }

    public function callback(Request $request, GeniusAuthClient $client): RedirectResponse
    {
        $client->handleCallback($request);

        return redirect()->intended('/');
    }

    public function logout(Request $request, GeniusAuthClient $client): RedirectResponse
    {
        $client->logout($request);

        return redirect('/');
    }
}
