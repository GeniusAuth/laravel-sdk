<?php

namespace GeniusAuth\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireGeniusAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has(config('geniusauth.session_key'))) {
            return redirect()->route('geniusauth.login');
        }

        return $next($request);
    }
}
