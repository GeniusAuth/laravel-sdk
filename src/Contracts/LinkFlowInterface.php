<?php

declare(strict_types=1);

namespace GeniusAuth\Laravel\Contracts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Application-layer contract for the identity linking HTTP flow.
 */
interface LinkFlowInterface
{
    /**
     * Handle the incoming link request from GeniusAuth connected-apps page.
     * Stores pending link state in session and redirects to login if needed.
     */
    public function handleLinkRequest(Request $request): RedirectResponse;

    /**
     * Complete a pending identity link after OIDC callback.
     * Syncs the user to GeniusAuth and redirects back with status.
     */
    public function completeLink(Request $request): ?RedirectResponse;
}
