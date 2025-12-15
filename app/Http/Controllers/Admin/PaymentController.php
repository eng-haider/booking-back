<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Services\QiCardPaymentService;
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
     * Get all payments with filtering and pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Payment::with(['booking.customer', 'booking.venue']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by payment method
            if ($request->has('method')) {
                $query->where('method', $request->method);
            }

            // Filter by date range
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Search by transaction reference
            if ($request->has('search')) {
                $query->where('transaction_ref', 'like', '%' . $request->search . '%');
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $payments = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => PaymentResource::collection($payments),
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'last_page' => $payments->lastPage(),
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch payments', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $payment = Payment::with(['booking.customer', 'booking.venue'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new PaymentResource($payment),
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
     * Manually verify payment status with gateway
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(int $id)
    {
        try {
            $payment = Payment::findOrFail($id);

            if (!$payment->transaction_ref) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment has no transaction reference',
                ], 400);
            }

            $verificationData = $this->paymentService->verifyPayment($payment->transaction_ref);

            return response()->json([
                'success' => true,
                'message' => 'Payment verification completed',
                'data' => [
                    'payment' => new PaymentResource($payment->fresh()),
                    'verification' => $verificationData,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Admin payment verification failed', [
                'payment_id' => $id,
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
     * Process refund for a payment
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function refund(Request $request, int $id)
    {
        try {
            $payment = Payment::with('booking')->findOrFail($id);

            $validated = $request->validate([
                'amount' => 'nullable|numeric|min:0|max:' . $payment->amount,
                'reason' => 'required|string|max:500',
            ]);

            $result = $this->paymentService->refundPayment(
                $payment,
                $validated['amount'] ?? null,
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment refunded successfully',
                'data' => [
                    'payment' => new PaymentResource($payment->fresh()),
                    'refund' => $result,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Admin payment refund failed', [
                'payment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        try {
            $dateFrom = $request->get('date_from', now()->subDays(30));
            $dateTo = $request->get('date_to', now());

            $statistics = [
                'total_payments' => Payment::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'completed_payments' => Payment::where('status', 'completed')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'pending_payments' => Payment::where('status', 'pending')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'failed_payments' => Payment::where('status', 'failed')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'refunded_payments' => Payment::where('status', 'refunded')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'total_revenue' => Payment::where('status', 'completed')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('amount'),
                'total_refunded' => Payment::where('status', 'refunded')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('amount'),
                'success_rate' => 0,
            ];

            // Calculate success rate
            if ($statistics['total_payments'] > 0) {
                $statistics['success_rate'] = round(
                    ($statistics['completed_payments'] / $statistics['total_payments']) * 100,
                    2
                );
            }

            // Get payment method breakdown
            $statistics['by_method'] = Payment::selectRaw('method, COUNT(*) as count, SUM(amount) as total')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->groupBy('method')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch payment statistics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent payment activities
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentActivities(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);

            $payments = Payment::with(['booking.customer', 'booking.venue'])
                ->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => PaymentResource::collection($payments),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activities',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
