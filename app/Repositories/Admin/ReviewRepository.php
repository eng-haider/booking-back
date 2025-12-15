<?php

namespace App\Repositories\Admin;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ReviewRepository
{
    /**
     * Get all reviews with filters and pagination
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;

        return QueryBuilder::for(Review::class)
            ->allowedIncludes(['customer', 'venue', 'venue.provider'])
            ->allowedFilters([
                AllowedFilter::exact('venue_id'),
                AllowedFilter::exact('customer_id'),
                AllowedFilter::exact('rating'),
                AllowedFilter::callback('min_rating', function ($query, $value) {
                    $query->where('rating', '>=', $value);
                }),
                AllowedFilter::callback('provider_id', function ($query, $value) {
                    $query->whereHas('venue', function ($q) use ($value) {
                        $q->where('provider_id', $value);
                    });
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('comment', 'like', '%' . $value . '%');
                }),
            ])
            ->allowedSorts(['rating', 'created_at', 'updated_at'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    /**
     * Find review by ID with relationships
     */
    public function findById(int $id, array $with = []): ?Review
    {
        $query = Review::query();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Delete a review
     */
    public function delete(Review $review): bool
    {
        return $review->delete();
    }

    /**
     * Get review statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $query = DB::table('reviews');

        // Apply filters
        if (isset($filters['venue_id'])) {
            $query->where('venue_id', $filters['venue_id']);
        }

        if (isset($filters['provider_id'])) {
            $query->join('venues', 'reviews.venue_id', '=', 'venues.id')
                  ->where('venues.provider_id', $filters['provider_id']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('reviews.created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('reviews.created_at', '<=', $filters['end_date']);
        }

        $totalReviews = (clone $query)->count();
        $averageRating = (clone $query)->avg('reviews.rating');

        $ratingDistribution = [
            '5_star' => (clone $query)->where('reviews.rating', 5)->count(),
            '4_star' => (clone $query)->where('reviews.rating', 4)->count(),
            '3_star' => (clone $query)->where('reviews.rating', 3)->count(),
            '2_star' => (clone $query)->where('reviews.rating', 2)->count(),
            '1_star' => (clone $query)->where('reviews.rating', 1)->count(),
        ];

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => round($averageRating ?? 0, 2),
            'rating_distribution' => $ratingDistribution,
        ];
    }

    /**
     * Get recent reviews
     */
    public function getRecent(int $limit = 10): Collection
    {
        return Review::with(['customer', 'venue'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top-rated venues based on reviews
     */
    public function getTopRatedVenues(int $limit = 10): array
    {
        return DB::table('reviews')
            ->select(
                'venue_id',
                DB::raw('AVG(rating) as avg_rating'),
                DB::raw('COUNT(*) as review_count')
            )
            ->groupBy('venue_id')
            ->having('review_count', '>=', 3)
            ->orderBy('avg_rating', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $venue = \App\Models\Venue::with('provider')->find($item->venue_id);
                return [
                    'venue' => $venue,
                    'avg_rating' => round($item->avg_rating, 2),
                    'review_count' => $item->review_count,
                ];
            })
            ->toArray();
    }
}
