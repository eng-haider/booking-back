<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Get all reviews for provider's venues.
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;

        $reviews = Review::whereHas('venue', function ($query) use ($provider) {
                $query->where('provider_id', $provider->id);
            })
            ->with(['user:id,name', 'venue:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Get reviews for a specific venue.
     */
    public function getVenueReviews(Request $request, int $venueId): JsonResponse
    {
        $provider = $request->user()->provider;

        // Check if venue belongs to the provider
        $venue = $provider->venues()->find($venueId);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $reviews = Review::where('venue_id', $venueId)
            ->with(['user:id,name', 'customer:id,user_id'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'statistics' => [
                'average_rating' => round($venue->reviews()->avg('rating'), 1),
                'total_reviews' => $venue->reviews()->count(),
                'rating_distribution' => [
                    '5_star' => $venue->reviews()->where('rating', 5)->count(),
                    '4_star' => $venue->reviews()->where('rating', 4)->count(),
                    '3_star' => $venue->reviews()->where('rating', 3)->count(),
                    '2_star' => $venue->reviews()->where('rating', 2)->count(),
                    '1_star' => $venue->reviews()->where('rating', 1)->count(),
                ],
            ],
        ]);
    }

    /**
     * Get review statistics for all provider venues.
     */
    public function statistics(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;

        $totalReviews = Review::whereHas('venue', function ($query) use ($provider) {
            $query->where('provider_id', $provider->id);
        })->count();

        $averageRating = Review::whereHas('venue', function ($query) use ($provider) {
            $query->where('provider_id', $provider->id);
        })->avg('rating');

        $recentReviews = Review::whereHas('venue', function ($query) use ($provider) {
            $query->where('provider_id', $provider->id);
        })
            ->with(['user:id,name', 'venue:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_reviews' => $totalReviews,
                'average_rating' => round($averageRating, 1),
                'recent_reviews' => $recentReviews,
            ],
        ]);
    }
}
