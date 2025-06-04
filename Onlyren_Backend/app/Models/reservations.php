<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'duration',
        'status',
        'guests',
        'total_amount',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'duration' => 'integer',
        'guests' => 'integer',
        'total_amount' => 'integer'
    ];

    protected $attributes = [
        'status' => 'Pending',
        'guests' => 1
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'Payment');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForRoom($query, $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    // Mutators
    public function setStartTimeAttribute($value)
    {
        if ($value) {
            $this->attributes['start_time'] = Carbon::createFromFormat('H:i', $value)->format('H:i:s');
        }
    }

    public function setEndTimeAttribute($value)
    {
        if ($value) {
            $this->attributes['end_time'] = Carbon::createFromFormat('H:i', $value)->format('H:i:s');
        }
    }

    // Accessors
    public function getStartTimeAttribute($value)
    {
        return $value ? Carbon::createFromFormat('H:i:s', $value)->format('H:i') : null;
    }

    public function getEndTimeAttribute($value)
    {
        return $value ? Carbon::createFromFormat('H:i:s', $value)->format('H:i') : null;
    }

    // Calculate duration in hours
    public function calculateDuration()
    {
        if ($this->start_time && $this->end_time) {
            $start = Carbon::createFromFormat('H:i:s', $this->attributes['start_time']);
            $end = Carbon::createFromFormat('H:i:s', $this->attributes['end_time']);
            
            return $end->diffInHours($start);
        }
        return 0;
    }

    // Calculate total amount
    public function calculateTotalAmount()
    {
        if ($this->room && $this->duration) {
            return $this->room->price_per_hour * $this->duration;
        }
        return 0;
    }

    // Check if reservation conflicts with existing ones
    public static function hasConflict($roomId, $startDate, $endDate, $startTime, $endTime, $excludeId = null)
    {
        $query = self::where('room_id', $roomId)
            ->where('status', '!=', 'Cancelled')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}