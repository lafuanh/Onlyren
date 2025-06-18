<?php
// app/Http/Controllers/Api/UserController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Message;

class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_admin' => $user->is_admin,
                'is_renter' => $user->is_renter,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $request->user()->id,
            'current_password' => 'sometimes|required_with:password|string',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $updateData = [];

        // Update name if provided
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        // Update email if provided
        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }

        // Update password if provided
        if ($request->has('password')) {
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }
            
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Get user's reservations
     */
    public function getReservations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $query = Reservation::with(['room:id,name,location,price_per_hour', 'payment'])
                ->where('user_id', $user->id);

            // Apply filters
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('start_date', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('end_date', '<=', $request->end_date);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $reservations = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $reservations->items(),
                'meta' => [
                    'current_page' => $reservations->currentPage(),
                    'last_page' => $reservations->lastPage(),
                    'per_page' => $reservations->perPage(),
                    'total' => $reservations->total(),
                    'from' => $reservations->firstItem(),
                    'to' => $reservations->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reservations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's payments
     */
    public function getPayments(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $query = Payment::with(['reservation.room:id,name,location'])
                ->whereHas('reservation', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });

            // Apply filters
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('method') && $request->method) {
                $query->where('method', $request->method);
            }

            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $payments = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $payments->items(),
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's conversations
     */
    public function getConversations(): JsonResponse
    {
        try {
            $user = Auth::user();
            $conversations = Message::getConversations($user->id);

            return response()->json([
                'success' => true,
                'data' => $conversations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch conversations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's favorite rooms (placeholder)
     */
    public function getFavorites(Request $request): JsonResponse
    {
        // Placeholder - implement when you add favorites functionality
        $favorites = collect([
            [
                'id' => 1,
                'room_id' => 3,
                'room_title' => 'Luxury Penthouse',
                'room_price' => 1200000,
                'room_location' => 'Jakarta Selatan',
                'room_image' => '/images/rooms/room3.jpg',
                'added_at' => now()->subDays(5),
            ],
            [
                'id' => 2,
                'room_id' => 5,
                'room_title' => 'Spacious Co-working Space',
                'room_price' => 800000,
                'room_location' => 'Bandung',
                'room_image' => '/images/rooms/room5.jpg',
                'added_at' => now()->subDays(10),
            ]
        ]);

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    /**
     * Add room to favorites (placeholder)
     */
    public function addToFavorites(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Placeholder - implement actual favorite adding logic
        return response()->json([
            'success' => true,
            'message' => 'Room added to favorites successfully'
        ]);
    }

    /**
     * Remove room from favorites (placeholder)
     */
    public function removeFromFavorites(Request $request, $roomId): JsonResponse
    {
        // Placeholder - implement actual favorite removal logic
        return response()->json([
            'success' => true,
            'message' => 'Room removed from favorites successfully'
        ]);
    }

    /**
     * Get user's messages/conversations (placeholder)
     */
    public function getMessages(Request $request): JsonResponse
    {
        // Placeholder - implement when you add messaging functionality
        $conversations = collect([
            [
                'id' => 1,
                'participant_name' => 'Property Owner',
                'participant_role' => 'renter',
                'room_title' => 'Cozy Studio Apartment',
                'last_message' => 'Your booking has been approved!',
                'last_message_at' => now()->subHours(1),
                'unread_count' => 1,
            ],
            [
                'id' => 2,
                'participant_name' => 'Admin Support',
                'participant_role' => 'admin',
                'room_title' => null,
                'last_message' => 'How can we help you today?',
                'last_message_at' => now()->subHours(24),
                'unread_count' => 0,
            ]
        ]);

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * Send message (placeholder)
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|integer',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Placeholder - implement actual message sending logic
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully'
        ]);
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect'
            ], 422);
        }

        // Delete all user tokens
        $user->tokens()->delete();

        // Delete user account
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }
}