<?php
namespace App\Http\Middleware;


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class HandleCors
{
/**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Define your allowed origins
        $allowedOrigins = [
            'http://localhost:3000',          // Your Vite development server (THIS IS THE ONE CAUSING THE ERROR)
            'http://localhost:5173',          // Another common Vite port
            'http://localhost',
            'http://127.0.0.1:8000',
            'http://127.0.0.1:8080',

            'https://onlyren.noupal.pro',     // Your production frontend domain
            // Add other development/staging domains as needed
        ];

        $origin = $request->header('Origin');
        $resolvedAllowOrigin = ''; // Initialize to empty string

        // Find the exact origin if it's in our allowed list
        if ($origin && in_array($origin, $allowedOrigins)) {
            $resolvedAllowOrigin = $origin;
        }

        // --- Crucial Change Here ---
        // Access-Control-Allow-Origin MUST NOT be '*' when Access-Control-Allow-Credentials is 'true'
        // So, we only set the specific origin if it's found, otherwise we don't set it (browser will block anyway)
        // or handle it with an error.

        $headers = [
            // Set the specific origin. If no match, it will be an empty string, which browsers treat as disallowed.
            'Access-Control-Allow-Origin'      => $resolvedAllowOrigin,
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Content-Type, X-Auth-Token, Origin, Authorization, Accept, X-Requested-With, X-CSRF-TOKEN',
            'Access-Control-Allow-Credentials' => 'true', // Still essential for Sanctum/cookies
            'Access-Control-Max-Age'           => '86400',
        ];

        // Handle preflight OPTIONS requests
        if ($request->isMethod('OPTIONS')) {
            return response()->json('ok', 200, $headers);
        }

        $response = $next($request);

        // Add CORS headers to the actual response
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }

}
