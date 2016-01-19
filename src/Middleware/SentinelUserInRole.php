<?php

namespace Centaur\Middleware;

use Closure;
use Sentinel;

class SentinelUserInRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (!Sentinel::check()) {
            return $this->denied($request);
        }

        if (!Sentinel::inRole($role)) {
            return $this->denied($request);
        }

        return $next($request);
    }

    public function denied($request)
    {
        if ($request->ajax()) {
            return response('Unauthorized.', 401);
        } else {
            session()->flash('error', 'You do not have permission to do that.');
            return redirect()->back();
        }
    }
}
