<?php

use App\Http\Middleware\Cors;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Configuración prioritaria de middlewares
        $middleware->priority([
            \App\Http\Middleware\Cors::class, // CORS debe ir primero
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
        $middleware->append(\App\Http\Middleware\TrustProxies::class);
        
        $middleware->trustProxies(
            ['*'], // proxies (primer parámetro)
            Request::HEADER_X_FORWARDED_FOR | // headers (segundo parámetro)
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );
        
        // Excepción para CSRF
        $middleware->validateCsrfTokens(except: [
            'https://front-production-cc8a.up.railway.app/*',
            'https://back-production-3ec7.up.railway.app/*'
        ]);
        $middleware->append(\App\Http\Middleware\Cors::class);
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();