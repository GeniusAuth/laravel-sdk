<?php

namespace GeniusAuth\Laravel\Facades;

use GeniusAuth\Laravel\IdentityLinkingService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\RedirectResponse handleLinkRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Http\RedirectResponse|null completeLink(\Illuminate\Http\Request $request)
 * @method static array syncToGeniusAuth(string $externalUserId, string $phone, ?string $email = null, ?string $name = null, bool $phoneVerified = false)
 * @method static array|null lookupByGeniusId(string $geniusId)
 */
class GeniusAuthLink extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IdentityLinkingService::class;
    }
}
