<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreBookingRequest;
use App\Repositories\Customer\BookingRepository;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * Display a listing of bookings for the authenticated customer.
     */
    public function index(Request $request)
    {
        try {
            $customer = $request->user();

            $bookings = $this->bookingRepository->getAllByCustomer($customer->id);

            return response()->json([
                'success' => true,
                'data' => $bookings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bookings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created booking.
     */
    public function store(StoreBookingRequest $request)
    {
        try {
            $customer = $request->user();

            $data = $request->validated();
            $data['customer_id'] = $customer->id;

            // Check availability
            $isAvailable = $this->bookingRepository->isTimeSlotAvailable(
                $data['venue_id'],
                $data['booking_date'],
                $data['start_time'],
                $data['end_time']
            );

            if (!$isAvailable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time slot is not available',
                ], 422);
            }

            $booking = $this->bookingRepository->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking->load(['venue', 'status']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(Request $request, $id)
    {
        try {
            $customer = $request->user();

            $booking = $this->bookingRepository->findByIdForCustomer($id, $customer->id);

            return response()->json([
                'success' => true,
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, $id)
    {
        try {
            $customer = $request->user();

            $request->validate([
                'cancellation_reason' => 'nullable|string|max:500',
            ]);

            $booking = $this->bookingRepository->cancel(
                $id,
                $customer->id,
                $request->input('cancellation_reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get upcoming bookings.
     */
    public function upcoming(Request $request)
    {
        try {
            $customer = $request->user();

            $bookings = $this->bookingRepository->getUpcoming($customer->id);

            return response()->json([
                'success' => true,
                'data' => $bookings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch upcoming bookings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get past bookings.
     */
    public function past(Request $request)
    {
        try {
            $customer = $request->user();

            $bookings = $this->bookingRepository->getPast($customer->id);

            return response()->json([
                'success' => true,
                'data' => $bookings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch past bookings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get booking statistics for the customer.
     */
    public function statistics(Request $request)
    {
        try {
            $customer = $request->user();

            $statistics = $this->bookingRepository->getStatisticsByCustomer($customer->id);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
