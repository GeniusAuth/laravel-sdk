<?php

use GeniusAuth\Laravel\Http\Controllers\GeniusAuthController;
use GeniusAuth\Laravel\Http\Controllers\IdentityLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/auth/genius', [GeniusAuthController::class, 'login'])->name('geniusauth.login');
    Route::get('/auth/genius/callback', [GeniusAuthController::class, 'callback'])->name('geniusauth.callback');
    Route::post('/auth/genius/logout', [GeniusAuthController::class, 'logout'])->name('geniusauth.logout');

    // Identity Linking — receives redirect from GeniusAuth connected-apps page
    Route::get('/geniusauth/link', [IdentityLinkController::class, 'link'])->name('geniusauth.link');
    // Post-login callback that completes a pending link if one exists
    Route::get('/auth/genius/link/complete', [IdentityLinkController::class, 'callback'])->name('geniusauth.link.complete');
});
