<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Get all reviews for a specific venue.
     */
    public function getVenueReviews(int $venueId): JsonResponse
    {
        $venue = Venue::find($venueId);

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
            ],
        ]);
    }

    /**
     * Create a new review (Customer only).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'venue_id' => ['required', 'integer', 'exists:venues,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Check if user already reviewed this venue
        $existingReview = Review::where('user_id', $user->id)
            ->where('venue_id', $request->venue_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this venue',
            ], 422);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'customer_id' => null,
            'venue_id' => $request->venue_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review created successfully',
            'data' => $review->load(['user:id,name']),
        ], 201);
    }

    /**
     * Update an existing review.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        // Check if the review belongs to the authenticated user
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this review',
            ], 403);
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review->fresh(['user:id,name']),
        ]);
    }

    /**
     * Delete a review.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        // Check if the review belongs to the authenticated user
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this review',
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Get all reviews by the authenticated customer.
     */
    public function myReviews(Request $request): JsonResponse
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with(['venue:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }
}
