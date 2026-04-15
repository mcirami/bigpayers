<?php

namespace App\Http\Middleware;

use Closure;
use LeadMax\TrackYourStats\System\Session;

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
            if (Session::permissions()->can($permission) == false) {
                return redirect('/dashboard');
            }
        }


        return $next($request);
    }
}
