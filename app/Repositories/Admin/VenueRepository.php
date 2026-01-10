<?php

namespace App\Repositories\Admin;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Str;

class VenueRepository
{
    /**
     * Get all venues with filters, sorting, and pagination
     */
    public function getAll(array $params): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;

        return QueryBuilder::for(Venue::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('provider_id'),
                AllowedFilter::exact('venue_type_id'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('city'),
                AllowedFilter::partial('country'),
                AllowedFilter::exact('status'),
                AllowedFilter::scope('active'),
                AllowedFilter::scope('inactive'),
                AllowedFilter::scope('suspended'),
            ])
            ->allowedSorts(['id', 'name', 'city', 'rating', 'created_at', 'updated_at'])
            ->allowedIncludes([
                'owner',
                'venueType',
                'category',
                'country',
                'resources',
                'amenities',
                'photos',
                'bookings',
                'bookings.customer',
                'bookings.status',
                'bookings.venue',
                'reviews',
                'reviews.customer',
            ])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    /**
     * Find venue by ID
     */
    public function findById(int $id, array $relations = []): ?Venue
    {
        return Venue::with($relations)->find($id);
    }

    /**
     * Find venue by slug
     */
    public function findBySlug(string $slug, array $relations = []): ?Venue
    {
        return Venue::with($relations)->where('slug', $slug)->first();
    }

    /**
     * Get venues by provider
     */
    public function getByProvider(int $providerId, array $relations = []): Collection
    {
        return Venue::with($relations)
            ->where('provider_id', $providerId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get venues by type
     */
    public function getByType(int $venueTypeId, array $relations = []): Collection
    {
        return Venue::with($relations)
            ->where('venue_type_id', $venueTypeId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new venue
     */
    public function create(array $data): Venue
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while ($this->slugExists($data['slug'])) {
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
     * Get venue statistics
     */
    public function getStatistics(Venue $venue): array
    {
        return [
            'total_bookings' => $venue->bookings()->count(),
            'pending_bookings' => $venue->bookings()->where('status', 'pending')->count(),
            'confirmed_bookings' => $venue->bookings()->where('status', 'confirmed')->count(),
            'completed_bookings' => $venue->bookings()->where('status', 'completed')->count(),
            'total_revenue' => $venue->bookings()
                ->whereHas('payment', function ($query) {
                    $query->where('status', 'paid');
                })
                ->sum('total_price'),
            'average_rating' => $venue->reviews()->avg('rating') ?? 0,
            'total_reviews' => $venue->reviews()->count(),
            'total_amenities' => $venue->amenities()->count(),
            'total_photos' => $venue->photos()->count(),
        ];
    }

    /**
     * Get active venues
     */
    public function getActive(): Collection
    {
        return Venue::active()->with(['owner', 'venueType'])->get();
    }

    /**
     * Get featured venues
     */
    public function getFeatured(): Collection
    {
        return Venue::where('is_featured', true)
            ->where('status', 'active')
            ->with(['owner', 'venueType', 'photos'])
            ->get();
    }

    /**
     * Search venues
     */
    public function search(string $query): Collection
    {
        return Venue::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%")
              ->orWhere('city', 'like', "%{$query}%")
              ->orWhere('address', 'like', "%{$query}%");
        })->with(['owner', 'venueType'])
          ->limit(20)
          ->get();
    }

    /**
     * Get overall statistics
     */
    public function getOverallStatistics(): array
    {
        return [
            'total_venues' => Venue::count(),
            'active_venues' => Venue::where('status', 'active')->count(),
            'inactive_venues' => Venue::where('status', 'inactive')->count(),
            'suspended_venues' => Venue::where('status', 'suspended')->count(),
            'featured_venues' => Venue::where('is_featured', true)->count(),
            'new_this_month' => Venue::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'total_capacity' => Venue::sum('capacity'),
            'average_rating' => Venue::avg('rating') ?? 0,
        ];
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Venue $venue): bool
    {
        return $venue->update(['is_featured' => !$venue->is_featured]);
    }

    /**
     * Attach amenities to venue
     */
    public function syncAmenities(Venue $venue, array $amenityIds): void
    {
        $venue->amenities()->sync($amenityIds);
    }

    /**
     * Get venues by city
     */
    public function getByCity(string $city): Collection
    {
        return Venue::where('city', $city)
            ->where('status', 'active')
            ->with(['owner', 'venueType'])
            ->get();
    }

    /**
     * Get venues by country
     */
    public function getByCountry(string $country): Collection
    {
        return Venue::where('country', $country)
            ->where('status', 'active')
            ->with(['owner', 'venueType'])
            ->get();
    }
}
