<?php

namespace GeniusAuth\Laravel\Facades;

use GeniusAuth\Laravel\Contracts\OidcClientInterface;
use Illuminate\Support\Facades\Facade;

class GeniusAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OidcClientInterface::class;
    }
}
