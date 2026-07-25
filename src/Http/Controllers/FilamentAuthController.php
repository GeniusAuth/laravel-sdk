<?php

namespace GeniusAuth\Laravel\Http\Controllers;

use GeniusAuth\Laravel\Contracts\OidcClientInterface;
use GeniusAuth\Laravel\Contracts\StaffSyncInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

/**
 * Handles OIDC authentication for Filament admin panels.
 *
 * Flow:
 * 1. User clicks "Sign in with GeniusAuth" on Filament login page
 * 2. Redirected to GeniusAuth OIDC authorization endpoint
 * 3. After authentication, GeniusAuth redirects back to /admin/geniusauth/callback
 * 4. Controller exchanges code for tokens, reads claims, syncs staff user, logs in
 */
class FilamentAuthController
{
    public function __construct(
        private OidcClientInterface $client,
        private StaffSyncInterface $staffSync,
    ) {}

    /**
     * Redirect to GeniusAuth OIDC authorization endpoint.
     */
    public function login(): RedirectResponse
    {
        $redirectUri = Config::get('geniusauth.filament_redirect_uri') ?? $this->filamentCallbackUrl();

        return $this->client->redirect(null, $redirectUri);
    }

    /**
     * Handle the OIDC callback — exchange code, sync staff user, log in.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $redirectUri = Config::get('geniusauth.filament_redirect_uri') ?? $this->filamentCallbackUrl();
            $authUser = $this->client->handleCallback($request, $redirectUri);
        } catch (\Exception $e) {
            return redirect()
                ->to($this->filamentLoginUrl())
                ->withErrors(['geniusauth' => 'Authentication failed: ' . $e->getMessage()]);
        }

        // Sync the staff user into the local database using OIDC claims
        $claims = $authUser['claims'] ?? [];
        $localUser = $this->staffSync->syncFromClaims($claims);

        if (! $localUser) {
            return redirect()
                ->to($this->filamentLoginUrl())
                ->withErrors(['geniusauth' => 'Your GeniusAuth account is not a staff account.']);
        }

        // Check if user can access the panel
        if (method_exists($localUser, 'canAccessPanel')) {
            $panel = $this->resolveFilamentPanel();
            if ($panel && ! $localUser->canAccessPanel($panel)) {
                return redirect()
                    ->to($this->filamentLoginUrl())
                    ->withErrors(['geniusauth' => 'You do not have permission to access this panel.']);
            }
        }

        // Log the user in
        Auth::login($localUser, true);
        session()->regenerate();

        $panelPath = Config::get('geniusauth.filament_admin_panel_path', 'admin');

        return redirect()->intended("/{$panelPath}");
    }

    /**
     * Get the Filament login URL.
     */
    private function filamentLoginUrl(): string
    {
        $panelPath = Config::get('geniusauth.filament_admin_panel_path', 'admin');

        return "/{$panelPath}/login";
    }

    /**
     * Get the Filament OIDC callback URL.
     */
    private function filamentCallbackUrl(): string
    {
        $panelPath = Config::get('geniusauth.filament_admin_panel_path', 'admin');

        return url("/{$panelPath}/geniusauth/callback");
    }

    /**
     * Try to resolve the default Filament panel instance.
     */
    private function resolveFilamentPanel(): ?object
    {
        if (! class_exists(\Filament\Panel::class)) {
            return null;
        }

        try {
            return \Filament\Filament::getPanel('admin');
        } catch (\Exception) {
            return null;
        }
    }
}
