<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Get all conversations for the authenticated user
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
     * Get messages between two users
     */
    public function getConversation($otherUserId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Validate that the other user exists
            $otherUser = User::findOrFail($otherUserId);
            
            // Get messages between the two users
            $messages = Message::getConversation($user->id, $otherUserId);
            
            // Mark messages as read
            Message::where('sender_id', $otherUserId)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $messages,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'email' => $otherUser->email
                    ]
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch conversation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|exists:users,id',
                'message' => 'required|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            // Prevent sending message to self
            if ($user->id == $request->receiver_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot send message to yourself'
                ], 422);
            }

            $message = Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'sent_at' => now()
            ]);

            $message->load(['sender:id,name,email', 'receiver:id,name,email']);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark messages as read
     */
    public function markAsRead($senderId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            Message::where('sender_id', $senderId)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark messages as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount(): JsonResponse
    {
        try {
            $user = Auth::user();
            $count = Message::getUnreadCount($user->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users for messaging
     */
    public function searchUsers(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $query = $request->query;

            $users = User::where('id', '!=', $user->id)
                ->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->select('id', 'name', 'email')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a message (only sender can delete)
     */
    public function deleteMessage($messageId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $message = Message::where('id', $messageId)
                ->where('sender_id', $user->id)
                ->first();

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found or unauthorized'
                ], 404);
            }

            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete message',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
