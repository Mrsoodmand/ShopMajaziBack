<?php

namespace App\Http\Controllers\AdminAuth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Concerns\LogsoutGuard;

/**
 * Admin sign-in at the hidden /admin-url-login-with-secure-path/login. The page is React (resources/admin/login.tsx)
 * and posts JSON here; a success answers with where to go next (the page the admin was sent away from, else the
 * React panel). Laravel's AuthenticatesUsers keeps the checks, session handling and throttling.
 */
class LoginController extends Controller
{
    use AuthenticatesUsers, LogsoutGuard {
        LogsoutGuard::logout insteadof AuthenticatesUsers;
    }

    const LOGIN_PATH = '/admin-url-login-with-secure-path/login';

    public $redirectTo = '/admin/app';

    public function __construct()
    {
        $this->middleware('admin.guest', ['except' => 'logout']);
    }

    public function showLoginForm()
    {
        return view('admin-react.login', [
            'boot' => [
                'action' => route('admin-login'),
                'csrfToken' => csrf_token(),
                'email' => null,
            ],
        ]);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate(
            ['email' => 'required|string', 'password' => 'required|string'],
            ['email.required' => 'ایمیل را وارد کنید.', 'password.required' => 'کلمه عبور را وارد کنید.'],
        );
    }

    /** The JSON form gets where to go next; a plain form post keeps Laravel's redirect. */
    protected function authenticated(Request $request, $admin)
    {
        if ($request->wantsJson()) {
            return response()->json(['redirect' => redirect()->intended($this->redirectPath())->getTargetUrl()]);
        }
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages(['email' => ['ایمیل یا کلمه عبور درست نیست.']]);
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn($this->throttleKey($request));
        $minutes = max(1, (int) ceil($seconds / 60));

        throw ValidationException::withMessages([
            'email' => ["چند بار پشت سر هم ورود ناموفق بود. {$minutes} دقیقه دیگر دوباره امتحان کنید."],
        ])->status(429);
    }

    /** After logging out, back to this login page rather than the storefront. */
    public function logoutToPath()
    {
        return self::LOGIN_PATH;
    }

    protected function guard()
    {
        return Auth::guard('admin');
    }
}
