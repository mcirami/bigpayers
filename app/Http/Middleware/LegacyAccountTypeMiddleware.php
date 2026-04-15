<?php

namespace App\Http\Middleware;

use Closure;
use LeadMax\TrackYourStats\System\Session;

class LegacyAccountTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$userTypes)
    {
        if (!in_array(Session::userType(), $userTypes)) {
            abort(403, "Incorrect user type");
        }

        return $next($request);
    }
}
