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
    /**
     * Display a listing of rooms with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            Log::info('Room search request', [
                'query_params' => $request->all(),
                'headers' => $request->headers->all()
            ]);

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
                'sort_by' => 'nullable|string|in:created_at,price,rating,name',
                'sort_order' => 'nullable|string|in:asc,desc'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed', [
                    'errors' => $validator->errors()
                ]);
                return response()->json([
                    'error' => 'Invalid parameters',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $query = Room::query();

            // Apply search filter
            if (!empty($data['search'])) {
                $searchTerm = $data['search'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('location', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Apply filters for other parameters here...

            // Ensure the query is correct before executing
            Log::info('Query prepared', [
                'query' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $rooms = $query->paginate(12);
            return response()->json([
                'data' => $rooms,
                'meta' => [
                    'current_page' => $rooms->currentPage(),
                    'last_page' => $rooms->lastPage(),
                    'per_page' => $rooms->perPage(),
                    'total' => $rooms->total(),
                    'from' => $rooms->firstItem(),
                    'to' => $rooms->lastItem()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in room search', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
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
            'price' => $room->price_per_day,
            'price_per_day' => $room->price_per_day,
            'price_per_week' => $room->price_per_week,
            'price_per_month' => $room->price_per_month,
            'image' => $room->featured_image,
            'featured_image' => $room->featured_image,
            'rating' => (float) $room->rating,
            'review_count' => (int) $room->review_count,
            'is_available' => (bool) $room->is_available,
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

    
}