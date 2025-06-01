<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reservations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservations::with(['user', 'room'])->get();
        return response()->json([
            'success' => true,
            'data' => $reservations
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'duration' => 'required|integer',
            'guests' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        // Check if room is available for the selected dates
        $existingReservation = Reservations::where('room_id', $request->room_id)
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
            })
            ->where('status', '!=', 'Completed')
            ->first();

        if ($existingReservation) {
            return response()->json([
                'success' => false,
                'message' => 'Room is not available for selected dates'
            ], 422);
        }

        $reservation = Reservations::create([
            'user_id' => $request->user()->id,
            'room_id' => $request->room_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration' => $request->duration,
            'guests' => $request->guests,
            'status' => 'Pending'
        ]);

        return response()->json([
            'success' => true,
            'data' => $reservation->load(['user', 'room'])
        ], 201);
    }

    public function show($id)
    {
        $reservation = Reservations::with(['user', 'room'])->find($id);
        
        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reservation
        ]);
    }

    public function update(Request $request, $id)
    {
        $reservation = Reservations::find($id);

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending,Payment,Completed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $reservation->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'data' => $reservation->load(['user', 'room'])
        ]);
    }

    public function destroy($id)
    {
        $reservation = Reservations::find($id);

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 404);
        }

        $reservation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reservation deleted successfully'
        ]);
    }
}