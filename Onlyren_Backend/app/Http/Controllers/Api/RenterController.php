<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RenterController extends Controller
{
    /**
     * Get renter profile
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
                'is_renter' => $user->is_renter,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * Update renter profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->update($request->only(['name', 'email']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Get renter's rooms
     */
    public function getRooms(Request $request): JsonResponse
    {
        // This is a placeholder - you'll need to create Room model and relationships
        $rooms = collect([
            [
                'id' => 1,
                'title' => 'Cozy Studio Apartment',
                'description' => 'Perfect for students',
                'price' => 500000,
                'status' => 'available',
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'title' => 'Spacious 2BR Apartment',
                'description' => 'Great for small families',
                'price' => 800000,
                'status' => 'occupied',
                'created_at' => now(),
            ]
        ]);

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    /**
     * Create new room
     */
    public function createRoom(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'address' => 'required|string',
            'city' => 'required|string',
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Placeholder response - implement actual room creation
        $room = [
            'id' => rand(1000, 9999),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'address' => $request->address,
            'city' => $request->city,
            'type' => $request->type,
            'owner_id' => $request->user()->id,
            'status' => 'available',
            'created_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Room created successfully',
            'data' => $room
        ], 201);
    }

    /**
     * Get specific room
     */
    public function getRoom(Request $request, $id): JsonResponse
    {
        // Placeholder response
        $room = [
            'id' => $id,
            'title' => 'Sample Room',
            'description' => 'Sample description',
            'price' => 600000,
            'status' => 'available',
            'owner_id' => $request->user()->id,
        ];

        return response()->json([
            'success' => true,
            'data' => $room
        ]);
    }

    /**
     * Update room
     */
    public function updateRoom(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string',
            'type' => 'sometimes|string',
            'status' => 'sometimes|in:available,occupied,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Placeholder response
        return response()->json([
            'success' => true,
            'message' => 'Room updated successfully'
        ]);
    }

    /**
     * Delete room
     */
    public function deleteRoom(Request $request, $id): JsonResponse
    {
        // Placeholder response
        return response()->json([
            'success' => true,
            'message' => 'Room deleted successfully'
        ]);
    }

    /**
     * Get renter's orders/bookings
     */
    public function getOrders(Request $request): JsonResponse
    {
        $orders = collect([
            [
                'id' => 1,
                'room_title' => 'Cozy Studio Apartment',
                'tenant_name' => 'John Doe',
                'tenant_email' => 'john@example.com',
                'start_date' => '2024-07-01',
                'end_date' => '2024-12-31',
                'total_amount' => 3000000,
                'status' => 'pending',
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'room_title' => 'Spacious 2BR Apartment',
                'tenant_name' => 'Jane Smith',
                'tenant_email' => 'jane@example.com',
                'start_date' => '2024-06-15',
                'end_date' => '2024-11-15',
                'total_amount' => 4000000,
                'status' => 'approved',
                'created_at' => now(),
            ]
        ]);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Approve order
     */
    public function approveOrder(Request $request, $id): JsonResponse
    {
        // Implement order approval logic
        return response()->json([
            'success' => true,
            'message' => 'Order approved successfully'
        ]);
    }

    /**
     * Reject order
     */
    public function rejectOrder(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'sometimes|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Implement order rejection logic
        return response()->json([
            'success' => true,
            'message' => 'Order rejected successfully'
        ]);
    }

    /**
     * Complete order
     */
    public function completeOrder(Request $request, $id): JsonResponse
    {
        // Implement order completion logic
        return response()->json([
            'success' => true,
            'message' => 'Order completed successfully'
        ]);
    }

    /**
     * Get conversations
     */
    public function getConversations(Request $request): JsonResponse
    {
        $conversations = collect([
            [
                'id' => 1,
                'participant_name' => 'John Doe',
                'last_message' => 'When can I move in?',
                'last_message_at' => now()->subHours(2),
                'unread_count' => 2,
            ],
            [
                'id' => 2,
                'participant_name' => 'Jane Smith',
                'last_message' => 'Thank you for approving my booking!',
                'last_message_at' => now()->subHours(5),
                'unread_count' => 0,
            ]
        ]);

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * Send message
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

        // Implement message sending logic
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully'
        ]);
    }
}