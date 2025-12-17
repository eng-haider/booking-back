<?php

namespace App\Repositories\Customer;

use App\Models\Venue;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class VenueRepository
{
    /**
     * Get all venues with filtering, sorting, and pagination.
     */
    public function getAll(): LengthAwarePaginator
    {
        return QueryBuilder::for(Venue::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('provider_id'),
                AllowedFilter::exact('venue_type_id'),
                AllowedFilter::exact('country_id'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('city'),
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
                'provider',
                'venueType',
                'country',
                'resources',
                'amenities',
                'photos',
                'schedules',
                'reviews',
            ])
            ->where('status', 'active')
            ->defaultSort('-created_at')
            ->paginate(request('per_page', 15));
    }

    /**
     * Find venue by ID.
     */
    public function findById(int $id)
    {
        return Venue::with([
            'provider',
            'venueType',
            'country',
            'resources',
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
            ->with(['provider', 'venueType', 'country', 'photos'])
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
            ->with(['provider', 'venueType', 'country', 'photos'])
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

        return $query->with(['provider', 'venueType', 'country', 'photos'])
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
            'resources' => $venue->resources,
        ];
    }
}
