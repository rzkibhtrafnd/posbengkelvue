<?php

use App\Enums\UserRole;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() < 400 || request()->expectsJson()) {
                return $response;
            }

            $user = request()->user();
            $homeUrl = $user
                ? route($user->role === UserRole::ADMIN ? 'admin.dashboard' : 'kasir.dashboard')
                : route('login');

            return Inertia::render('Errors/Http', [
                'status' => $response->getStatusCode(),
                'homeUrl' => $homeUrl,
                'isAuthenticated' => $user !== null,
                'workshop' => Inertia::getShared('workshop', [
                    'name' => null,
                    'logo_url' => null,
                ]),
            ])->toResponse(request())->setStatusCode($response->getStatusCode());
        });
    })->create();
