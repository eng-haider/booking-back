<?php

namespace App\Repositories\Customer;

use App\Models\Provider;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ProviderRepository
{
    /**
     * Get all providers with filtering, sorting, and pagination.
     */
    public function getAll(): LengthAwarePaginator
    {
        return QueryBuilder::for(Provider::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('address'),
                AllowedFilter::exact('governorate_id'),
                AllowedFilter::exact('status'),
                'email',
                'phone',
            ])
            ->allowedSorts([
                'id',
                'name',
                'created_at',
            ])
            ->allowedIncludes([
                'user',
                'governorate',
                'venues',
            ])
            // ->where('status', 'approved')
            ->defaultSort('-created_at')
            ->paginate(request('per_page', 15));
    }

    /**
     * Find provider by ID.
     */
    public function findById(int $id)
    {
        return Provider::with([
            'user',
            'governorate',
            'venues',
        ])->where('status', 'approved')->findOrFail($id);
    }

    /**
     * Get provider statistics.
     */
    public function getStatistics(int $providerId): array
    {
        $provider = Provider::with('venues')->findOrFail($providerId);

        return [
            'total_venues' => $provider->venues()->count(),
            'active_venues' => $provider->venues()->where('status', 'active')->count(),
            'average_rating' => $provider->venues()->avg('rating'),
            'total_bookings' => $provider->venues()->withCount('bookings')->get()->sum('bookings_count'),
        ];
    }

    /**
     * Search providers.
     */
    public function search(string $query)
    {
        return Provider::where('status', 'approved')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%");
            })
            ->with(['governorate', 'venues'])
            ->paginate(15);
    }
}
