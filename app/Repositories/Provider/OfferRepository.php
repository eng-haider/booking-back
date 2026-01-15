<?php

namespace App\Repositories\Provider;

use App\Models\Offer;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class OfferRepository
{
    /**
     * Get all offers for a provider's venues
     */
    public function getAllByProvider(int $providerId, array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;

        return QueryBuilder::for(Offer::class)
            ->whereHas('venue', function ($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            })
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('venue_id'),
                AllowedFilter::exact('discount_type'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::partial('title'),
                AllowedFilter::scope('active'),
                AllowedFilter::scope('available'),
            ])
            ->allowedSorts([
                'id',
                'title',
                'discount_value',
                'start_date',
                'end_date',
                'used_count',
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes(['venue'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    /**
     * Get all offers for a specific venue
     */
    public function getAllByVenue(int $venueId, int $providerId, array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;

        return QueryBuilder::for(Offer::class)
            ->where('venue_id', $venueId)
            ->whereHas('venue', function ($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            })
            ->allowedFilters([
                AllowedFilter::exact('is_active'),
                AllowedFilter::scope('active'),
                AllowedFilter::scope('available'),
            ])
            ->allowedSorts([
                'id',
                'title',
                'discount_value',
                'start_date',
                'end_date',
                'created_at',
            ])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    /**
     * Find offer by ID for specific provider
     */
    public function findByIdForProvider(int $offerId, int $providerId, array $relations = []): ?Offer
    {
        return Offer::whereHas('venue', function ($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            })
            ->with($relations)
            ->find($offerId);
    }

    /**
     * Create a new offer
     */
    public function create(array $data): Offer
    {
        return Offer::create($data);
    }

    /**
     * Update an offer
     */
    public function update(Offer $offer, array $data): bool
    {
        return $offer->update($data);
    }

    /**
     * Delete an offer
     */
    public function delete(Offer $offer): bool
    {
        return $offer->delete();
    }

    /**
     * Toggle offer active status
     */
    public function toggleActive(Offer $offer): bool
    {
        return $offer->update(['is_active' => !$offer->is_active]);
    }

    /**
     * Get active offers for a venue
     */
    public function getActiveOffersByVenue(int $venueId): Collection
    {
        return Offer::where('venue_id', $venueId)
            ->active()
            ->get();
    }

    /**
     * Get available offers for a venue (active and not maxed out)
     */
    public function getAvailableOffersByVenue(int $venueId): Collection
    {
        return Offer::where('venue_id', $venueId)
            ->available()
            ->get();
    }

    /**
     * Get offer statistics for provider
     */
    public function getStatisticsByProvider(int $providerId): array
    {
        $baseQuery = Offer::whereHas('venue', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        });

        return [
            'total_offers' => (clone $baseQuery)->count(),
            'active_offers' => (clone $baseQuery)->where('is_active', true)->count(),
            'expired_offers' => (clone $baseQuery)->where('end_date', '<', now())->count(),
            'upcoming_offers' => (clone $baseQuery)->where('start_date', '>', now())->count(),
            'total_uses' => (clone $baseQuery)->sum('used_count'),
        ];
    }

    /**
     * Increment offer usage
     */
    public function incrementUsage(Offer $offer): void
    {
        $offer->incrementUsedCount();
    }

    /**
     * Check if venue belongs to provider
     */
    public function venuebelongsToProvider(int $venueId, int $providerId): bool
    {
        return Venue::where('id', $venueId)
            ->where('provider_id', $providerId)
            ->exists();
    }
}
