<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Add a simple test route to debug
Route::get('/test-web', function () {
    return response()->json([
        'message' => 'Web routes are working',
        'timestamp' => now()
    ]);
});