<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreOfferRequest;
use App\Http\Requests\Provider\UpdateOfferRequest;
use App\Repositories\Provider\OfferRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function __construct(
        protected OfferRepository $offerRepository
    ) {}

    /**
     * Get all offers for provider's venues.
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;

        $offers = $this->offerRepository->getAllByProvider(
            $provider->id,
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    /**
     * Get all offers for a specific venue.
     */
    public function venueOffers(Request $request, int $venueId): JsonResponse
    {
        $provider = $request->user()->provider;

        // Verify venue belongs to provider
        if (!$this->offerRepository->venuebelongsToProvider($venueId, $provider->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found or does not belong to you',
            ], 404);
        }

        $offers = $this->offerRepository->getAllByVenue(
            $venueId,
            $provider->id,
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    /**
     * Store a newly created offer.
     */
    public function store(StoreOfferRequest $request): JsonResponse
    {
        $provider = $request->user()->provider;
        $data = $request->validated();

        // Verify venue belongs to provider
        if (!$this->offerRepository->venuebelongsToProvider($data['venue_id'], $provider->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found or does not belong to you',
            ], 404);
        }

        $offer = $this->offerRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Offer created successfully',
            'data' => $offer->load('venue'),
        ], 201);
    }

    /**
     * Display the specified offer.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;

        $offer = $this->offerRepository->findByIdForProvider(
            $id,
            $provider->id,
            ['venue']
        );

        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $offer,
        ]);
    }

    /**
     * Update the specified offer.
     */
    public function update(UpdateOfferRequest $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;

        $offer = $this->offerRepository->findByIdForProvider($id, $provider->id);

        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found',
            ], 404);
        }

        $data = $request->validated();

        // If venue_id is being changed, verify it belongs to provider
        if (isset($data['venue_id']) && $data['venue_id'] != $offer->venue_id) {
            if (!$this->offerRepository->venuebelongsToProvider($data['venue_id'], $provider->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Venue not found or does not belong to you',
                ], 404);
            }
        }

        $this->offerRepository->update($offer, $data);

        return response()->json([
            'success' => true,
            'message' => 'Offer updated successfully',
            'data' => $offer->fresh(['venue']),
        ]);
    }

    /**
     * Remove the specified offer.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;

        $offer = $this->offerRepository->findByIdForProvider($id, $provider->id);

        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found',
            ], 404);
        }

        $this->offerRepository->delete($offer);

        return response()->json([
            'success' => true,
            'message' => 'Offer deleted successfully',
        ]);
    }

    /**
     * Toggle offer active status.
     */
    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;

        $offer = $this->offerRepository->findByIdForProvider($id, $provider->id);

        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found',
            ], 404);
        }

        $this->offerRepository->toggleActive($offer);

        return response()->json([
            'success' => true,
            'message' => 'Offer status updated successfully',
            'data' => $offer->fresh(),
        ]);
    }

    /**
     * Get offer statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;

        $statistics = $this->offerRepository->getStatisticsByProvider($provider->id);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get active offers for a specific venue.
     */
    public function activeVenueOffers(Request $request, int $venueId): JsonResponse
    {
        $provider = $request->user()->provider;

        // Verify venue belongs to provider
        if (!$this->offerRepository->venuebelongsToProvider($venueId, $provider->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found or does not belong to you',
            ], 404);
        }

        $offers = $this->offerRepository->getActiveOffersByVenue($venueId);

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }
}
