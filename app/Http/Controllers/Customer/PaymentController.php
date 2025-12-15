<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\InitiatePaymentRequest;
use App\Services\QiCardPaymentService;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentController extends Controller
{
    protected QiCardPaymentService $paymentService;

    public function __construct(QiCardPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Initiate payment for a booking
     *
     * @param InitiatePaymentRequest $request
     * @param int $bookingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePayment(InitiatePaymentRequest $request, int $bookingId)
    {
        try {
            $customer = $request->user();

            // Find booking and verify ownership
            $booking = Booking::where('id', $bookingId)
                ->where('customer_id', $customer->id)
                ->with(['venue', 'customer'])
                ->firstOrFail();

            // Check if booking already has a completed payment
            if ($booking->payment && $booking->payment->status->value === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking has already been paid',
                ], 400);
            }

            // Initiate payment
            $paymentData = $this->paymentService->initiatePayment($booking, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => $paymentData,
            ]);

        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Check if it's a configuration error
            if (str_contains($e->getMessage(), 'not configured')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway is not configured. Please contact support.',
                    'error' => config('app.debug') ? $e->getMessage() : 'Configuration error',
                ], 503);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Verify payment status
     *
     * @param Request $request
     * @param string $transactionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPayment(Request $request, string $transactionId)
    {
        try {
            $customer = $request->user();

            // Find payment by transaction ID
            $payment = Payment::where('transaction_ref', $transactionId)
                ->whereHas('booking', function ($query) use ($customer) {
                    $query->where('customer_id', $customer->id);
                })
                ->with(['booking'])
                ->firstOrFail();

            // Verify with payment gateway
            $verificationData = $this->paymentService->verifyPayment($transactionId);

            return response()->json([
                'success' => true,
                'message' => 'Payment verification completed',
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => $payment->status->value,
                    'amount' => $payment->amount,
                    'verification' => $verificationData,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Payment verification failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment details
     *
     * @param Request $request
     * @param int $paymentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentDetails(Request $request, int $paymentId)
    {
        try {
            $customer = $request->user();

            $payment = Payment::where('id', $paymentId)
                ->whereHas('booking', function ($query) use ($customer) {
                    $query->where('customer_id', $customer->id);
                })
                ->with(['booking.venue'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $payment->id,
                    'booking_id' => $payment->booking_id,
                    'method' => $payment->method,
                    'amount' => $payment->amount,
                    'status' => $payment->status->value,
                    'transaction_ref' => $payment->transaction_ref,
                    'paid_at' => $payment->paid_at?->toISOString(),
                    'created_at' => $payment->created_at->toISOString(),
                    'booking' => [
                        'id' => $payment->booking->id,
                        'venue_name' => $payment->booking->venue->name ?? 'N/A',
                        'booking_date' => $payment->booking->booking_date?->format('Y-m-d'),
                    ],
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Handle payment webhook from QiCard
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request)
    {
        try {
            Log::info('Payment webhook received', $request->all());

            // Process callback
            $payment = $this->paymentService->handleCallback($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
            ]);

        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'data' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle payment callback (return from payment gateway)
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        try {
            // QiCard redirects back with paymentId in the URL
            $paymentId = $request->input('paymentId') ?? $request->input('transaction_id') ?? $request->input('id');

            if (!$paymentId) {
                Log::warning('Payment callback received without paymentId', $request->all());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment ID not provided',
                ], 400);
            }

            Log::info('Payment callback received', [
                'paymentId' => $paymentId,
                'all_params' => $request->all()
            ]);

            // Verify payment status from QiCard API
            $verificationResult = $this->paymentService->verifyPayment($paymentId);

            // Find our payment record by transaction_ref (which is the QiCard paymentId)
            $payment = Payment::where('transaction_ref', $paymentId)->first();

            if (!$payment) {
                Log::error('Payment record not found', ['paymentId' => $paymentId]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => $payment->status->value,
                    'booking_id' => $payment->booking_id,
                    'amount' => $payment->amount,
                    'paid_at' => $payment->paid_at?->toISOString(),
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Payment callback failed', [
                'data' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment callback processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle payment cancellation
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request)
    {
        try {
            $transactionId = $request->input('transaction_id');

            if ($transactionId) {
                $payment = Payment::where('transaction_ref', $transactionId)->first();
                
                if ($payment) {
                    Log::info('Payment cancelled by user', [
                        'payment_id' => $payment->id,
                        'transaction_id' => $transactionId,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment cancelled',
            ]);

        } catch (Exception $e) {
            Log::error('Payment cancellation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process cancellation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment history for customer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        try {
            $customer = $request->user();

            $payments = Payment::whereHas('booking', function ($query) use ($customer) {
                    $query->where('customer_id', $customer->id);
                })
                ->with(['booking.venue'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $payments,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
