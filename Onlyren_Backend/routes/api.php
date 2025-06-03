<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Auth\ReservationController;


// Public routes
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Test route to verify API is working
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Route::get('/paper', [PostController::class, 'index']);
// Route::post('/paper', [PostController::class, 'store']);
// Route::get('/posts/{id}', [PostController::class, 'show']);

Route::apiResource('posts', PostController::class);

Route::post('/test-post', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'data' => $request->all()
    ]);
});

// ... existing code ...

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('reservations', ReservationController::class);
});