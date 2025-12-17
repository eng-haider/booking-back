<?php

namespace App\Repositories\Provider;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class BookingRepository
{
    /**
     * Get all bookings for provider's venues
     */
    public function getAllByProvider(int $providerId, array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;
        
        return QueryBuilder::for(Booking::class)
            ->whereHas('venue', function ($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            })
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('customer_id'),
                AllowedFilter::exact('venue_id'),
                AllowedFilter::exact('resource_id'),
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
                'total_amount',
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes(['customer', 'venue', 'resource', 'payment', 'user', 'status'])
            ->defaultSort('-booking_date')
            ->paginate($perPage);
    }

    /**
     * Find booking by ID for provider's venues
     */
    public function findByIdForProvider(int $bookingId, int $providerId, array $relations = []): ?Booking
    {
        return Booking::whereHas('venue', function ($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            })
            ->with($relations)
            ->find($bookingId);
    }

    /**
     * Get venue IDs for provider
     */
    private function getVenueIdsForProvider(int $providerId): array
    {
        return \App\Models\Venue::where('provider_id', $providerId)->pluck('id')->toArray();
    }

    /**
     * Confirm booking
     */
    public function confirm(Booking $booking): bool
    {
        $statusId = \App\Models\Status::where('name', 'confirmed')->value('id');
        
        return $booking->update([
            'status_id' => $statusId,
        ]);
    }

    /**
     * Cancel booking
     */
    public function cancel(Booking $booking, ?string $reason = null): bool
    {
        $statusId = \App\Models\Status::where('name', 'cancelled')->value('id');
        
        return $booking->update([
            'status_id' => $statusId,
        ]);
    }

    /**
     * Complete booking
     */
    public function complete(Booking $booking): bool
    {
        $statusId = \App\Models\Status::where('name', 'completed')->value('id');
        
        return $booking->update([
            'status_id' => $statusId,
        ]);
    }

    /**
     * Get upcoming bookings for provider
     */
    public function getUpcomingByProvider(int $providerId, int $limit = 10): Collection
    {
        return Booking::whereHas('venue', function ($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            })
            ->where('booking_date', '>=', now()->toDateString())
            ->whereIn('status_id', function($query) {
                $query->select('id')
                    ->from('statuses')
                    ->whereIn('name', ['pending', 'confirmed']);
            })
            ->with(['customer', 'venue'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    /**
     * Get today's bookings for provider
     */
    public function getTodayByProvider(int $providerId): Collection
    {
        $today = now()->toDateString();
        
        return Booking::whereHas('venue', function ($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            })
            ->where('booking_date', $today)
            ->with(['customer', 'venue'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get booking statistics for provider
     */
    public function getStatisticsByProvider(int $providerId): array
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $baseQuery = Booking::whereHas('venue', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        });

        return [
            'total_bookings' => (clone $baseQuery)->count(),
            'pending_bookings' => (clone $baseQuery)->whereHas('status', fn($q) => $q->where('name', 'pending'))->count(),
            'confirmed_bookings' => (clone $baseQuery)->whereHas('status', fn($q) => $q->where('name', 'confirmed'))->count(),
            'completed_bookings' => (clone $baseQuery)->whereHas('status', fn($q) => $q->where('name', 'completed'))->count(),
            'cancelled_bookings' => (clone $baseQuery)->whereHas('status', fn($q) => $q->where('name', 'cancelled'))->count(),
            'today_bookings' => (clone $baseQuery)->where('booking_date', $today)->count(),
            'this_month_bookings' => (clone $baseQuery)
                ->whereBetween('booking_date', [$startOfMonth, $endOfMonth])
                ->count(),
            'total_revenue' => (clone $baseQuery)
                ->sum('total_amount'),
            'this_month_revenue' => (clone $baseQuery)
                ->whereBetween('booking_date', [$startOfMonth, $endOfMonth])
                ->sum('total_amount'),
        ];
    }
}
