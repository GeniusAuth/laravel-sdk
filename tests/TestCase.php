<?php

namespace GeniusAuth\Laravel\Tests;

use GeniusAuth\Laravel\Providers\GeniusAuthServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [GeniusAuthServiceProvider::class];
    }
}
