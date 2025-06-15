<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;            // <-- UNCOMMENTED
use App\Http\Middleware\RenterMiddleware;          // <-- UNCOMMENTED
use App\Http\Middleware\UserMiddleware;            // <-- UNCOMMENTED
use App\Http\Middleware\AdminOrRenterMiddleware;   // <-- UNCOMMENTED
use App\Http\Middleware\HandleCors;; // Import your new middleware

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Apply your custom CORS middleware to API routes
        $middleware->api(prepend: [
            HandleCors::class, // Use your custom middleware here
        ]);

        // ... your other middleware aliases (admin, renter, user, etc.)
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'renter' => RenterMiddleware::class, // <-- THIS WAS MISSING
            'user' => UserMiddleware::class,     // <-- THIS WAS MISSING
            'admin_or_renter' => AdminOrRenterMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();