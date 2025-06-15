<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RenterController;
// use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

    // Room routes
    Route::prefix('rooms')->group(function () {
        Route::get('/', [RoomController::class, 'index']);
        Route::get('/featured', [RoomController::class, 'featured']);
        Route::get('/{id}', [RoomController::class, 'show']);
        Route::get('/{id}/availability', [RoomController::class, 'checkAvailability']);
    });

// Search routes
Route::get('/search', [SearchController::class, 'search']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Common authenticated routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    // User-specific routes
    Route::middleware(['user'])->prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'getProfile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::get('/dashboard', [UserController::class, 'getDashboard']);
        
        // User reservations
        Route::get('/reservations', [ReservationController::class, 'index']);
        Route::get('/reservations/statistics', [ReservationController::class, 'statistics']);
        
        // User payments
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/statistics', [PaymentController::class, 'statistics']);
    });

    // Reservation routes (available to all authenticated users)
    Route::prefix('reservations')->group(function () {
        Route::post('/', [ReservationController::class, 'store']);
        Route::get('/{id}', [ReservationController::class, 'show']);
        Route::put('/{id}', [ReservationController::class, 'update']);
        Route::delete('/{id}', [ReservationController::class, 'destroy']);
    });

    // Payment routes (available to all authenticated users)
    Route::prefix('payments')->group(function () {
        Route::get('/{id}', [PaymentController::class, 'show']);
        Route::post('/reservation/{reservationId}', [PaymentController::class, 'processPayment']);
        Route::get('/methods', [PaymentController::class, 'getPaymentMethods']);
        Route::get('/{id}/receipt', [PaymentController::class, 'receipt']);
    });

    // Renter-specific routes
     // Renter-specific routes
    Route::middleware(['renter'])->prefix('renter')->group(function () {
        Route::get('/profile', [RenterController::class, 'getProfile']);
        Route::put('/profile', [RenterController::class, 'updateProfile']);

        // Renter Room Management
        Route::prefix('rooms')->group(function () {
            Route::get('/', [RenterController::class, 'getRooms']);
            Route::post('/', [RenterController::class, 'store']);
            Route::get('/{id}', [RenterController::class, 'getRoom']);
            // [FIX] Mengganti Route::post menjadi Route::put untuk update
            // Ini akan menangani request PUT asli dari frontend
            Route::put('/{id}', [RenterController::class, 'update']);
            // [FIX] Menambahkan kembali Route::post untuk menangani update via FormData (_method='PUT')
            Route::post('/{id}', [RenterController::class, 'update']);
            Route::delete('/{id}', [RenterController::class, 'destroy']);
        });

        // Renter Reservation Management
        Route::prefix('reservations')->group(function () {
            Route::get('/', [RenterController::class, 'getReservations']);
            Route::put('/{id}/approve', [RenterController::class, 'approveReservation']);
            Route::put('/{id}/reject', [RenterController::class, 'rejectReservation']);
            Route::put('/{id}/complete', [RenterController::class, 'completeReservation']);
        });
        
        // Renter payment management
        Route::prefix('payments')->group(function () {
            Route::get('/', [RenterController::class, 'getPayments']);
            Route::put('/{id}/confirm', [PaymentController::class, 'confirmPayment']);
        });
        
        // Communication
        Route::get('/conversations', [RenterController::class, 'getConversations']);
        Route::post('/messages', [RenterController::class, 'sendMessage']);
    });

    
});


// // Admin-specific routes
    // Route::middleware(['admin'])->prefix('admin')->group(function () {
    //     Route::get('/dashboard', [AdminController::class, 'getDashboard']);
        
    //      // User management
    //     Route::prefix('users')->group(function () {
    //         Route::get('/', [AdminController::class, 'getUsers']);
    //         Route::get('/{id}', [AdminController::class, 'getUser']);
    //         Route::put('/{id}', [AdminController::class, 'updateUser']);
    //         Route::delete('/{id}', [AdminController::class, 'deleteUser']);
    //     });

    //     // Room management
    //     Route::prefix('rooms')->group(function () {
    //         Route::get('/', [AdminController::class, 'getRooms']);
    //         Route::get('/{id}', [AdminController::class, 'getRoom']);
    //         Route::put('/{id}', [AdminController::class, 'updateRoom']);
    //         Route::delete('/{id}', [AdminController::class, 'deleteRoom']);
    //     });

    //     // Reservation management
    //     Route::prefix('reservations')->group(function () {
    //         Route::get('/', [AdminController::class, 'getReservations']);
    //         Route::get('/{id}', [AdminController::class, 'getReservation']);
    //         Route::put('/{id}/approve', [AdminController::class, 'approveReservation']);
    //         Route::put('/{id}/reject', [AdminController::class, 'rejectReservation']);
    //         Route::put('/{id}/complete', [AdminController::class, 'completeReservation']);
    //         Route::delete('/{id}', [AdminController::class, 'deleteReservation']);
    //     });

    //     // Payment management
    //     Route::prefix('payments')->group(function () {
    //         Route::get('/', [AdminController::class, 'getPayments']);
    //         Route::get('/{id}', [AdminController::class, 'getPayment']);
    //         Route::put('/{id}/confirm', [AdminController::class, 'confirmPayment']);
    //         Route::delete('/{id}', [AdminController::class, 'deletePayment']);
    //     });

    //     // Reporting and statistics
    //     Route::prefix('reports')->group(function () {
    //         Route::get('/reservations', [AdminController::class, 'getReservationReport']);
    //         Route::get('/payments', [AdminController::class, 'getPaymentReport']);
    //     });
    // });