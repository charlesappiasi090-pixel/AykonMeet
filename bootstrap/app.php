<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SessionTimeout;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->trimStrings(except: [
            'payload.sdp',
        ]);
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
        $middleware->redirectUsersTo('/dashboard');
        $middleware->replace(Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, VerifyCsrfToken::class);
        $middleware->web(append: [SessionTimeout::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
