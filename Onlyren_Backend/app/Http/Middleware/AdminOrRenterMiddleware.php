<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrRenterMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || (!$request->user()->isAdmin() && !$request->user()->isRenter())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin or Renter role required.'
            ], 403);
        }

        return $next($request);
    }
}