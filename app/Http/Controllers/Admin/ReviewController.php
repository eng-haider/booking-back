<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\ReviewRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewRepository $reviewRepository
    ) {
        $this->middleware(['permission:view reviews'])->only(['index', 'show', 'statistics', 'recent', 'topRatedVenues']);
        $this->middleware(['permission:delete reviews'])->only(['destroy']);
    }

    /**
     * Display a listing of reviews with filters
     */
    public function index(Request $request): JsonResponse
    {
        // Query Builder will automatically handle query parameters
        $reviews = $this->reviewRepository->getAll($request->all());

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Display the specified review
     */
    public function show(int $id): JsonResponse
    {
        $review = $this->reviewRepository->findById($id, [
            'customer',
            'venue.provider',
        ]);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review,
        ]);
    }

    /**
     * Remove the specified review
     */
    public function destroy(int $id): JsonResponse
    {
        $review = $this->reviewRepository->findById($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        $this->reviewRepository->delete($review);

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Get review statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->only([
            'venue_id',
            'provider_id',
            'start_date',
            'end_date',
        ]);

        $statistics = $this->reviewRepository->getStatistics($filters);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get recent reviews
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $reviews = $this->reviewRepository->getRecent($limit);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Get top-rated venues based on reviews
     */
    public function topRatedVenues(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $venues = $this->reviewRepository->getTopRatedVenues($limit);

        return response()->json([
            'success' => true,
            'data' => $venues,
        ]);
    }

    /**
     * Get reviews for a specific venue
     */
    public function byVenue(int $venueId, Request $request): JsonResponse
    {
        $filters = array_merge(
            $request->only(['rating', 'min_rating', 'search', 'sort_by', 'sort_order', 'per_page']),
            ['venue_id' => $venueId]
        );

        $reviews = $this->reviewRepository->getAll($filters);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Get reviews by a specific customer
     */
    public function byCustomer(int $customerId, Request $request): JsonResponse
    {
        $filters = array_merge(
            $request->only(['rating', 'min_rating', 'search', 'sort_by', 'sort_order', 'per_page']),
            ['customer_id' => $customerId]
        );

        $reviews = $this->reviewRepository->getAll($filters);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Get reviews for a specific provider's venues
     */
    public function byProvider(int $providerId, Request $request): JsonResponse
    {
        $filters = array_merge(
            $request->only(['rating', 'min_rating', 'search', 'sort_by', 'sort_order', 'per_page']),
            ['provider_id' => $providerId]
        );

        $reviews = $this->reviewRepository->getAll($filters);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }
}
