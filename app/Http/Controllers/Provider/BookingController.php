<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreBookingRequest;
use App\Repositories\Provider\BookingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected BookingRepository $bookingRepository
    ) {
        $this->middleware(['permission:manage own bookings'])->only(['index', 'show', 'upcoming', 'past', 'statistics']);
        // $this->middleware(['permission:create bookings'])->only(['store']);
        $this->middleware(['permission:confirm bookings'])->only(['confirm']);
        $this->middleware(['permission:cancel bookings'])->only(['cancel']);
        $this->middleware(['permission:complete bookings'])->only(['complete']);
    }

    /**
     * Display a listing of provider's bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $bookings = $this->bookingRepository->getAllByProvider(
            $provider->id,
            request()->all()
        );

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Store a newly created booking.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get venue to calculate end_time for availability check
        $venue = \App\Models\Venue::findOrFail($data['venue_id']);
        $bookingDuration = $venue->booking_duration_hours ?? 1;
        $startTime = \Carbon\Carbon::parse($data['start_time']);
        $endTime = $startTime->copy()->addHours($bookingDuration)->format('H:i:s');

        // Check if time slot is available
        if (!$this->bookingRepository->isTimeSlotAvailable(
            $data['venue_id'],
            $data['booking_date'],
            $data['start_time'],
            $endTime
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Time slot is not available',
            ], 422);
        }

        // Create booking (end_time and total_price calculated automatically in repository)
        $booking = $this->bookingRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking->load(['customer', 'venue', 'status']),
        ], 201);
    }

    /**
     * Display the specified booking.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $booking = $this->bookingRepository->findByIdForProvider(
            $id,
            $provider->id,
            ['customer', 'venue', 'resource', 'payment']
        );

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    /**
     * Confirm booking.
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $booking = $this->bookingRepository->findByIdForProvider($id, $provider->id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be confirmed',
            ], 422);
        }

        $this->bookingRepository->confirm($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking confirmed successfully',
            'data' => $booking->fresh(),
        ]);
    }

    /**
     * Cancel booking.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $booking = $this->bookingRepository->findByIdForProvider($id, $provider->id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel completed or already cancelled bookings',
            ], 422);
        }

        $reason = $request->input('reason');

        $this->bookingRepository->cancel($booking, $reason);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => $booking->fresh(),
        ]);
    }

    /**
     * Complete booking.
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $booking = $this->bookingRepository->findByIdForProvider($id, $provider->id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Only confirmed bookings can be completed',
            ], 422);
        }

        $this->bookingRepository->complete($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking completed successfully',
            'data' => $booking->fresh(),
        ]);
    }

    /**
     * Get upcoming bookings.
     */
    public function upcoming(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $bookings = $this->bookingRepository->getUpcomingByProvider($provider->id);

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Get today's bookings.
     */
    public function today(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        
        $bookings = $this->bookingRepository->getTodayByProvider($provider->id);

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Get booking statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;

        $statistics = $this->bookingRepository->getStatisticsByProvider($provider->id);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
