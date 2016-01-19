<?php

namespace Centaur\Middleware;

use Closure;
use Sentinel;

class SentinelUserHasAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        if (!Sentinel::check()) {
            return $this->denied($request);
        }

        if (!Sentinel::hasAccess($permission)) {
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
