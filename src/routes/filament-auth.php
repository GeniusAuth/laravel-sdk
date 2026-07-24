<?php

use GeniusAuth\Laravel\Http\Controllers\FilamentAuthController;
use Illuminate\Support\Facades\Route;

$panelPath = config('geniusauth.filament_admin_panel_path', 'admin');

Route::prefix($panelPath . '/geniusauth')
    ->middleware('web')
    ->group(function (): void {
        Route::get('/login', [FilamentAuthController::class, 'login'])
            ->name('filament.geniusauth.login');

        Route::get('/callback', [FilamentAuthController::class, 'callback'])
            ->name('filament.geniusauth.callback');
    });
