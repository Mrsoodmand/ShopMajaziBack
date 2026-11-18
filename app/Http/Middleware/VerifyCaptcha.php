<?php

namespace App\Http\Middleware;

use App\Services\Captcha;
use Closure;
use Illuminate\Http\Request;

/*
 * `captcha:<action>` on a route: the request must pass App\Services\Captcha (nothing is checked while the mode is
 * off). A failure is a 422 in the storefront's envelope, with `code` for the page to react to and the current
 * captcha config in `data.captcha` (the mode may have changed since the page loaded). `error` repeats the message
 * for the old Blade pages, which read that key. A plain (non-AJAX) form post goes back with a `captcha` error instead.
 */
class VerifyCaptcha
{
    public function __construct(private Captcha $captcha)
    {
    }

    public function handle(Request $request, Closure $next, string $action = 'submit')
    {
        $Code = $this->captcha->check($request, $action);
        if ($Code === null) {
            return $next($request);
        }
        $Message = Captcha::MESSAGES[$Code];
        if (!$request->expectsJson()) { // a plain form post (partner pages): back to the form, which shows a new picture
            return back()->withErrors(['captcha' => $Message])
                ->withInput($request->except(['password', 'password_confirmation', 'captcha_token', 'captcha_id', 'captcha_answer']));
        }
        return response()->json([
            'success' => false,
            'message' => $Message,
            'code' => $Code,
            'errors' => ['captcha' => [$Message]],
            'data' => ['captcha' => $this->captcha->publicConfig()],
            'error' => [$Message],
        ], 422)->header('Cache-Control', 'no-store');
    }
}
