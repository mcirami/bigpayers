<?php

namespace App\Http\Middleware;

use App\Support\RuntimeBootstrap;
use Closure;

class InitializeRuntime
{
    public function handle($request, Closure $next)
    {
        RuntimeBootstrap::boot();

        return $next($request);
    }
}
