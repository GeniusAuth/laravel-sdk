<?php

use GeniusAuth\Laravel\Facades\GeniusAuth;
use Illuminate\Support\Facades\Route;

Route::get('/sign-in', fn () => GeniusAuth::redirect())->name('sign-in');
Route::get('/account', fn () => GeniusAuth::user())->middleware('geniusauth');
