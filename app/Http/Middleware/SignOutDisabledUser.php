<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * A customer disabled in admin › user (users.status = 1) is signed out on their next request, whether the session
 * was already open or came back from the remember-me cookie. Only the `user` guard is ended: an admin signed in on
 * the same browser (they share the session) stays signed in.
 */
class SignOutDisabledUser
{
    /** Shown wherever a disabled account tries to sign in. */
    const MESSAGE = 'حساب کاربری شما غیرفعال شده است. برای پیگیری، لطفاً با پشتیبانی تماس بگیرید.';

    public function handle($request, Closure $next)
    {
        $guard = Auth::guard('user');
        if ($guard->check() && (int) $guard->user()->status === 1) {
            $guard->logout();
        }

        return $next($request);
    }
}
