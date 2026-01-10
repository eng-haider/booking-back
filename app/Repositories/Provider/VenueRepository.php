<?php

namespace App\Repositories\Provider;

use App\Models\Venue;
use App\Services\ScheduleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class VenueRepository
{
    public function __construct(
        protected ScheduleService $scheduleService
    ) {}

    /**
     * Get all venues for a provider
     */
    public function getAllByProvider(int $providerId, array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;

        return QueryBuilder::for(Venue::class)
            ->where('provider_id', $providerId)
            ->allowedFilters([
                AllowedFilter::partial('name'),
            AllowedFilter::exact('id'),
                AllowedFilter::partial('city'),
                AllowedFilter::partial('country'),
                AllowedFilter::exact('status'),
                AllowedFilter::scope('active'),
                AllowedFilter::scope('inactive'),
            ])
            ->allowedSorts([
                'id',
                'name',
                'city',
                'rating',
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes(['amenities', 'photos', 'bookings', 'bookings.customer', 'owner', 'category', 'reviews', 'reviews.customer', 'reviews.user'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    /**
     * Find venue by ID for specific provider
     */
    public function findByIdForProvider(int $venueId, int $providerId, array $relations = []): ?Venue
    {
        return Venue::where('id', $venueId)
            ->where('provider_id', $providerId)
            ->with($relations)
            ->first();
    }

    /**
     * Create a new venue
     */
    public function create(array $data): Venue
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            // Generate slug from category name or use a unique identifier
            $category = \App\Models\Category::find($data['category_id']);
            $baseSlug = $category ? Str::slug($category->name) : 'venue';
            $data['slug'] = $baseSlug . '-' . uniqid();
        }

        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Venue::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        return Venue::create($data);
    }

    /**
     * Update venue
     */
    public function update(Venue $venue, array $data): bool
    {
        return $venue->update($data);
    }

    /**
     * Delete venue
     */
    public function delete(Venue $venue): bool
    {
        return $venue->delete();
    }

    /**
     * Update venue status
     */
    public function updateStatus(Venue $venue, string $status): bool
    {
        return $venue->update(['status' => $status]);
    }

    /**
     * Check if slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Venue::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Sync amenities
     */
    public function syncAmenities(Venue $venue, array $amenityIds): void
    {
        $venue->amenities()->sync($amenityIds);
    }

    /**
     * Get venue statistics
     */
    public function getStatistics(Venue $venue): array
    {
        return [
            'total_bookings' => $venue->bookings()->count(),
            'pending_bookings' => $venue->bookings()->where('status', 'pending')->count(),
            'confirmed_bookings' => $venue->bookings()->where('status', 'confirmed')->count(),
            'completed_bookings' => $venue->bookings()->where('status', 'completed')->count(),
            'cancelled_bookings' => $venue->bookings()->where('status', 'cancelled')->count(),
            'total_revenue' => $venue->bookings()
                ->whereHas('payment', fn($q) => $q->where('status', 'paid'))
                ->sum('total_price'),
            'this_month_revenue' => $venue->bookings()
                ->whereHas('payment', fn($q) => $q->where('status', 'paid'))
                ->whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year)
                ->sum('total_price'),
            'average_rating' => $venue->reviews()->avg('rating') ?? 0,
            'total_reviews' => $venue->reviews()->count(),
        ];
    }

    /**
     * Create schedules for a venue.
     * 
     * @param Venue $venue
     * @param array|null $scheduleData
     * @return \Illuminate\Support\Collection
     */
    public function createSchedules(Venue $venue, ?array $scheduleData = null): \Illuminate\Support\Collection
    {
        if ($scheduleData && !empty($scheduleData)) {
            return $this->scheduleService->createSchedulesForVenue($venue, $scheduleData);
        }

        return $this->scheduleService->createDefaultSchedule($venue);
    }

    /**
     * Get available time periods for the venue.
     * 
     * @param Venue $venue
     * @param string|null $date Optional date to check real availability (Y-m-d format)
     * @return array
     */
    public function getAvailableTimePeriods(Venue $venue, ?string $date = null): array
    {
        return $this->scheduleService->getAllAvailableTimePeriods($venue, $date);
    }
}
