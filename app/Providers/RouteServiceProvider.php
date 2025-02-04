<?php

namespace App\Providers;

use App\Http\Middleware\AcceptedProvider;
use App\Http\Middleware\AccountVerifiedMiddleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/manage';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware(['api' , 'auth:sanctum' , 'role:user' , AccountVerifiedMiddleware::class])
                ->prefix('api/me')
                ->group(base_path('routes/API/User.php'));

            Route::middleware(['api' , 'auth:sanctum' , 'role:provider' , AccountVerifiedMiddleware::class , AcceptedProvider::class])
                ->prefix('api/provider')
                ->as('provider.')
                ->group(base_path('routes/API/Provider.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));


            Route::middleware(['web', 'auth' , 'role:admin'])
                ->prefix('manage')
                ->as('manage.')
                ->group(base_path('routes/manage.php'));
        });
    }
}
