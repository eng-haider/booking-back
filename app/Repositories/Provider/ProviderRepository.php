<?php

namespace App\Repositories\Provider;

use App\Models\Provider;

class ProviderRepository
{
    /**
     * Find provider by ID
     */
    public function findById(int $id, array $relations = []): ?Provider
    {
        return Provider::with($relations)->find($id);
    }

    /**
     * Find provider by user ID
     */
    public function findByUserId(int $userId, array $relations = []): ?Provider
    {
        return Provider::with($relations)->where('user_id', $userId)->first();
    }

    /**
     * Update provider
     */
    public function update(Provider $provider, array $data): bool
    {
        return $provider->update($data);
    }

    /**
     * Get provider statistics
     */
    public function getStatistics(Provider $provider): array
    {
        return [
            'total_venues' => $provider->venues()->count(),
            'active_venues' => $provider->venues()->where('status', 'active')->count(),
            'total_resources' => $provider->venues()->withCount('resources')->get()->sum('resources_count'),
            'total_bookings' => $provider->venues()->withCount('bookings')->get()->sum('bookings_count'),
            'pending_bookings' => $provider->venues()
                ->with(['bookings' => fn($q) => $q->where('status', 'pending')])
                ->get()
                ->pluck('bookings')
                ->flatten()
                ->count(),
            'confirmed_bookings' => $provider->venues()
                ->with(['bookings' => fn($q) => $q->where('status', 'confirmed')])
                ->get()
                ->pluck('bookings')
                ->flatten()
                ->count(),
            'total_revenue' => $provider->venues()
                ->with(['bookings.payment' => fn($q) => $q->where('status', 'paid')])
                ->get()
                ->pluck('bookings')
                ->flatten()
                ->sum('total_price'),
            'this_month_revenue' => $provider->venues()
                ->with(['bookings' => function($q) {
                    $q->whereHas('payment', fn($pq) => $pq->where('status', 'paid'))
                      ->whereMonth('booking_date', now()->month)
                      ->whereYear('booking_date', now()->year);
                }])
                ->get()
                ->pluck('bookings')
                ->flatten()
                ->sum('total_price'),
        ];
    }
}
