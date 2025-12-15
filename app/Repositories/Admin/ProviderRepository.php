<?php

namespace App\Repositories\Admin;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ProviderRepository
{
    /**
     * Get all providers with filters, sorting, and includes.
     */
    public function getAll(array $params = []): Collection|QueryBuilder|LengthAwarePaginator
    {
        return QueryBuilder::for(Provider::class)
            ->allowedFilters([
            AllowedFilter::exact('id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
                AllowedFilter::partial('phone'),
                AllowedFilter::partial('city'),
                AllowedFilter::partial('country'),
            ])
            ->allowedSorts(['name', 'created_at', 'status'])
            ->allowedIncludes(['user', 'venues'])
            ->paginate($params['per_page'] ?? 15);
    }

    /**
     * Find provider by ID with optional relationships.
     */
    public function findById(int $id, array $with = []): ?Provider
    {
        $query = Provider::query();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Find provider by slug.
     */
    public function findBySlug(string $slug, array $with = []): ?Provider
    {
        $query = Provider::where('slug', $slug);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->first();
    }

    /**
     * Create a new provider.
     */
    public function create(array $data): Provider
    {
        return Provider::create($data);
    }

    /**
     * Update provider.
     */
    public function update(Provider $provider, array $data): bool
    {
        return $provider->update($data);
    }

    /**
     * Delete provider.
     */
    public function delete(Provider $provider): bool
    {
        return $provider->delete();
    }

    /**
     * Get providers by user ID.
     */
    public function getByUserId(int $userId): Collection
    {
        return Provider::where('user_id', $userId)
            ->with('venues')
            ->get();
    }

    /**
     * Get active providers.
     */
    public function getActive(): Collection
    {
        return Provider::active()->get();
    }

    /**
     * Check if slug exists.
     */
    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $query = Provider::where('slug', $slug);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Update provider status.
     */
    public function updateStatus(Provider $provider, string $status): bool
    {
        return $provider->update(['status' => $status]);
    }

    /**
     * Get provider statistics.
     */
    public function getStatistics(Provider $provider): array
    {
        return [
            'total_venues' => $provider->venues()->count(),
            'active_venues' => $provider->venues()->where('status', 'active')->count(),
            'total_bookings' => $provider->venues()
                ->withCount('bookings')
                ->get()
                ->sum('bookings_count'),
        ];
    }
}
