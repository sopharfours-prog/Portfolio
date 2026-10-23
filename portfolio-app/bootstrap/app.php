<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackPageView;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'locale' => SetLocale::class,
            'track' => TrackPageView::class,
        ]);
        // Session-authenticated admin pages can call /api with their cookie (Sanctum SPA mode)
        $middleware->web(append: [SecurityHeaders::class]);
        // Apple returns the sign-in result as a cross-site POST; it is verified by its own nonce cookie instead
        $middleware->validateCsrfTokens(except: ['auth/apple/callback']);
        $middleware->api(prepend: [ForceJsonResponse::class]);
        $middleware->statefulApi();
        // Admin area and public visitor accounts each send people to their own pages
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*') ? route('admin.login') : route('member.login'));
        $middleware->redirectUsersTo(fn (Request $request) => $request->is('admin', 'admin/*') || $request->user('web') ? route('admin.dashboard') : route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
