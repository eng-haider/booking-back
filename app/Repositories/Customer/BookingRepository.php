<?php

namespace App\Repositories\Customer;

use App\Models\Booking;
use App\Models\Status;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BookingRepository
{
    /**
     * Get all bookings for a customer with filtering, sorting, and pagination.
     */
    public function getAllByCustomer(int $customerId): LengthAwarePaginator
    {
        $perPage = request('per_page', 15);
        
        return QueryBuilder::for(Booking::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('venue_id'),
                AllowedFilter::exact('status_id'),
                AllowedFilter::scope('date_from'),
                AllowedFilter::scope('date_to'),
                AllowedFilter::scope('pending'),
                AllowedFilter::scope('confirmed'),
                AllowedFilter::scope('completed'),
                AllowedFilter::scope('cancelled'),
            ])
            ->allowedSorts([
                'id',
                'booking_date',
                'start_time',
                'end_time',
                'total_price',
                'created_at',
            ])
            ->allowedIncludes([
                'venue',
                'venue.provider',
                'venue.country',
                'status',
                'payment',
            ])
            ->where('customer_id', $customerId)
            ->defaultSort('-booking_date', '-start_time')
            ->paginate($perPage);
    }

    /**
     * Find booking by ID for a customer.
     */
    public function findByIdForCustomer(int $bookingId, int $customerId)
    {
        return Booking::with([
            'venue.provider',
            'venue.country',
            'status',
            'payment',
        ])
            ->where('customer_id', $customerId)
            ->findOrFail($bookingId);
    }

    /**
     * Create a new booking.
     */
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // Get venue to calculate price
            $venue = \App\Models\Venue::findOrFail($data['venue_id']);
            
            // Calculate total price based on hours
            if ($venue->price_per_hour) {
                $startTime = \Carbon\Carbon::parse($data['start_time']);
                $endTime = \Carbon\Carbon::parse($data['end_time']);
                $hours = $endTime->diffInHours($startTime);
                $data['total_price'] = $venue->price_per_hour * $hours;
            }
            
            // Get pending status
            $pendingStatus = Status::where('slug', 'pending')->firstOrFail();
            
            $data['status_id'] = $pendingStatus->id;
            $data['booking_reference'] = $this->generateReference();

            return Booking::create($data);
        });
    }

    /**
     * Cancel a booking.
     */
    public function cancel(int $bookingId, int $customerId, string $reason = null): Booking
    {
        return DB::transaction(function () use ($bookingId, $customerId, $reason) {
            $booking = $this->findByIdForCustomer($bookingId, $customerId);

            // Get cancelled status
            $cancelledStatus = Status::where('slug', 'cancelled')->firstOrFail();

            $booking->update([
                'status_id' => $cancelledStatus->id,
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Check if time slot is available.
     */
    public function isTimeSlotAvailable(
        int $venueId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null
    ): bool {
        $query = Booking::where('venue_id', $venueId)
            ->where('booking_date', $date)
            ->whereHas('status', function ($q) {
                $q->whereIn('slug', ['confirmed', 'pending']);
            })
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return !$query->exists();
    }

    /**
     * Get customer statistics.
     */
    public function getStatisticsByCustomer(int $customerId): array
    {
        $completedStatus = Status::where('slug', 'completed')->first();
        $cancelledStatus = Status::where('slug', 'cancelled')->first();

        return [
            'total_bookings' => Booking::where('customer_id', $customerId)->count(),
            'completed_bookings' => Booking::where('customer_id', $customerId)
                ->where('status_id', $completedStatus?->id)
                ->count(),
            'cancelled_bookings' => Booking::where('customer_id', $customerId)
                ->where('status_id', $cancelledStatus?->id)
                ->count(),
            'total_spent' => Booking::where('customer_id', $customerId)
                ->where('status_id', $completedStatus?->id)
                ->sum('total_price'),
        ];
    }

    /**
     * Generate a unique booking reference.
     */
    private function generateReference(): string
    {
        do {
            $reference = 'BK' . strtoupper(substr(uniqid(), -8));
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }

    /**
     * Get upcoming bookings for a customer.
     */
    public function getUpcoming(int $customerId)
    {
        return Booking::where('customer_id', $customerId)
            ->whereHas('status', function ($q) {
                $q->whereIn('slug', ['confirmed', 'pending']);
            })
            ->where('booking_date', '>=', now()->toDateString())
            ->with(['venue', 'status'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get past bookings for a customer.
     */
    public function getPast(int $customerId)
    {
        return Booking::where('customer_id', $customerId)
            ->where(function ($q) {
                $q->where('booking_date', '<', now()->toDateString())
                    ->orWhereHas('status', function ($q2) {
                        $q2->whereIn('slug', ['completed', 'cancelled']);
                    });
            })
            ->with(['venue', 'status'])
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(15);
    }
}
