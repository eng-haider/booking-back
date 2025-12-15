<?php

namespace App\Models;

use App\Enums\VenueStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'owner_id',
        'category_id',
        'name',
        'description',
        'price_per_hour',
        'capacity',
        'base_price',
        'currency',
        'status',
        'buffer_minutes',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'price_per_hour' => 'decimal:2',
            'capacity' => 'integer',
            'base_price' => 'decimal:2',
            'buffer_minutes' => 'integer',
            'status' => VenueStatus::class,
        ];
    }

    /**
     * Get the provider that owns the venue.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get the owner of the venue.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the category that this venue belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the photos for this venue.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Get the bookings for this venue.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the reviews for this venue.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the schedules for this venue.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get the amenities for this venue.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'amenity_venue');
    }

    /**
     * Get the primary photo for this venue.
     */
    public function primaryPhoto(): HasMany
    {
        return $this->hasMany(Photo::class)->where('is_primary', true);
    }
}
