<?php

namespace App\Http\Middleware;

use App\Support\CurrentUserSession;
use Closure;

class AccountTypeMiddleware
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
        if (!in_array(CurrentUserSession::type(), $userTypes)) {
            abort(403, "Incorrect user type");
        }

        return $next($request);
    }
}
