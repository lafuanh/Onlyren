<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'type',
        'capacity',
        'specifications',
        'price_per_hour',
        'price_per_day',
        'price_per_week',
        'price_per_month',
        'featured_image',
        'images',
        'amenities',
        'facilities',
        'rating',
        'review_count',
        'is_available',
        'is_featured',
        'availability_status',
        'owner_id'
    ];

    protected $casts = [
        'images' => 'array',
        'amenities' => 'array',
        'facilities' => 'array',
        'price_per_hour' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'price_per_week' => 'decimal:2',
        'price_per_month' => 'decimal:2',
        'rating' => 'decimal:2',
        'review_count' => 'integer',
        'is_available' => 'boolean',
        'is_featured' => 'boolean'
    ];

    protected $appends = [
        'price' // Default price (per day)
    ];

    /**
     * Get the owner of the room
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get reservations for this room
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the default price (per day)
     */
    public function getPriceAttribute()
    {
        return $this->price_per_day;
    }

    /**
     * Scope for available rooms
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope for featured rooms
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%")
              ->orWhere('location', 'LIKE', "%{$search}%");
        });
    }
}