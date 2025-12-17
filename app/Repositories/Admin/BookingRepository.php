<?php

namespace App\Repositories\Admin;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BookingRepository
{
    /**
     * Get all bookings with filters, sorting, and pagination
     */
    public function getAll(array $params): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;

        return QueryBuilder::for(Booking::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('customer_id'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('venue_id'),
                AllowedFilter::exact('resource_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::scope('date_from'),
                AllowedFilter::scope('date_to'),
                AllowedFilter::scope('pending'),
                AllowedFilter::scope('confirmed'),
                AllowedFilter::scope('completed'),
                AllowedFilter::scope('cancelled'),
            ])
            ->allowedSorts(['id', 'booking_date', 'start_time', 'end_time', 'total_price', 'created_at', 'updated_at'])
            ->allowedIncludes(['customer', 'user', 'venue', 'resource', 'payment'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    /**
     * Find booking by ID
     */
    public function findById(int $id, array $relations = []): ?Booking
    {
        return Booking::with($relations)->find($id);
    }

    /**
     * Find booking by reference number
     */
    public function findByReference(string $reference): ?Booking
    {
        return Booking::where('booking_reference', $reference)->first();
    }

    /**
     * Get bookings by customer
     */
    public function getByCustomer(int $customerId, array $relations = []): Collection
    {
        return Booking::with($relations)
            ->where('customer_id', $customerId)
            ->orderBy('booking_date', 'desc')
            ->get();
    }

    /**
     * Get bookings by venue
     */
    public function getByVenue(int $venueId, array $relations = []): Collection
    {
        return Booking::with($relations)
            ->where('venue_id', $venueId)
            ->orderBy('booking_date', 'desc')
            ->get();
    }

    /**
     * Get bookings by resource
     */
    public function getByResource(int $resourceId, array $relations = []): Collection
    {
        return Booking::with($relations)
            ->where('resource_id', $resourceId)
            ->orderBy('booking_date', 'desc')
            ->get();
    }

    /**
     * Create a new booking
     */
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    /**
     * Update booking
     */
    public function update(Booking $booking, array $data): bool
    {
        return $booking->update($data);
    }

    /**
     * Delete booking
     */
    public function delete(Booking $booking): bool
    {
        return $booking->delete();
    }

    /**
     * Update booking status
     */
    public function updateStatus(Booking $booking, string $status): bool
    {
        return $booking->update(['status' => $status]);
    }

    /**
     * Cancel booking
     */
    public function cancel(Booking $booking, ?string $reason = null): bool
    {
        return $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Confirm booking
     */
    public function confirm(Booking $booking): bool
    {
        return $booking->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Complete booking
     */
    public function complete(Booking $booking): bool
    {
        return $booking->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Get booking statistics
     */
    public function getStatistics(Booking $booking): array
    {
        $duration = \Carbon\Carbon::parse($booking->start_time)
            ->diffInMinutes(\Carbon\Carbon::parse($booking->end_time));

        return [
            'duration_minutes' => $duration,
            'duration_hours' => round($duration / 60, 2),
            'payment_status' => $booking->payment?->status ?? 'unpaid',
            'days_until_booking' => now()->diffInDays($booking->booking_date, false),
            'is_past' => $booking->booking_date < now()->toDateString(),
        ];
    }

    /**
     * Check if time slot is available
     */
    public function isTimeSlotAvailable(
        int $resourceId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null
    ): bool {
        $query = Booking::where('resource_id', $resourceId)
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
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
     * Get overall statistics
     */
    public function getOverallStatistics(): array
    {
        $today = now()->toDateString();
        $thisMonth = now()->month;
        $thisYear = now()->year;

        return [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'today_bookings' => Booking::where('booking_date', $today)->count(),
            'this_month_bookings' => Booking::whereMonth('booking_date', $thisMonth)
                ->whereYear('booking_date', $thisYear)
                ->count(),
            'total_revenue' => Booking::whereHas('payment', function ($query) {
                    $query->where('status', 'paid');
                })->sum('total_price'),
            'this_month_revenue' => Booking::whereHas('payment', function ($query) {
                    $query->where('status', 'paid');
                })
                ->whereMonth('booking_date', $thisMonth)
                ->whereYear('booking_date', $thisYear)
                ->sum('total_price'),
        ];
    }

    /**
     * Get upcoming bookings
     */
    public function getUpcoming(int $limit = 10): Collection
    {
        return Booking::with(['customer', 'venue', 'resource'])
            ->where('booking_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    /**
     * Get past bookings
     */
    public function getPast(int $limit = 10): Collection
    {
        return Booking::with(['customer', 'venue', 'resource'])
            ->where('booking_date', '<', now()->toDateString())
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search bookings
     */
    public function search(string $query): Collection
    {
        return Booking::where(function ($q) use ($query) {
            $q->where('booking_reference', 'like', "%{$query}%")
              ->orWhereHas('customer', function ($q2) use ($query) {
                  $q2->where('first_name', 'like', "%{$query}%")
                     ->orWhere('last_name', 'like', "%{$query}%")
                     ->orWhere('email', 'like', "%{$query}%")
                     ->orWhere('phone', 'like', "%{$query}%");
              })
              ->orWhereHas('venue', function ($q2) use ($query) {
                  $q2->where('name', 'like', "%{$query}%");
              });
        })->with(['customer', 'venue'])
          ->limit(20)
          ->get();
    }

    /**
     * Generate unique booking reference
     */
    public function generateReference(): string
    {
        do {
            $reference = 'BK-' . strtoupper(uniqid());
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }
}
