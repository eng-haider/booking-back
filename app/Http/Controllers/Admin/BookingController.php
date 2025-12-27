<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBookingRequest;
use App\Http\Requests\Admin\UpdateBookingRequest;
use App\Repositories\Admin\BookingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected BookingRepository $bookingRepository
    ) {
        $this->middleware(['permission:view bookings'])->only(['index', 'show', 'search', 'upcoming', 'past', 'statistics', 'overallStatistics']);
        // $this->middleware(['permission:create bookings'])->only(['store']);
        $this->middleware(['permission:edit bookings'])->only(['update', 'updateStatus']);
        $this->middleware(['permission:delete bookings'])->only(['destroy']);
        $this->middleware(['permission:confirm bookings'])->only(['confirm']);
        $this->middleware(['permission:cancel bookings'])->only(['cancel']);
        $this->middleware(['permission:complete bookings'])->only(['complete']);
        $this->middleware(['permission:check availability'])->only(['checkAvailability']);
    }

    /**
     * Display a listing of bookings.
     */
    public function index(): JsonResponse
    {
        $bookings = $this->bookingRepository->getAll(request()->all());

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
            'data' => $booking->load(['customer', 'user', 'venue']),
        ], 201);
    }

    /**
     * Display the specified booking.
     */
    public function show(int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findById($id, [
            'customer',
            'user',
            'venue',
            'resource',
            'payment'
        ]);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        $statistics = $this->bookingRepository->getStatistics($booking);

        return response()->json([
            'success' => true,
            'data' => [
                'booking' => $booking,
                'statistics' => $statistics,
            ],
        ]);
    }

    /**
     * Update the specified booking.
     */
    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findById($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        $data = $request->validated();

        // Check if time slot is available (if time/date changed)
        if (
            isset($data['venue_id']) || 
            isset($data['booking_date']) || 
            isset($data['start_time'])
        ) {
            $venueId = $data['venue_id'] ?? $booking->venue_id;
            $date = $data['booking_date'] ?? $booking->booking_date;
            $startTime = $data['start_time'] ?? $booking->start_time;
            
            // Get venue to calculate end_time
            $venue = \App\Models\Venue::findOrFail($venueId);
            $bookingDuration = $venue->booking_duration_hours ?? 1;
            $start = \Carbon\Carbon::parse($startTime);
            $endTime = $start->copy()->addHours($bookingDuration)->format('H:i:s');

            if (!$this->bookingRepository->isTimeSlotAvailable(
                $venueId,
                $date,
                $startTime,
                $endTime,
                $id
            )) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time slot is not available',
                ], 422);
            }
            
            // Update end_time in data
            $data['end_time'] = $endTime;
        }

        $this->bookingRepository->update($booking, $data);

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => $booking->fresh(['customer', 'user', 'venue', 'resource', 'payment']),
        ]);
    }

    /**
     * Remove the specified booking.
     */
    public function destroy(int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findById($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        $this->bookingRepository->delete($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking deleted successfully',
        ]);
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findById($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        $status = $request->input('status');

        if (!in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status',
            ], 422);
        }

        $this->bookingRepository->updateStatus($booking, $status);

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully',
            'data' => $booking->fresh(),
        ]);
    }

    /**
     * Confirm booking.
     */
    public function confirm(int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findById($id);

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
        $booking = $this->bookingRepository->findById($id);

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
    public function complete(int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findById($id);

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
     * Get booking statistics.
     */
    public function statistics(int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findById($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        $statistics = $this->bookingRepository->getStatistics($booking);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Search bookings.
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

        $bookings = $this->bookingRepository->search($query);

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Get overall booking statistics.
     */
    public function overallStatistics(): JsonResponse
    {
        $statistics = $this->bookingRepository->getOverallStatistics();

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get upcoming bookings.
     */
    public function upcoming(): JsonResponse
    {
        $limit = request()->input('limit', 10);
        $bookings = $this->bookingRepository->getUpcoming($limit);

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Get past bookings.
     */
    public function past(): JsonResponse
    {
        $limit = request()->input('limit', 10);
        $bookings = $this->bookingRepository->getPast($limit);

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Check time slot availability.
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'venue_id' => 'required|integer|exists:venues,id',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i:s',
        ]);

        // Get venue to calculate end_time
        $venue = \App\Models\Venue::findOrFail($request->venue_id);
        $bookingDuration = $venue->booking_duration_hours ?? 1;
        $startTime = \Carbon\Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addHours($bookingDuration)->format('H:i:s');

        $isAvailable = $this->bookingRepository->isTimeSlotAvailable(
            $request->venue_id,
            $request->booking_date,
            $request->start_time,
            $endTime
        );

        return response()->json([
            'success' => true,
            'data' => [
                'available' => $isAvailable,
                'booking_duration_hours' => $bookingDuration,
                'calculated_end_time' => $endTime,
            ],
        ]);
    }
}
