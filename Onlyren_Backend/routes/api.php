<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RenterController;
// use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Common authenticated routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    
    // User-specific routes
    Route::middleware(['user'])->prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'getProfile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        // Add more user routes as needed
    });
    
    // Renter-specific routes
    Route::middleware(['renter'])->prefix('renter')->group(function () {
        Route::get('/profile', [RenterController::class, 'getProfile']);
        Route::put('/profile', [RenterController::class, 'updateProfile']);
        // Route::get('/rooms', [RenterController::class, 'getRooms']);
        // Route::post('/rooms', [RenterController::class, 'createRoom']);
        // Route::get('/rooms/{id}', [RenterController::class, 'getRoom']);
        // Route::put('/rooms/{id}', [RenterController::class, 'updateRoom']);
        // Route::delete('/rooms/{id}', [RenterController::class, 'deleteRoom']);
        // Route::get('/orders', [RenterController::class, 'getOrders']);
        // Route::put('/orders/{id}/approve', [RenterController::class, 'approveOrder']);
        // Route::put('/orders/{id}/reject', [RenterController::class, 'rejectOrder']);
        // Route::put('/orders/{id}/complete', [RenterController::class, 'completeOrder']);
        // Route::get('/conversations', [RenterController::class, 'getConversations']);
        // Route::post('/messages', [RenterController::class, 'sendMessage']);
    });
    
    // Admin-specific routes
    // Route::middleware(['admin'])->prefix('admin')->group(function () {
    //     Route::get('/dashboard', [AdminController::class, 'getDashboard']);
    //     Route::get('/users', [AdminController::class, 'getUsers']);
    //     Route::get('/users/{id}', [AdminController::class, 'getUser']);
    //     Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    //     Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    //     Route::get('/rooms', [AdminController::class, 'getRooms']);
    //     Route::get('/orders', [AdminController::class, 'getOrders']);
    //     Route::get('/reports', [AdminController::class, 'getReports']);
    // });
});

// Public API routes (no authentication required)
// Route::get('/rooms', [App\Http\Controllers\Api\RoomController::class, 'index']);
// Route::get('/rooms/{id}', [App\Http\Controllers\Api\RoomController::class, 'show']);
// Route::get('/search', [App\Http\Controllers\Api\SearchController::class, 'search']);