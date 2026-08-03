<?php

namespace App\Http\Middleware;

use App\Support\UserDomain\User;
use Closure;

class UserSessionAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = new User();
        if ($user->verify_login_session()) {
            return $next($request);
        } else {
            return redirect('/login');
        }
    }
}
