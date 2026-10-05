<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // A secret invitation URL and submitted messages must not enter debug
        // pages or exception logs. Keep this policy scoped to the randi module.
        $exceptions->dontReportWhen(fn (Throwable $error) => request()->is('randi', 'randi/*'));
        $exceptions->render(fn (Throwable $error, \Illuminate\Http\Request $request) => app(\App\Services\Randi\ExceptionResponder::class)->render($error, $request));
    })->create();
