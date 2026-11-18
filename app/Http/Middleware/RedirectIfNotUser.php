<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotUser
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = 'user')
	{
	    if (!Auth::guard($guard)->check()) {
	        return redirect()->guest('user/login'); // remembers the page, so /login (routes/web.php) can send them back
	    }

	    return $next($request);
	}
}