<?php

namespace GeniusAuth\Laravel\Facades;

use GeniusAuth\Laravel\GeniusAuthClient;
use Illuminate\Support\Facades\Facade;

class GeniusAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GeniusAuthClient::class;
    }
}
