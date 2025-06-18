<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function update($id, Request $request): JsonResponse
    {
        try {
            Log::info('Attempting to update room.', ['room_id' => $id, 'request_data' => $request->all()]);
            
            $room = Room::findOrFail($id);

            if ($room->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk memperbarui ruangan ini.'], 403);
            }

            // [REVISED] Added 'price_per_hour' to validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'required|string|max:255',
                'type' => 'required|string|max:100',
                'capacity' => 'required|string|max:100',
                'price_per_hour' => 'required|numeric|min:0', // <<< ADDED
                'price_per_day' => 'required|numeric|min:0',
                'price_per_week' => 'nullable|numeric|min:0',
                'price_per_month' => 'nullable|numeric|min:0',
                'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'specifications' => 'nullable|string',
                'amenities' => 'nullable|json',
                'is_available' => 'boolean',
            ]);

            if ($validator->fails()) {
                Log::error('Room update validation failed', ['room_id' => $id, 'errors' => $validator->errors()->toArray()]);
                return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
            }

            $validatedData = $validator->validated();

            if ($request->hasFile('featured_image')) {
                if ($room->featured_image) {
                    Storage::disk('public')->delete($room->featured_image);
                }
                $imagePath = $request->file('featured_image')->store('rooms', 'public');
                $validatedData['featured_image'] = $imagePath;
            }

            if (isset($validatedData['amenities']) && is_string($validatedData['amenities'])) {
                $validatedData['amenities'] = json_decode($validatedData['amenities'], true);
            }

            $room->update($validatedData);

            Log::info('Room updated successfully', ['room_id' => $room->id]);

            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil diperbarui.',
                'data' => $this->transformRoom($room->fresh(), true)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Ruangan tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            Log::error('Error updating room', ['room_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal server.'], 500);
        }
    }

    public function destroy($id, Request $request): JsonResponse
    {
        try {
            // Find the room by ID
            $room = Room::findOrFail($id);

            // --- Authorization Check ---
            // Ensure only the owner can delete their room
            if ($room->owner_id !== $request->user()->id) {
                Log::warning('Unauthorized attempt to delete room', [
                    'room_id' => $id,
                    'user_id' => $request->user()->id ?? 'Guest'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus ruangan ini.'
                ], 403); // Forbidden
            }

            // Delete associated featured image from storage if it exists
            if ($room->featured_image) {
                Storage::disk('public')->delete($room->featured_image);
                Log::info('Deleted featured image for room', ['path' => $room->featured_image]);
            }

            // Optionally, delete other images if you have an 'images' column
            if ($room->images && is_array($room->images)) {
                foreach ($room->images as $imagePath) {
                    // Assuming 'images' stores public paths or full paths
                    $pathToDelete = str_replace('/storage/', '', $imagePath); // Adjust if your image path is different
                    if (Storage::disk('public')->exists($pathToDelete)) {
                        Storage::disk('public')->delete($pathToDelete);
                    }
                }
                Log::info('Deleted additional images for room', ['room_id' => $id]);
            }

            // Delete the room from the database
            $room->delete();

            Log::info('Room deleted successfully', [
                'room_id' => $id,
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil dihapus.'
            ], 200); // 200 OK or 204 No Content for successful deletion

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Attempt to delete non-existent room', ['room_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan.'
            ], 404); // Not Found
        } catch (\Exception $e) {
            Log::error('Error deleting room', [
                'room_id' => $id,
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? 'Guest'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal server saat menghapus ruangan: ' . $e->getMessage()
            ], 500); // Internal Server Error
        }
    }
      /**
     * Display a listing of rooms with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            Log::info('Room search request received', ['params' => $request->all()]);

            $validator = Validator::make($request->all(), [
                'search' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:100',
                'price_min' => 'nullable|numeric|min:0',
                'price_max' => 'nullable|numeric|min:0',
                'period' => 'nullable|in:Harian,Mingguan,Bulanan',
                'amenities' => 'nullable|string',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50',
                'sort_by' => 'nullable|string|in:created_at,price_per_day,rating,name',
                'sort_order' => 'nullable|string|in:asc,desc'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid search parameters.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Room::query()->where('is_available', true);

            // General Search (searches name and description)
            if ($request->filled('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', $searchTerm)
                      ->orWhere('description', 'LIKE', $searchTerm);
                });
            }

            // **CRITICAL FIX: Location Search**
            if ($request->filled('location')) {
                $locationTerm = '%' . $request->input('location') . '%';
                $query->where('location', 'LIKE', $locationTerm);
            }

            // Price Filter based on Period
            $priceColumn = $this->getPriceColumnByPeriod($request->input('period', 'Harian'));
            if ($request->filled('price_min')) {
                $query->where($priceColumn, '>=', $request->input('price_min'));
            }
            if ($request->filled('price_max')) {
                $query->where($priceColumn, '<=', $request->input('price_max'));
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->input('per_page', 12);
            $paginatedRooms = $query->paginate($perPage);
            
            Log::info('Search results count', ['count' => $paginatedRooms->total()]);

            // Return a clean, structured response
            return response()->json([
                'success' => true,
                'data' => $paginatedRooms->items(),
                'meta' => [
                    'current_page' => $paginatedRooms->currentPage(),
                    'last_page' => $paginatedRooms->lastPage(),
                    'per_page' => $paginatedRooms->perPage(),
                    'total' => $paginatedRooms->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Room search error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An internal server error occurred.'
            ], 500);
        }
    }

    /**
     * Display the specified room
     */
    public function show($id): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);
            
            return response()->json([
                'data' => $this->transformRoom($room, true)
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Room not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching room details', [
                'room_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get featured rooms
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = min(20, max(1, $request->get('limit', 8)));
            
            $rooms = Room::where('is_available', true)
                ->where('is_featured', true)
                ->orderBy('rating', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $transformedRooms = $rooms->map(function ($room) {
                return $this->transformRoom($room);
            });

            return response()->json([
                'data' => $transformedRooms
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching featured rooms', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Check room availability
     */
    public function checkAvailability($id, Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Invalid parameters',
                    'errors' => $validator->errors()
                ], 422);
            }

            $room = Room::findOrFail($id);
            
            // Check if room is available (implement your availability logic here)
            $isAvailable = true; // Placeholder - implement actual availability check
            
            return response()->json([
                'available' => $isAvailable,
                'room_id' => $room->id,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking availability', [
                'room_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Transform room data for API response
     */
        private function transformRoom($room, $detailed = false): array
    {
        $data = [
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'location' => $room->location,
            'type' => $room->type,
            'capacity' => $room->capacity,
            'price_per_hour' => $room->price_per_hour, // <<< ADDED
            'price_per_day' => $room->price_per_day,
            'price_per_week' => $room->price_per_week,
            'price_per_month' => $room->price_per_month,
            'featured_image' => $room->featured_image,
            'rating' => (float) $room->rating,
            'review_count' => (int) $room->review_count,
            'is_available' => (bool) $room->is_available,
            'owner_id' => $room->owner_id, // Add owner_id for frontend messaging
            'created_at' => $room->created_at,
            'updated_at' => $room->updated_at
        ];

        if ($detailed) {
            $data['images'] = $room->images ?? [];
            $data['amenities'] = $room->amenities ?? [];
            $data['specifications'] = $room->specifications;
            $data['owner'] = $room->owner;
        }

        return $data;
    }

    /**
     * Get price column based on period
     */
    private function getPriceColumnByPeriod($period): string
    {
        switch ($period) {
            case 'Harian':
                return 'price_per_day';
            case 'Mingguan':
                return 'price_per_week';
            case 'Bulanan':
                return 'price_per_month';
            default:
                return 'price_per_day';
        }
    }
    
    
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('Attempting to create a new room.', [
                'request_data' => $request->all(),
                'user_id' => $request->user()->id ?? 'Guest'
            ]);

            // [REVISED] Added 'price_per_hour' to validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'required|string|max:255',
                'type' => 'required|string|max:100',
                'capacity' => 'required|string|max:100',
                'price_per_hour' => 'required|numeric|min:0', // <<< ADDED
                'price_per_day' => 'required|numeric|min:0',
                'price_per_week' => 'nullable|numeric|min:0',
                'price_per_month' => 'nullable|numeric|min:0',
                'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'specifications' => 'nullable|string',
                'amenities' => 'nullable|json',
            ]);

            if ($validator->fails()) {
                Log::error('Room creation validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();

            $imagePath = null;
            if ($request->hasFile('featured_image')) {
                $imagePath = $request->file('featured_image')->store('rooms', 'public');
            }
            $validatedData['featured_image'] = $imagePath;
            $validatedData['owner_id'] = $request->user()->id;
            $validatedData['is_available'] = true;
            $validatedData['is_featured'] = false;

            if (isset($validatedData['amenities']) && is_string($validatedData['amenities'])) {
                $validatedData['amenities'] = json_decode($validatedData['amenities'], true);
            }

            $room = Room::create($validatedData);

            Log::info('Room created successfully', ['room_id' => $room->id, 'room_name' => $room->name]);

            return response()->json([
                'success' => true,
                'message' => 'Room created successfully',
                'data' => $this->transformRoom($room, true)
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating room', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }


}