<?php

use GeniusAuth\Laravel\Http\Controllers\GeniusAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/auth/genius', [GeniusAuthController::class, 'login'])->name('geniusauth.login');
    Route::get('/auth/genius/callback', [GeniusAuthController::class, 'callback'])->name('geniusauth.callback');
    Route::post('/auth/genius/logout', [GeniusAuthController::class, 'logout'])->name('geniusauth.logout');
});
