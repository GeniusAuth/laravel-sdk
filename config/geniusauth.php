<?php

return [
    'issuer' => env('GENIUSAUTH_ISSUER', 'https://auth.geniuspay.tech'),
    'client_id' => env('GENIUSAUTH_CLIENT_ID'),
    'client_secret' => env('GENIUSAUTH_CLIENT_SECRET'),
    'redirect_uri' => env('GENIUSAUTH_REDIRECT_URI'),
    'scopes' => ['openid', 'profile', 'email'],
    'session_key' => 'geniusauth.user',

    // Identity Linking — API key for sync endpoint on GeniusAuth
    'sync_api_key' => env('GENIUSAUTH_SYNC_API_KEY'),

    // Identity Linking — URL on this app that receives the redirect from GeniusAuth
    // Defaults to the SDK's built-in route. Override if you have a custom controller.
    'link_url' => env('GENIUSAUTH_LINK_URL'),

    // Staff SSO — Enable Filament admin authentication via GeniusAuth
    'filament_auth_enabled' => env('GENIUSAUTH_FILAMENT_AUTH', false),

    // Staff SSO — Redirect URI for the Filament OIDC callback
    // Must be registered in GeniusAuth OAuth client's redirect_uris
    'filament_redirect_uri' => env('GENIUSAUTH_FILAMENT_REDIRECT_URI'),

    // Staff SSO — Filament admin panel path (e.g. 'admin')
    'filament_admin_panel_path' => env('GENIUSAUTH_FILAMENT_PANEL_PATH', 'admin'),

    // Staff SSO — The local User model class used by the host app
    'staff_model' => env('GENIUSAUTH_STAFF_MODEL', 'App\\Models\\User'),

    // Staff SSO — Mapping of GeniusAuth roles to local app roles
    // Each app configures this to match its own role system
    'staff_roles_mapping' => [
        // 'superadmin' => 'superadmin',
        // 'admin' => 'admin',
        // 'support' => 'support_it',
    ],
];
