<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVenueRequest;
use App\Http\Requests\Admin\UpdateVenueRequest;
use App\Repositories\Admin\VenueRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function __construct(
        protected VenueRepository $venueRepository
    ) {
        // $this->middleware(['permission:view_venues'])->only(['index', 'show', 'search', 'featured', 'byCity', 'byProvider', 'byType', 'statistics', 'overallStatistics']);
        $this->middleware(['permission:create_venues'])->only(['store']);
        $this->middleware(['permission:edit_venues'])->only(['update', 'updateStatus', 'syncAmenities']);
        $this->middleware(['permission:delete_venues'])->only(['destroy']);
        $this->middleware(['permission:feature_venues'])->only(['toggleFeatured']);
    }

    /**
     * Display a listing of venues.
     */
    public function index(): JsonResponse
    {
        $venues = $this->venueRepository->getAll(request()->all());

        return response()->json([
            'success' => true,
            'data' => $venues,
        ]);
    }

    /**
     * Store a newly created venue.
     */
    public function store(StoreVenueRequest $request): JsonResponse
    {
        $data = $request->validated();

        $venue = $this->venueRepository->create($data);

        // Sync amenities if provided
        if (isset($data['amenity_ids'])) {
            $this->venueRepository->syncAmenities($venue, $data['amenity_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Venue created successfully',
            'data' => $venue->load(['provider', 'amenities']),
        ], 201);
    }

    /**
     * Display the specified venue.
     */
    public function show(int $id): JsonResponse
    {
        $venue = $this->venueRepository->findById($id, [
            'provider',
            'resources',
            'amenities',
            'photos',
            'bookings'
        ]);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $statistics = $this->venueRepository->getStatistics($venue);

        return response()->json([
            'success' => true,
            'data' => [
                'venue' => $venue,
                'statistics' => $statistics,
            ],
        ]);
    }

    /**
     * Update the specified venue.
     */
    public function update(UpdateVenueRequest $request, int $id): JsonResponse
    {
        $venue = $this->venueRepository->findById($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $data = $request->validated();

        // Handle slug update
        if (isset($data['slug']) && $data['slug'] !== $venue->slug) {
            if ($this->venueRepository->slugExists($data['slug'], $id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slug already exists',
                ], 422);
            }
        }

        $this->venueRepository->update($venue, $data);

        // Sync amenities if provided
        if (isset($data['amenity_ids'])) {
            $this->venueRepository->syncAmenities($venue, $data['amenity_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Venue updated successfully',
            'data' => $venue->fresh(['provider', 'amenities', 'resources']),
        ]);
    }

    /**
     * Remove the specified venue.
     */
    public function destroy(int $id): JsonResponse
    {
        $venue = $this->venueRepository->findById($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $this->venueRepository->delete($venue);

        return response()->json([
            'success' => true,
            'message' => 'Venue deleted successfully',
        ]);
    }

    /**
     * Update venue status.
     */
    public function updateStatus(int $id): JsonResponse
    {
        $venue = $this->venueRepository->findById($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $status = request()->input('status');

        if (!in_array($status, ['active', 'inactive', 'suspended'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status',
            ], 422);
        }

        $this->venueRepository->updateStatus($venue, $status);

        return response()->json([
            'success' => true,
            'message' => 'Venue status updated successfully',
            'data' => $venue->fresh(),
        ]);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(int $id): JsonResponse
    {
        $venue = $this->venueRepository->findById($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $this->venueRepository->toggleFeatured($venue);

        return response()->json([
            'success' => true,
            'message' => 'Featured status toggled successfully',
            'data' => $venue->fresh(),
        ]);
    }

    /**
     * Get venue statistics.
     */
    public function statistics(int $id): JsonResponse
    {
        $venue = $this->venueRepository->findById($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $statistics = $this->venueRepository->getStatistics($venue);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Search venues.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required',
            ], 422);
        }

        $venues = $this->venueRepository->search($query);

        return response()->json([
            'success' => true,
            'data' => $venues,
        ]);
    }

    /**
     * Get overall venue statistics.
     */
    public function overallStatistics(): JsonResponse
    {
        $statistics = $this->venueRepository->getOverallStatistics();

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get featured venues.
     */
    public function featured(): JsonResponse
    {
        $venues = $this->venueRepository->getFeatured();

        return response()->json([
            'success' => true,
            'data' => $venues,
        ]);
    }

    /**
     * Get venues by provider.
     */
    public function byProvider(int $providerId): JsonResponse
    {
        $venues = $this->venueRepository->getByProvider($providerId, ['resources']);

        return response()->json([
            'success' => true,
            'data' => $venues,
        ]);
    }

    /**
     * Get venues by city.
     */
    public function byCity(Request $request): JsonResponse
    {
        $city = $request->input('city');

        if (empty($city)) {
            return response()->json([
                'success' => false,
                'message' => 'City is required',
            ], 422);
        }

        $venues = $this->venueRepository->getByCity($city);

        return response()->json([
            'success' => true,
            'data' => $venues,
        ]);
    }

    /**
     * Sync amenities.
     */
    public function syncAmenities(Request $request, int $id): JsonResponse
    {
        $venue = $this->venueRepository->findById($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $amenityIds = $request->input('amenity_ids', []);

        $this->venueRepository->syncAmenities($venue, $amenityIds);

        return response()->json([
            'success' => true,
            'message' => 'Amenities synced successfully',
            'data' => $venue->fresh(['amenities']),
        ]);
    }
}
