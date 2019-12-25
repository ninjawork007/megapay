<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Support\Facades\Session;

class Guest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($guard == 'users')
        {
            if (!Auth::check())
            {
                return \Redirect::guest('/login');
            }
            if (Auth::user()->status == 'Inactive')
            {
                return response()->view('admin.errors.403', [], 403);
            }
        }
        elseif ($guard == 'admin')
        {
            if (!Auth::guard('admin')->check())
            {
                return redirect()->route('admin');
            }
        }
        return $next($request);
    }
}
