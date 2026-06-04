<?php

namespace App\Http\Middleware;

use App\Support\CurrentUserSession;
use Closure;

class LegacyPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$permissions)
    {
        foreach ($permissions as $permission) {
            if (CurrentUserSession::permissions()->can($permission) == false) {
                return redirect('/dashboard');
            }
        }


        return $next($request);
    }
}
