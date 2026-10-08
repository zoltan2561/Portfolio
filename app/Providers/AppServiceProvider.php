<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (['randi-login' => 5, 'randi-create' => 5, 'randi-response' => 30, 'randi-admin' => 20] as $name => $attempts) {
            RateLimiter::for($name, function (Request $request) use ($attempts, $name) {
                $identity = hash_hmac('sha256', $request->ip() ?? 'unknown', (string) config('app.key'));

                return Limit::perMinute($attempts)->by($name.':'.$identity)->response(function (Request $request, array $headers) {
                    $message = 'Most túl sok próbálkozás érkezett. Várj egy percet, és próbáld újra.';

                    return $request->expectsJson()
                        ? response()->json(['ok' => false, 'message' => $message], 429, $headers)
                        : response()->view('randi.status', compact('message'), 429, $headers);
                });
            });
        }

        RateLimiter::for('portfolio-contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('portfolio-statistics-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
