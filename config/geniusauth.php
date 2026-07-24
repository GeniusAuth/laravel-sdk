<?php

return [
    'issuer' => env('GENIUSAUTH_ISSUER', 'https://auth.geniuspay.tech'),
    'client_id' => env('GENIUSAUTH_CLIENT_ID'),
    'client_secret' => env('GENIUSAUTH_CLIENT_SECRET'),
    'redirect_uri' => env('GENIUSAUTH_REDIRECT_URI'),
    'scopes' => ['openid', 'profile', 'email'],
    'session_key' => 'geniusauth.user',
];
