<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/api.php'));

            Route::prefix('admin')
                ->middleware(['web', 'admin', 'auth:admin'])
                ->namespace($this->namespace)
                ->namespace('App\Http\Controllers')
                ->as('admin.')
                ->group(base_path('routes/admin.php'));

            Route::prefix('user')
                ->middleware(['web', 'user', 'auth:user'])
                ->namespace($this->namespace)
                ->namespace('App\Http\Controllers')
                ->as('user.')
                ->group(base_path('routes/user.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        /*
         * Next.js storefront account API (routes/web.php › api/v1/storefront/account). A plain `throttle:N,1` keys
         * every route on the same IP counter, so polling an order (12 a minute) used up the 10-a-minute buy limit and
         * customers sharing an IP (mobile carriers, offices) limited each other. Each kind of request has its own
         * counter here, per signed-in customer (the `user` guard), or per IP for guests.
         */
        $Who = fn (Request $request) => ($Id = \Illuminate\Support\Facades\Auth::guard('user')->id()) ? "u$Id" : 'ip' . $request->ip();
        RateLimiter::for('storefront-auth', fn (Request $request) => Limit::perMinute(15)->by('ip' . $request->ip()));
        RateLimiter::for('storefront-buy', fn (Request $request) => Limit::perMinute(10)->by($Who($request)));
        // Per order: a page polls each order it shows on its own.
        RateLimiter::for('storefront-poll', fn (Request $request) => Limit::perMinute(40)->by($Who($request) . '|' . $request->path()));
        RateLimiter::for('storefront-read', fn (Request $request) => Limit::perMinute(60)->by($Who($request)));
        RateLimiter::for('storefront-write', fn (Request $request) => Limit::perMinute(15)->by($Who($request)));
    }
}
