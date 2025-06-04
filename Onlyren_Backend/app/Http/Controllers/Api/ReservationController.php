<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Display a listing of reservations
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Reservation::with(['room:id,name,images,address', 'payment']);

            // Filter by user role
            if ($user->role === 'user') {
                $query->forUser($user->id);
            }

            // Apply filters
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('room_id') && $request->room_id) {
                $query->forRoom($request->room_id);
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
     * Store a newly created reservation
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'room_id' => 'required|exists:rooms,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'guests' => 'required|integer|min:1',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $room = Room::findOrFail($data['room_id']);

            // Check room capacity
            if ($data['guests'] > $room->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Number of guests exceeds room capacity'
                ], 422);
            }

            // Check for conflicts
            if (Reservation::hasConflict(
                $data['room_id'],
                $data['start_date'],
                $data['end_date'],
                $data['start_time'],
                $data['end_time']
            )) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room is not available for the selected time period'
                ], 409);
            }

            DB::beginTransaction();

            // Calculate duration and total amount
            $startTime = Carbon::createFromFormat('H:i', $data['start_time']);
            $endTime = Carbon::createFromFormat('H:i', $data['end_time']);
            $duration = $endTime->diffInHours($startTime);
            $totalAmount = $room->price_per_hour * $duration;

            // Create reservation
            $reservation = Reservation::create([
                'user_id' => Auth::id(),
                'room_id' => $data['room_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'duration' => $duration,
                'guests' => $data['guests'],
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
                'status' => 'Pending'
            ]);

            // Create payment record
            $payment = Payment::create([
                'reservation_id' => $reservation->id,
                'amount' => $totalAmount,
                'status' => 'Pending',
                'transaction_id' => Payment::generateTransactionId()
            ]);

            DB::commit();

            // Load relationships
            $reservation->load(['room:id,name,images,address', 'payment']);

            return response()->json([
                'success' => true,
                'message' => 'Reservation created successfully',
                'data' => $reservation
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified reservation
     */
    public function show($id): JsonResponse
    {
        try {
            $reservation = Reservation::with([
                'room:id,name,description,images,address,location,amenities,price_per_hour',
                'user:id,name,email',
                'payment'
            ])->findOrFail($id);

            // Check authorization
            $user = Auth::user();
            if ($user->role === 'user' && $reservation->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $reservation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified reservation
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $reservation = Reservation::findOrFail($id);

            // Check authorization
            $user = Auth::user();
            if ($user->role === 'user' && $reservation->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Only allow updates for pending reservations
            if ($reservation->status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update reservation with status: ' . $reservation->status
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'start_date' => 'sometimes|date|after_or_equal:today',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'sometimes|date_format:H:i',
                'guests' => 'sometimes|integer|min:1',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // If time or date is being updated, check for conflicts
            if (isset($data['start_date']) || isset($data['end_date']) || 
                isset($data['start_time']) || isset($data['end_time'])) {
                
                $startDate = $data['start_date'] ?? $reservation->start_date;
                $endDate = $data['end_date'] ?? $reservation->end_date;
                $startTime = $data['start_time'] ?? $reservation->start_time;
                $endTime = $data['end_time'] ?? $reservation->end_time;

                if (Reservation::hasConflict(
                    $reservation->room_id,
                    $startDate,
                    $endDate,
                    $startTime,
                    $endTime,
                    $reservation->id
                )) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Room is not available for the selected time period'
                    ], 409);
                }

                // Recalculate duration and total amount
                if (isset($data['start_time']) || isset($data['end_time'])) {
                    $start = Carbon::createFromFormat('H:i', $startTime);
                    $end = Carbon::createFromFormat('H:i', $endTime);
                    $data['duration'] = $end->diffInHours($start);
                    $data['total_amount'] = $reservation->room->price_per_hour * $data['duration'];
                }
            }

            DB::beginTransaction();

            $reservation->update($data);

            // Update payment amount if total changed
            if (isset($data['total_amount'])) {
                $reservation->payment->update(['amount' => $data['total_amount']]);
            }

            DB::commit();

            $reservation->load(['room:id,name,images,address', 'payment']);

            return response()->json([
                'success' => true,
                'message' => 'Reservation updated successfully',
                'data' => $reservation
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the specified reservation
     */
    public function destroy($id): JsonResponse
    {
        try {
            $reservation = Reservation::findOrFail($id);

            // Check authorization
            $user = Auth::user();
            if ($user->role === 'user' && $reservation->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Only allow cancellation for pending reservations
            if ($reservation->status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel reservation with status: ' . $reservation->status
                ], 422);
            }

            DB::beginTransaction();

            // Update reservation status to cancelled
            $reservation->update(['status' => 'Cancelled']);

            // Update payment status
            if ($reservation->payment) {
                $reservation->payment->update(['status' => 'Cancelled']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservation cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel reservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reservation statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Reservation::query();

            if ($user->role === 'user') {
                $query->forUser($user->id);
            }

            $stats = [
                'total_reservations' => $query->count(),
                'pending_reservations' => $query->clone()->pending()->count(),
                'confirmed_reservations' => $query->clone()->confirmed()->count(),
                'completed_reservations' => $query->clone()->completed()->count(),
                'cancelled_reservations' => $query->clone()->where('status', 'Cancelled')->count(),
                'total_revenue' => $query->clone()->whereIn('status', ['Payment', 'Completed'])->sum('total_amount'),
                'this_month_reservations' => $query->clone()->whereMonth('created_at', now()->month)->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}