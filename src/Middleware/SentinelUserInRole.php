<?php

namespace Centaur\Middleware;

use Closure;
use Sentinel;

class SentinelUserInRole
{
    use TranslationHelper;

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
            $message = $this->translate('unauthorized', 'Unauthorized');
            return response()->json(['error' => $message], 401);
        } else {
            $message = $this->translate('need_permission', 'You do not have permission to do that.');
            session()->flash('error', $message);
            return redirect()->back();
        }
    }
}
