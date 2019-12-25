<?php

namespace PragmaRX\Google2FALaravel;

use Closure;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class Middleware
{
    public function handle($request, Closure $next)
    {
    	// dd($request);
        $authenticator = app(Authenticator::class)->boot($request);
    	//dd($authenticator->isAuthenticated());

        if ($authenticator->isAuthenticated())
        {
            return $next($request);
        }

        return $authenticator->makeRequestOneTimePasswordResponse();
    }
}
