<?php

namespace Centaur\Middleware;

use Closure;
use Sentinel;

class SentinelGuest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Sentinel::check()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
}
