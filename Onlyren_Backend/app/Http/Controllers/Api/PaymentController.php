<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Payment::with(['reservation.room:id,name,description,images,location', 'reservation.user:id,name,email']);

            // Filter by user role
            if ($user->role === 'user') {
                $query->whereHas('reservation', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            // Apply filters
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('method') && $request->method) {
                $query->byMethod($request->method);
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
     * Display the specified payment
     */
    public function show($id): JsonResponse
    {
        try {
            $payment = Payment::with([
                'reservation.room:id,name,description,images,location',
                'reservation.user:id,name,email'
            ])->findOrFail($id);

            // Check authorization
            $user = Auth::user();
            if ($user->role === 'user' && $payment->reservation->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

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
     * Process payment for a reservation
     */
    public function processPayment(Request $request, $reservationId): JsonResponse
    {
        try {
            $reservation = Reservation::with('payment')->findOrFail($reservationId);

            // Check authorization
            $user = Auth::user();
            if ($user->role === 'user' && $reservation->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Check if reservation can be paid
            if ($reservation->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation cannot be paid with status: ' . $reservation->status
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'method' => 'required|in:Cash,QRIS,Bank Transfer',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update payment details
            $payment = $reservation->payment;
            $payment->update([
                'method' => $request->method,
                'notes' => $request->notes,
                'status' => 'paid',
                'payment_date' => now()
            ]);

            // Update reservation status
            $reservation->update(['status' => 'Payment']);

            DB::commit();

            $payment->load(['reservation.room:id,name,images,location']);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm payment (for admin/renter)
     */
    public function confirmPayment($id): JsonResponse
    {
        try {
            $payment = Payment::with('reservation')->findOrFail($id);

            // Check authorization (only admin/renter can confirm)
            $user = Auth::user();
            if (!in_array($user->role, ['admin', 'renter'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            if ($payment->status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is not in paid status'
                ], 422);
            }

            DB::beginTransaction();

            // Update reservation to completed
            $payment->reservation->update(['status' => 'Completed']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed and reservation completed'
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
     * Get payment methods
     */
    public function getPaymentMethods(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['value' => 'Cash', 'label' => 'Cash Payment'],
                ['value' => 'QRIS', 'label' => 'QRIS'],
                ['value' => 'Bank Transfer', 'label' => 'Bank Transfer']
            ]
        ]);
    }

    /**
     * Get payment statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Payment::query();

            if ($user->role === 'user') {
                $query->whereHas('reservation', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $stats = [
                'total_payments' => $query->count(),
                'pending_payments' => $query->clone()->pending()->count(),
                'paid_payments' => $query->clone()->paid()->count(),
                'total_revenue' => $query->clone()->paid()->sum('amount'),
                'this_month_revenue' => $query->clone()->paid()->whereMonth('payment_date', now()->month)->sum('amount'),
                'payment_methods' => $query->clone()->paid()
                    ->selectRaw('method, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('method')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment receipt
     */
    public function receipt($id): JsonResponse
    {
        try {
            $payment = Payment::with([
                'reservation.room:id,name,location,price_per_hour',
                'reservation.user:id,name,email'
            ])->findOrFail($id);

            // Check authorization
            $user = Auth::user();
            if ($user->role === 'user' && $payment->reservation->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $receipt = [
                'transaction_id' => $payment->transaction_id,
                'payment_date' => $payment->payment_date,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'reservation' => [
                    'id' => $payment->reservation->id,
                    'start_date' => $payment->reservation->start_date,
                    'end_date' => $payment->reservation->end_date,
                    'start_time' => $payment->reservation->start_time,
                    'end_time' => $payment->reservation->end_time,
                    'duration' => $payment->reservation->duration,
                    'guests' => $payment->reservation->guests,
                    'room' => $payment->reservation->room,
                    'user' => $payment->reservation->user
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $receipt
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}