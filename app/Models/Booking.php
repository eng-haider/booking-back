<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Traits\HasPayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory, HasPayment;

    protected $fillable = [
        'user_id',
        'customer_id',
        'venue_id',
        'booking_reference',
        'booking_date',
        'start_time',
        'end_time',
        'total_price',
        'status_id',
        'payment_status',
        'notes',
        'special_requests',
        'cancellation_reason',
        'cancelled_at',
        'confirmed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'total_price' => 'decimal:2',
            'cancelled_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the user that made the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer that made the booking.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the venue for this booking.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get the status for this booking.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Get the payment for this booking.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scope to filter by date range (from)
     */
    public function scopeDateFrom($query, $date)
    {
        return $query->where('booking_date', '>=', $date);
    }

    /**
     * Scope to filter by date range (to)
     */
    public function scopeDateTo($query, $date)
    {
        return $query->where('booking_date', '<=', $date);
    }

    /**
     * Scope to get pending bookings
     */
    public function scopePending($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('slug', 'pending');
        });
    }

    /**
     * Scope to get confirmed bookings
     */
    public function scopeConfirmed($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('slug', 'confirmed');
        });
    }

    /**
     * Scope to get completed bookings
     */
    public function scopeCompleted($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('slug', 'completed');
        });
    }

    /**
     * Scope to get cancelled bookings
     */
    public function scopeCancelled($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('slug', 'cancelled');
        });
    }
}
