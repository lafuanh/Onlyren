<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Closure;

class HandleCors
{
    // public function handle(Request $request, Closure $next)
    // {
    //     if ($request->getMethod() === 'OPTIONS') {
    //         return response()->json('OK', 200, [
    //             'Access-Control-Allow-Origin' => '*',
    //             'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
    //             'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
    //         ]);
    //     }

    //     return $next($request)
    //         ->header('Access-Control-Allow-Origin', '*')
    //         ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
    //         ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    // }

}
