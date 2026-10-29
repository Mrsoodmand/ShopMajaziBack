<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = 'admin')
	{
	    if (!Auth::guard($guard)->check()) {
	        // Remember the page so signing in returns to it. The redirect still goes to a path that does not exist,
	        // which keeps the real login address hidden from anyone who tries /admin.
	        if ($request->isMethod('get') && !$request->expectsJson()) {
	            redirect()->setIntendedUrl($request->fullUrl());
	        }
	        return redirect('admin/login');
	    }

	    return $next($request);
	}
}