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

    /**
     * Defines the relationship to the Reservation model.
     */
    public function reservation()
    {
        // Because your column is 'reservations_id', we must specify it here.
        // The Laravel convention would be 'reservation_id'.
        return $this->belongsTo(Reservation::class, 'reservations_id');
    }

    /**
     * Generate a unique transaction ID.
     *
     * @return string
     */
    public static function generateTransactionId()
    {
        // Using random_bytes is more secure for transaction IDs than uniqid
        return 'txn_' . bin2hex(random_bytes(16));
    }
}