<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * THIS IS THE CRITICAL FIX.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_id',
        'transaction_id',
        'amount',
        'status',
        'method',
        'payment_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'integer',
    ];

    protected $attributes = [
        'status' => 'pending'
    ];

    /**
     * Defines the relationship to the Reservation model.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Generate a unique transaction ID.
     *
     * @return string
     */
    public static function generateTransactionId()
    {
        return 'TXN-' . time() . '-' . rand(1000, 9999);
    }

    /**
     * Mark payment as paid.
     */
    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => now()
        ]);

        // Update reservation status
        $this->reservation->update(['status' => 'Payment']);
    }

    /**
     * Scope a query to only include pending payments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include paid payments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include payments by a specific method.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $method
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByMethod($query, $method)
    {
        if ($method) {
            return $query->where('method', $method);
        }
        return $query;
    }
}