<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'method',
        'amount',
        'status',
        'transaction_id',
        'payment_date',
        'notes'
    ];

    protected $casts = [
        'amount' => 'integer',
        'payment_date' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'Pending'
    ];

    // Relationships
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'Paid');
    }

    public function scopeByMethod($query, $method)
    {
        if ($method) {
            return $query->where('method', $method);
        }
        return $query;
    }

    // Generate transaction ID
    public static function generateTransactionId()
    {
        return 'TXN-' . time() . '-' . rand(1000, 9999);
    }

    // Mark payment as paid
    public function markAsPaid()
    {
        $this->update([
            'status' => 'Paid',
            'payment_date' => now()
        ]);

        // Update reservation status
        $this->reservation->update(['status' => 'Payment']);
    }
}