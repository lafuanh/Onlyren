<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Search rooms with advanced filters
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = Room::query()->available();

            // Text search in name, description, location
            if ($request->has('q') && $request->q) {
                $searchTerm = $request->q;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('location', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('address', 'LIKE', '%' . $searchTerm . '%');
                });
            }

            // Filter by room type
            if ($request->has('type') && $request->type) {
                $query->byType($request->type);
            }

            // Filter by capacity
            if ($request->has('capacity') && $request->capacity) {
                $query->byCapacity($request->capacity);
            }

            // Filter by price range
            if ($request->has('price_min') && $request->has('price_max')) {
                $query->byPriceRange($request->price_min, $request->price_max);
            } elseif ($request->has('price_min')) {
                $query->where('price_per_hour', '>=', $request->price_min);
            } elseif ($request->has('price_max')) {
                $query->where('price_per_hour', '<=', $request->price_max);
            }

            // Filter by location
            if ($request->has('location') && $request->location) {
                $query->where('location', 'LIKE', '%' . $request->location . '%');
            }

            // Filter by amenities
            if ($request->has('amenities') && $request->amenities) {
                $amenities = is_array($request->amenities) ? $request->amenities : explode(',', $request->amenities);
                foreach ($amenities as $amenity) {
                    $query->whereJsonContains('amenities', trim($amenity));
                }
            }

            // Filter by availability (date range)
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereDoesntHave('reservations', function($q) use ($request) {
                    $q->where('status', '!=', 'Cancelled')
                      ->where(function ($subQuery) use ($request) {
                          $subQuery->whereBetween('start_date', [$request->start_date, $request->end_date])
                                   ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                                   ->orWhere(function ($dateQuery) use ($request) {
                                       $dateQuery->where('start_date', '<=', $request->start_date)
                                                ->where('end_date', '>=', $request->end_date);
                                   });
                      });
                });
            }

            // Sorting options
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            switch ($sortBy) {
                case 'price_low':
                    $query->orderBy('price_per_hour', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price_per_hour', 'desc');
                    break;
                case 'rating':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'capacity':
                    $query->orderBy('capacity', $sortOrder);
                    break;
                case 'name':
                    $query->orderBy('name', $sortOrder);
                    break;
                default:
                    $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = min($request->get('per_page', 12), 50); // Max 50 items per page
            $rooms = $query->paginate($perPage);

            // Transform data for search results
            $rooms->getCollection()->transform(function ($room) use ($request) {
                $period = $request->get('period', 'Harian');
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'type' => $room->type,
                    'capacity' => $room->capacity,
                    'price' => $room->getPriceByPeriod($period),
                    'price_per_hour' => $room->price_per_hour,
                    'address' => $room->address,
                    'location' => $room->location,
                    'amenities' => $room->amenities,
                    'images' => $room->images,
                    'main_image' => $room->main_image,
                    'rating' => $room->rating,
                    'review_count' => $room->review_count,
                    'period' => $period
                ];
            });

            // Get filter options for frontend
            $filterOptions = [
                'types' => Room::distinct('type')->pluck('type')->filter()->values(),
                'locations' => Room::distinct('location')->pluck('location')->filter()->values(),
                'price_range' => [
                    'min' => Room::min('price_per_hour'),
                    'max' => Room::max('price_per_hour')
                ],
                'capacity_range' => [
                    'min' => Room::min('capacity'),
                    'max' => Room::max('capacity')
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $rooms->items(),
                'meta' => [
                    'current_page' => $rooms->currentPage(),
                    'last_page' => $rooms->lastPage(),
                    'per_page' => $rooms->perPage(),
                    'total' => $rooms->total(),
                    'from' => $rooms->firstItem(),
                    'to' => $rooms->lastItem()
                ],
                'filters' => $filterOptions,
                'applied_filters' => $request->only([
                    'q', 'type', 'capacity', 'price_min', 'price_max', 
                    'location', 'amenities', 'start_date', 'end_date', 'sort_by'
                ])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function suggestions(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $suggestions = [
                'rooms' => Room::where('name', 'LIKE', '%' . $query . '%')
                    ->select('id', 'name', 'type', 'location')
                    ->limit(5)
                    ->get(),
                'locations' => Room::where('location', 'LIKE', '%' . $query . '%')
                    ->distinct('location')
                    ->pluck('location')
                    ->take(5),
                'types' => Room::where('type', 'LIKE', '%' . $query . '%')
                    ->distinct('type')
                    ->pluck('type')
                    ->take(5)
            ];

            return response()->json([
                'success' => true,
                'data' => $suggestions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}