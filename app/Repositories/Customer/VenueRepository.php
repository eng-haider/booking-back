<?php

namespace App\Repositories\Customer;

use App\Models\Venue;
use App\Services\ScheduleService;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class VenueRepository
{
    public function __construct(
        protected ScheduleService $scheduleService
    ) {}

    /**
     * Get all venues with filtering, sorting, and pagination.
     */
    public function getAll(bool $hasOffers = false): LengthAwarePaginator
    {
        $query = QueryBuilder::for(Venue::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('provider_id'),
                AllowedFilter::exact('venue_type_id'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('category_id'),
                AllowedFilter::exact('provider.governorate_id'),
                AllowedFilter::scope('featured'),
            ])
            ->allowedSorts([
                'id',
                'name',
                'city',
                'base_price',
                'rating',
                'created_at',
            ])
            ->allowedIncludes([
                'owner',
                'provider',
                'venueType',
                'country',
                'category',
                'amenities',
                'photos',
                'schedules',
                'reviews',
                'reviews.customer',
                'activeOffers',
            ])
            ->where('status', 'active');

        // Filter by venues with active offers if requested
        if ($hasOffers) {
            $query->whereHas('offers', function($q) {
                $q->where('is_active', true)
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now())
                  ->where(function($query) {
                      $query->whereNull('max_uses')
                          ->orWhereColumn('used_count', '<', 'max_uses');
                  });
            });
        }

        return $query->defaultSort('-created_at')
            ->paginate(request('per_page', 15));
    }

    /**
     * Find venue by ID.
     */
    public function findById(int $id)
    {
        return Venue::with([
            'owner',
            'venueType',
            'country',
            'amenities',
            'photos',
            'schedules',
            'reviews.customer',
        ])->where('status', 'active')->findOrFail($id);
    }

    /**
     * Get featured venues.
     */
    public function getFeatured(int $limit = 10)
    {
        return Venue::where('status', 'active')
            ->featured()
            ->with(['owner', 'venueType', 'country', 'photos'])
            ->limit($limit)
            ->get();
    }

    /**
     * Search venues.
     */
    public function search(string $query)
    {
        return Venue::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('city', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%");
            })
            ->with(['owner', 'venueType', 'country', 'photos'])
            ->paginate(15);
    }

    /**
     * Get venues by location.
     */
    public function getByLocation(string $city = null, int $countryId = null)
    {
        $query = Venue::where('status', 'active');

        if ($city) {
            $query->where('city', $city);
        }

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        return $query->with(['owner', 'venueType', 'country', 'photos'])
            ->paginate(15);
    }

    /**
     * Get venue availability for a date.
     */
    public function getAvailability(int $venueId, string $date)
    {
        $venue = $this->findById($venueId);
        
        $bookings = $venue->bookings()
            ->where('booking_date', $date)
            ->whereHas('status', function ($q) {
                $q->whereIn('slug', ['confirmed', 'pending']);
            })
            ->get();

        return [
            'venue' => $venue,
            'date' => $date,
            'bookings' => $bookings,
        ];
    }

    /**
     * Get available time periods for a venue.
     * 
     * @param int $venueId
     * @param string|null $date Optional date to check real availability (Y-m-d format)
     * @return array
     */
    public function getAvailableTimePeriods(int $venueId, ?string $date = null): array
    {
        $venue = Venue::with('schedules')
            ->where('status', 'active')
            ->findOrFail($venueId);

        return $this->scheduleService->getAllAvailableTimePeriods($venue, $date);
    }
}
