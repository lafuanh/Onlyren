<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RenterController extends Controller
{
    
    /**
     * Get renter profile
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user()->load('renterProfile');
        $profileData = $user->renterProfile ? $user->renterProfile->toArray() : [];

        // In your frontend, the user's name is in profile.name
        // but the form has ownerName. Let's send both for clarity.
        $data = array_merge([
            'id' => $user->id,
            'name' => $user->name, // This is profile.name in the sidebar
            'email' => $user->email,
        ], $profileData);

        // Ensure ownerName is set, falling back to the user's main name
        if (empty($data['ownerName'])) {
            $data['ownerName'] = $user->name;
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Update renter profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'ownerName' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'businessName' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'siupNumber' => 'nullable|string|max:50',
            'nibNumber' => 'nullable|string|max:50',
            'businessAddress' => 'nullable|string',
            'businessDescription' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation errors', 'errors' => $validator->errors()], 422);
        }

        // Update the User model (for email and the primary name)
        $user->update([
            'name' => $request->input('ownerName'), // Use ownerName as the primary user name
            'email' => $request->input('email'),
        ]);

        // Create or update the RenterProfile model
        $user->renterProfile()->updateOrCreate(
            ['user_id' => $user->id], // Condition to find the record
            $request->only([          // Data to update or create
                'businessName',
                'ownerName',
                'phone',
                'siupNumber',
                'nibNumber',
                'businessAddress',
                'businessDescription',
            ])
        );

        // Return the freshly updated and combined profile
        return $this->getProfile($request);
    }

        /**
     * Get rooms owned by the authenticated renter.
     */
    public function getRooms(Request $request): JsonResponse
    {
        $rooms = Room::where('owner_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Use the private helper to ensure consistent data format
        $transformedRooms = $rooms->map(fn($room) => $this->transformRoom($room));

        return response()->json(['success' => true, 'data' => $transformedRooms]);
    }


    /**
     * Get specific room
     */
    public function getRoom(Request $request, $id): JsonResponse
    {
        try {
            $room = Room::where('id', $id)
                ->where('owner_id', $request->user()->id)
                ->firstOrFail();

            return response()->json(['success' => true, 'data' => $this->transformRoom($room, true)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Room not found or you do not own this room.'], 404);
        }
    }

    /**
     * Update room
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $room = Room::where('id', $id)
                ->where('owner_id', $request->user()->id)
                ->firstOrFail();

            $validator = Validator::make($request->all(), $this->roomValidationRules(true));

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }

            $validatedData = $validator->validated();

            if ($request->hasFile('featured_image')) {
                if ($room->featured_image) {
                    Storage::disk('public')->delete($room->featured_image);
                }
                $validatedData['featured_image'] = $request->file('featured_image')->store('rooms', 'public');
            }

            if (isset($validatedData['amenities']) && is_string($validatedData['amenities'])) {
                $validatedData['amenities'] = json_decode($validatedData['amenities'], true);
            }

            $room->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil diperbarui.',
                'data' => $this->transformRoom($room->fresh(), true)
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Room not found or you do not own this room.'], 404);
        }
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

    public function updateJson(Request $request, $id): JsonResponse
{
    $room = Room::where('id', $id)->where('owner_id', $request->user()->id)->firstOrFail();
    
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'location' => 'required|string|max:255',
        'type' => 'required|string|max:100',
        'capacity' => 'required|string|max:100',
        'price_per_day' => 'required|numeric|min:0',
        'price_per_week' => 'nullable|numeric|min:0',
        'price_per_month' => 'nullable|numeric|min:0',
        'specifications' => 'nullable|string',
        'amenities' => 'nullable|array',
        'is_available' => 'boolean',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false, 
            'message' => 'Validation failed', 
            'errors' => $validator->errors()
        ], 422);
    }

    $validatedData = $validator->validated();
    $room->update($validatedData);

    return response()->json([
        'success' => true, 
        'message' => 'Room updated successfully.', 
        'data' => $this->transformRoom($room->fresh(), true)
    ], 200);
}
    /**
     * Store a new room for the authenticated renter.
     */
      public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->roomValidationRules());

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $validatedData['owner_id'] = $request->user()->id; // Set ownership

        if ($request->hasFile('featured_image')) {
            $validatedData['featured_image'] = $request->file('featured_image')->store('rooms', 'public');
        }

        if (isset($validatedData['amenities']) && is_string($validatedData['amenities'])) {
            $validatedData['amenities'] = json_decode($validatedData['amenities'], true);
        }

        $room = Room::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Ruangan berhasil dibuat.',
            'data' => $this->transformRoom($room->fresh(), true)
        ], 201);
    }

    /**
     * Update an existing room for the authenticated renter.
     */

    /**
     * Delete a room owned by the renter.
     */
     public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $room = Room::where('id', $id)
                ->where('owner_id', $request->user()->id)
                ->firstOrFail();

            if ($room->featured_image) {
                Storage::disk('public')->delete($room->featured_image);
            }

            $room->delete();

            return response()->json(['success' => true, 'message' => 'Ruangan berhasil dihapus.']);

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Room not found or you do not own this room.'], 404);
        }
    }

  

    #------------#
    #RESERVATION MANAGEMENT
    #
    #------------#
    public function getReservations(Request $request): JsonResponse
    {
        try {
            $renter = Auth::user();

            // Get the IDs of all rooms owned by the renter
            $roomIds = Room::where('owner_id', $renter->id)->pluck('id');

            // Fetch reservations for those rooms, including user and room details
            $reservations = Reservation::whereIn('room_id', $roomIds)
                ->with(['user:id,name,email', 'room:id,name,price_per_hour'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Transform the data to match the frontend's expectations
            $transformedData = $reservations->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'roomName' => $reservation->room->name ?? 'N/A',
                    'userName' => $reservation->user->name ?? 'N/A',
                    'userEmail' => $reservation->user->email ?? 'N/A',
                    'userPhone' => null, // You can add this if you store user phone numbers
                    'date' => $reservation->start_date->format('Y-m-d'),
                    'startTime' => $reservation->start_time,
                    'endTime' => $reservation->end_time,
                    'duration' => $reservation->duration,
                    'pricePerHour' => $reservation->room->price_per_hour ?? 0,
                    'totalPrice' => $reservation->total_amount,
                    'notes' => $reservation->notes,
                    'status' => strtolower($reservation->status), // e.g., 'pending', 'approved'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reservations: ' . $e->getMessage()
            ], 500);
        }
    }
    private function roomValidationRules(bool $isUpdate = false): array
    {
        // For 'store' (creation), all fields are strictly required.
        if (!$isUpdate) {
            return [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'required|string|max:255',
                'type' => 'required|string|max:100',
                'capacity' => 'required|string|max:100',
                'price_per_hour' => 'required|numeric|min:0',
                'price_per_day' => 'required|numeric|min:0',
                'price_per_week' => 'nullable|numeric|min:0',
                'price_per_month' => 'nullable|numeric|min:0',
                'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'specifications' => 'nullable|string',
                'amenities' => 'nullable|json',
                'is_available' => 'required|boolean',
            ];
        }

        // For 'update', use 'sometimes' to only validate fields that are present in the request.
        // This prevents errors if a field isn't sent.
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:100',
            'capacity' => 'sometimes|required|string|max:100',
            'price_per_hour' => 'sometimes|required|numeric|min:0',
            'price_per_day' => 'sometimes|required|numeric|min:0',
            'price_per_week' => 'nullable|numeric|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // 'sometimes' is not needed for files
            'specifications' => 'nullable|string',
            'amenities' => 'sometimes|nullable|json',
            'is_available' => 'sometimes|required|boolean',
        ];
    }

    /**
     * Helper to transform room data for a consistent API response.
     */
    private function transformRoom(Room $room, bool $detailed = false): array
    {
        $data = [
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'location' => $room->location,
            'type' => $room->type,
            'capacity' => $room->capacity,
            'price_per_hour' => $room->price_per_hour,
            'price_per_day' => $room->price_per_day,
            'price_per_week' => $room->price_per_week,
            'price_per_month' => $room->price_per_month,
            'featured_image' => $room->featured_image,
            'is_available' => (bool) $room->is_available,
        ];

        if ($detailed) {
            $data['amenities'] = $room->amenities ?? [];
            $data['specifications'] = $room->specifications;
        }

        return $data;
    }
}