<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreVenueRequest;
use App\Http\Requests\Provider\UpdateVenueRequest;
use App\Models\Photo;
use App\Repositories\Provider\VenueRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VenueController extends Controller
{
    public function __construct(
        protected VenueRepository $venueRepository
    ) {
        $this->middleware(['permission:manage own venues'])->only(['index', 'show', 'myStatistics']);
        $this->middleware(['permission:create venues'])->only(['store']);
        $this->middleware(['permission:manage own venues'])->only(['update', 'updateStatus', 'syncAmenities', 'uploadPhoto', 'deletePhoto', 'setPrimaryPhoto']);
        $this->middleware(['permission:manage own venues'])->only(['destroy']);
    }

    /**
     * Display a listing of provider's venues.
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $venues = $this->venueRepository->getAllByProvider(
            $provider->id,
            request()->all()
        );

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
        $provider = $request->user()->provider;
        $data = $request->validated();
        
        // Set provider_id from authenticated user
        $data['provider_id'] = $provider->id;

        $venue = $this->venueRepository->create($data);

        // Sync amenities if provided (accept both 'amenities' and 'amenity_ids')
        $amenityIds = $data['amenity_ids'] ?? $data['amenities'] ?? null;
        if ($amenityIds) {
            $this->venueRepository->syncAmenities($venue, $amenityIds);
        }

        // Handle photo uploads if provided
        if ($request->hasFile('photos')) {
            $this->handlePhotoUploads($request->file('photos'), $venue);
        }

        return response()->json([
            'success' => true,
            'message' => 'Venue created successfully',
            'data' => $venue->load(['amenities', 'photos']),
        ], 201);
    }

    /**
     * Display the specified venue.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $venue = $this->venueRepository->findByIdForProvider(
            $id,
            $provider->id,
            ['resources', 'amenities', 'photos', 'bookings']
        );

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
        $provider = $request->user()->provider;
        
        $venue = $this->venueRepository->findByIdForProvider($id, $provider->id);

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

        // Sync amenities if provided (accept both 'amenities' and 'amenity_ids')
        $amenityIds = $data['amenity_ids'] ?? $data['amenities'] ?? null;
        if ($amenityIds) {
            $this->venueRepository->syncAmenities($venue, $amenityIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Venue updated successfully',
            'data' => $venue->fresh(['amenities', 'resources']),
        ]);
    }

    /**
     * Remove the specified venue.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $venue = $this->venueRepository->findByIdForProvider($id, $provider->id);

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
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $venue = $this->venueRepository->findByIdForProvider($id, $provider->id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $status = $request->input('status');

        if (!in_array($status, ['active', 'inactive'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status. Only active or inactive allowed.',
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
     * Get venue statistics.
     */
    public function statistics(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $venue = $this->venueRepository->findByIdForProvider($id, $provider->id);

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
     * Upload photos to a venue.
     */
    public function uploadPhoto(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $venue = $this->venueRepository->findByIdForProvider($id, $provider->id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $request->validate([
            'photos' => 'required|array|max:10',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'is_primary' => 'sometimes|boolean',
        ]);

        $uploadedPhotos = $this->handlePhotoUploads($request->file('photos'), $venue, $request->boolean('is_primary', false));

        return response()->json([
            'success' => true,
            'message' => count($uploadedPhotos) . ' photo(s) uploaded successfully',
            'data' => [
                'venue' => $venue->fresh(['photos']),
                'uploaded_photos' => $uploadedPhotos,
            ],
        ], 201);
    }

    /**
     * Delete a photo from a venue.
     */
    public function deletePhoto(Request $request, int $venueId, int $photoId): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $venue = $this->venueRepository->findByIdForProvider($venueId, $provider->id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $photo = Photo::where('id', $photoId)
            ->where('venue_id', $venue->id)
            ->first();

        if (!$photo) {
            return response()->json([
                'success' => false,
                'message' => 'Photo not found',
            ], 404);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($photo->path)) {
            Storage::disk('public')->delete($photo->path);
        }

        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully',
            'data' => $venue->fresh(['photos']),
        ]);
    }

    /**
     * Set a photo as primary for a venue.
     */
    public function setPrimaryPhoto(Request $request, int $venueId, int $photoId): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $venue = $this->venueRepository->findByIdForProvider($venueId, $provider->id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
            ], 404);
        }

        $photo = Photo::where('id', $photoId)
            ->where('venue_id', $venue->id)
            ->first();

        if (!$photo) {
            return response()->json([
                'success' => false,
                'message' => 'Photo not found',
            ], 404);
        }

        // Remove primary flag from all photos of this venue
        Photo::where('venue_id', $venue->id)->update(['is_primary' => false]);

        // Set this photo as primary
        $photo->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Primary photo updated successfully',
            'data' => $venue->fresh(['photos']),
        ]);
    }

    /**
     * Handle photo uploads for a venue.
     */
    private function handlePhotoUploads(array $photos, $venue, bool $isPrimary = false): array
    {
        $uploadedPhotos = [];
        $isFirstPhoto = $venue->photos()->count() === 0;

        foreach ($photos as $index => $photo) {
            // Generate unique filename
            $filename = 'venues/' . $venue->id . '/' . uniqid() . '_' . time() . '.' . $photo->getClientOriginalExtension();
            
            // Store photo in public disk
            $path = $photo->storeAs('venues/' . $venue->id, basename($filename), 'public');

            // Create photo record
            $photoRecord = Photo::create([
                'venue_id' => $venue->id,
                'path' => $path,
                'is_primary' => ($isFirstPhoto && $index === 0) || $isPrimary,
            ]);

            $uploadedPhotos[] = [
                'id' => $photoRecord->id,
                'path' => $path,
                'url' => asset('storage/' . $path),
                'is_primary' => $photoRecord->is_primary,
            ];
        }

        return $uploadedPhotos;
    }
}
