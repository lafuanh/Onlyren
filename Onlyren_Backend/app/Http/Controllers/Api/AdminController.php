<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::where('role', 'user')->count(),
                'total_renters' => User::where('role', 'renter')->count(),
                'total_rooms' => Room::count(),
                'total_reservations' => Reservation::count(),
                'total_payments' => Payment::count(),
                'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
                'pending_reservations' => Reservation::where('status', 'pending')->count(),
                'pending_payments' => Payment::where('status', 'pending')->count(),
                'this_month_reservations' => Reservation::whereMonth('created_at', now()->month)->count(),
                'this_month_revenue' => Payment::where('status', 'paid')
                    ->whereMonth('payment_date', now()->month)
                    ->sum('amount'),
                'recent_activities' => $this->getRecentActivities(),
                'top_rooms' => $this->getTopRooms(),
                'monthly_stats' => $this->getMonthlyStats()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users with pagination and filters
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $query = User::query();

            // Apply filters
            if ($request->has('role') && $request->role) {
                $query->where('role', $request->role);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific user details
     */
    public function getUser($id): JsonResponse
    {
        try {
            $user = User::with([
                'reservations.room',
                'reservations.payment',
                'rooms' => function($query) {
                    $query->withCount('reservations');
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'role' => 'sometimes|in:user,renter,admin',
                'status' => 'sometimes|in:active,inactive,suspended'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Prevent admin from deleting themselves
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete your own account'
                ], 422);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all rooms with pagination and filters
     */
    public function getRooms(Request $request): JsonResponse
    {
        try {
            $query = Room::with(['owner:id,name,email']);

            // Apply filters
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $rooms = $query->paginate($perPage);

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
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific room details
     */
    public function getRoom($id): JsonResponse
    {
        try {
            $room = Room::with([
                'owner:id,name,email',
                'reservations.user:id,name,email',
                'reservations.payment'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $room
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update room
     */
    public function updateRoom(Request $request, $id): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'location' => 'sometimes|string',
                'type' => 'sometimes|string',
                'capacity' => 'sometimes|integer|min:1',
                'price_per_hour' => 'sometimes|numeric|min:0',
                'price_per_day' => 'sometimes|numeric|min:0',
                'price_per_week' => 'sometimes|numeric|min:0',
                'price_per_month' => 'sometimes|numeric|min:0',
                'status' => 'sometimes|in:available,unavailable,maintenance'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $room->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully',
                'data' => $room
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update room',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete room
     */
    public function deleteRoom($id): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);

            // Check if room has active reservations
            $activeReservations = $room->reservations()
                ->whereIn('status', ['pending', 'confirmed', 'payment'])
                ->count();

            if ($activeReservations > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete room with active reservations'
                ], 422);
            }

            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete room',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all reservations with pagination and filters
     */
    public function getReservations(Request $request): JsonResponse
    {
        try {
            $query = Reservation::with([
                'user:id,name,email',
                'room:id,name,location',
                'payment'
            ]);

            // Apply filters
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('room_id') && $request->room_id) {
                $query->where('room_id', $request->room_id);
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
     * Get specific reservation details
     */
    public function getReservation($id): JsonResponse
    {
        try {
            $reservation = Reservation::with([
                'user:id,name,email',
                'room:id,name,location,description',
                'payment'
            ])->findOrFail($id);

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
     * Approve reservation
     */
    public function approveReservation($id): JsonResponse
    {
        try {
            $reservation = Reservation::findOrFail($id);

            if ($reservation->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation is not in pending status'
                ], 422);
            }

            $reservation->update(['status' => 'confirmed']);

            return response()->json([
                'success' => true,
                'message' => 'Reservation approved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve reservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject reservation
     */
    public function rejectReservation($id): JsonResponse
    {
        try {
            $reservation = Reservation::findOrFail($id);

            if ($reservation->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation is not in pending status'
                ], 422);
            }

            $reservation->update(['status' => 'rejected']);

            // Update payment status if exists
            if ($reservation->payment) {
                $reservation->payment->update(['status' => 'cancelled']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Reservation rejected successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject reservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete reservation
     */
    public function completeReservation($id): JsonResponse
    {
        try {
            $reservation = Reservation::findOrFail($id);

            if (!in_array($reservation->status, ['confirmed', 'payment'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation is not in confirmed or payment status'
                ], 422);
            }

            $reservation->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'message' => 'Reservation completed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete reservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete reservation
     */
    public function deleteReservation($id): JsonResponse
    {
        try {
            $reservation = Reservation::findOrFail($id);

            // Only allow deletion of cancelled or rejected reservations
            if (!in_array($reservation->status, ['cancelled', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete active reservation'
                ], 422);
            }

            $reservation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reservation deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all payments with pagination and filters
     */
    public function getPayments(Request $request): JsonResponse
    {
        try {
            $query = Payment::with([
                'reservation.user:id,name,email',
                'reservation.room:id,name,location'
            ]);

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
     * Get specific payment details
     */
    public function getPayment($id): JsonResponse
    {
        try {
            $payment = Payment::with([
                'reservation.user:id,name,email',
                'reservation.room:id,name,location'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Confirm payment
     */
    public function confirmPayment($id): JsonResponse
    {
        try {
            $payment = Payment::with('reservation')->findOrFail($id);

            if ($payment->status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is not in paid status'
                ], 422);
            }

            DB::beginTransaction();

            // Update payment status
            $payment->update(['status' => 'confirmed']);

            // Update reservation status
            $payment->reservation->update(['status' => 'completed']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete payment
     */
    public function deletePayment($id): JsonResponse
    {
        try {
            $payment = Payment::findOrFail($id);

            // Only allow deletion of cancelled payments
            if ($payment->status !== 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete active payment'
                ], 422);
            }

            $payment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reservation report
     */
    public function getReservationReport(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth());
            $endDate = $request->get('end_date', now()->endOfMonth());

            $report = [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'total_reservations' => Reservation::whereBetween('created_at', [$startDate, $endDate])->count(),
                'pending_reservations' => Reservation::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'pending')->count(),
                'confirmed_reservations' => Reservation::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'confirmed')->count(),
                'completed_reservations' => Reservation::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'completed')->count(),
                'cancelled_reservations' => Reservation::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'cancelled')->count(),
                'daily_stats' => $this->getDailyReservationStats($startDate, $endDate),
                'top_rooms' => $this->getTopRoomsByReservations($startDate, $endDate),
                'top_users' => $this->getTopUsersByReservations($startDate, $endDate)
            ];

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate reservation report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment report
     */
    public function getPaymentReport(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth());
            $endDate = $request->get('end_date', now()->endOfMonth());

            $report = [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'total_payments' => Payment::whereBetween('created_at', [$startDate, $endDate])->count(),
                'total_revenue' => Payment::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'paid')->sum('amount'),
                'pending_payments' => Payment::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'pending')->count(),
                'paid_payments' => Payment::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'paid')->count(),
                'cancelled_payments' => Payment::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'cancelled')->count(),
                'payment_methods' => $this->getPaymentMethodStats($startDate, $endDate),
                'daily_revenue' => $this->getDailyRevenueStats($startDate, $endDate)
            ];

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate payment report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper methods for dashboard and reports
    private function getRecentActivities()
    {
        return DB::table('reservations')
            ->select('reservations.*', 'users.name as user_name', 'rooms.name as room_name')
            ->join('users', 'reservations.user_id', '=', 'users.id')
            ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
            ->orderBy('reservations.created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function getTopRooms()
    {
        return Room::withCount('reservations')
            ->orderBy('reservations_count', 'desc')
            ->limit(5)
            ->get();
    }

    private function getMonthlyStats()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'reservations' => Reservation::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'revenue' => Payment::where('status', 'paid')
                    ->whereMonth('payment_date', $date->month)
                    ->whereYear('payment_date', $date->year)
                    ->sum('amount')
            ];
        }
        return $months;
    }

    private function getDailyReservationStats($startDate, $endDate)
    {
        return DB::table('reservations')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getTopRoomsByReservations($startDate, $endDate)
    {
        return Room::withCount(['reservations' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
        ->orderBy('reservations_count', 'desc')
        ->limit(10)
        ->get();
    }

    private function getTopUsersByReservations($startDate, $endDate)
    {
        return User::withCount(['reservations' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
        ->orderBy('reservations_count', 'desc')
        ->limit(10)
        ->get();
    }

    private function getPaymentMethodStats($startDate, $endDate)
    {
        return Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->select('method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->get();
    }

    private function getDailyRevenueStats($startDate, $endDate)
    {
        return Payment::where('status', 'paid')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->select(DB::raw('DATE(payment_date) as date'), DB::raw('SUM(amount) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
} 