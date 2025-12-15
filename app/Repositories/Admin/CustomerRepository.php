<?php

namespace App\Repositories\Admin;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class CustomerRepository
{
    /**
     * Get all customers with filters, sorting, and pagination
     */
    public function getAll(array $params): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;

        return QueryBuilder::for(Customer::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::partial('first_name'),
                AllowedFilter::partial('last_name'),
                AllowedFilter::partial('email'),
                AllowedFilter::partial('phone'),
                AllowedFilter::partial('city'),
                AllowedFilter::partial('country'),
                AllowedFilter::exact('status'),
                AllowedFilter::scope('verified'),
            ])
            ->allowedSorts(['id', 'first_name', 'last_name', 'email', 'created_at', 'updated_at'])
            ->allowedIncludes(['user', 'bookings'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    /**
     * Find customer by ID
     */
    public function findById(int $id, array $relations = []): ?Customer
    {
        return Customer::with($relations)->find($id);
    }

    /**
     * Find customer by user ID
     */
    public function findByUserId(int $userId, array $relations = []): ?Customer
    {
        return Customer::with($relations)->where('user_id', $userId)->first();
    }

    /**
     * Find customer by email
     */
    public function findByEmail(string $email): ?Customer
    {
        return Customer::where('email', $email)->first();
    }

    /**
     * Find customer by phone
     */
    public function findByPhone(string $phone): ?Customer
    {
        return Customer::where('phone', $phone)->first();
    }

    /**
     * Create a new customer
     */
    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    /**
     * Update customer
     */
    public function update(Customer $customer, array $data): bool
    {
        return $customer->update($data);
    }

    /**
     * Delete customer
     */
    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    /**
     * Update customer status
     */
    public function updateStatus(Customer $customer, string $status): bool
    {
        return $customer->update(['status' => $status]);
    }

    /**
     * Verify customer email
     */
    public function verifyEmail(Customer $customer): bool
    {
        return $customer->update([
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Get customer statistics
     */
    public function getStatistics(Customer $customer): array
    {
        return [
            'total_bookings' => $customer->bookings()->count(),
            'completed_bookings' => $customer->bookings()->where('status', 'completed')->count(),
            'cancelled_bookings' => $customer->bookings()->where('status', 'cancelled')->count(),
            'total_spent' => $customer->bookings()
                ->whereHas('payment', function ($query) {
                    $query->where('status', 'paid');
                })
                ->sum('total_price'),
        ];
    }

    /**
     * Get verified customers
     */
    public function getVerified(): Collection
    {
        return Customer::whereNotNull('email_verified_at')->get();
    }

    /**
     * Get unverified customers
     */
    public function getUnverified(): Collection
    {
        return Customer::whereNull('email_verified_at')->get();
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = Customer::where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if phone exists
     */
    public function phoneExists(string $phone, ?int $excludeId = null): bool
    {
        $query = Customer::where('phone', $phone);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Search customers
     */
    public function search(string $query): Collection
    {
        return Customer::where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%");
        })->limit(20)->get();
    }

    /**
     * Get overall statistics
     */
    public function getOverallStatistics(): array
    {
        return [
            'total_customers' => Customer::count(),
            'verified_customers' => Customer::whereNotNull('email_verified_at')->count(),
            'unverified_customers' => Customer::whereNull('email_verified_at')->count(),
            'active_customers' => Customer::where('status', 'active')->count(),
            'inactive_customers' => Customer::where('status', 'inactive')->count(),
            'new_this_month' => Customer::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }
}
