<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Repositories\Customer\VenueRepository;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    protected $venueRepository;

    public function __construct(VenueRepository $venueRepository)
    {
        $this->venueRepository = $venueRepository;
    }

    /**
     * Display a listing of venues.
     */
    public function index()
    {
        try {
            $venues = $this->venueRepository->getAll();

            return response()->json([
                'success' => true,
                'data' => $venues,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch venues',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified venue.
     */
    public function show($id)
    {
        try {
            $venue = $this->venueRepository->findById($id);

            return response()->json([
                'success' => true,
                'data' => $venue,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get featured venues.
     */
    public function featured()
    {
        try {
            $venues = $this->venueRepository->getFeatured();

            return response()->json([
                'success' => true,
                'data' => $venues,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch featured venues',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search venues.
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('query', '');
            $venues = $this->venueRepository->search($query);

            return response()->json([
                'success' => true,
                'data' => $venues,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get venues by location.
     */
    public function byLocation(Request $request)
    {
        try {
            $city = $request->input('city');
            $countryId = $request->input('country_id');

            $venues = $this->venueRepository->getByLocation($city, $countryId);

            return response()->json([
                'success' => true,
                'data' => $venues,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch venues by location',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get venue availability for a specific date.
     */
    public function availability($id, Request $request)
    {
        try {
            $date = $request->input('date', now()->toDateString());
            $availability = $this->venueRepository->getAvailability($id, $date);

            return response()->json([
                'success' => true,
                'data' => $availability,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch availability',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
