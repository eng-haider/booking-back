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
        $this->middleware(['permission:create bookings'])->only(['store']);
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

        // Check if time slot is available
        if (!$this->bookingRepository->isTimeSlotAvailable(
            $data['resource_id'],
            $data['booking_date'],
            $data['start_time'],
            $data['end_time']
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Time slot is not available',
            ], 422);
        }

        // Generate booking reference
        $data['booking_reference'] = $this->bookingRepository->generateReference();

        $booking = $this->bookingRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking->load(['customer', 'user', 'venue', 'resource']),
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
            isset($data['resource_id']) || 
            isset($data['booking_date']) || 
            isset($data['start_time']) || 
            isset($data['end_time'])
        ) {
            $resourceId = $data['resource_id'] ?? $booking->resource_id;
            $date = $data['booking_date'] ?? $booking->booking_date;
            $startTime = $data['start_time'] ?? $booking->start_time;
            $endTime = $data['end_time'] ?? $booking->end_time;

            if (!$this->bookingRepository->isTimeSlotAvailable(
                $resourceId,
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
            'resource_id' => 'required|integer|exists:resources,id',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
        ]);

        $isAvailable = $this->bookingRepository->isTimeSlotAvailable(
            $request->resource_id,
            $request->booking_date,
            $request->start_time,
            $request->end_time
        );

        return response()->json([
            'success' => true,
            'data' => [
                'available' => $isAvailable,
            ],
        ]);
    }
}
