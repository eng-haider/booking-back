<?php

namespace App\Repositories\Admin;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
     * Create a new provider along with user account.
     */
    public function create(array $data): Provider
    {
        return DB::transaction(function () use ($data) {
            // Create user account first
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role' => 'owner',
            ]);

            // Assign provider/owner role using Spatie (web guard for users table)
            $user->assignRole('owner', 'web');

            // Create provider with the new user_id
            $provider = Provider::create([
                'user_id' => $user->id,
                'name' => $data['provider_name'] ?? $data['name'],
                'slug' => $data['slug'] ?? null,
                'description' => $data['description'] ?? null,
                'email' => $data['provider_email'] ?? $data['email'],
                'phone' => $data['provider_phone'] ?? $data['phone'],
                'address' => $data['address'] ?? null,
                'governorate_id' => $data['governorate_id'] ?? null,
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
                'website' => $data['website'] ?? null,
                'logo' => $data['logo'] ?? null,
                'license_number' => $data['license_number'] ?? null,
                'status' => $data['status'] ?? 'active',
                'settings' => $data['settings'] ?? null,
            ]);

            return $provider->load('user');
        });
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
