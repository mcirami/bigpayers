<?php

namespace App\Http\Middleware;

use App\Support\RuntimeBootstrap;
use Closure;

class InitializeLegacyRuntime
{
    public function handle($request, Closure $next)
    {
        RuntimeBootstrap::boot();

        return $next($request);
    }
}
