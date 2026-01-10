<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Booking;
use App\Enums\PaymentStatus;
use App\Events\PaymentCompleted;
use App\Events\PaymentFailed;
use App\Events\PaymentRefunded;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class QiCardPaymentService
{
    protected ?string $apiUrl;
    protected ?string $username;
    protected ?string $password;
    protected ?string $terminalId;
    protected string $currency;
    protected ?string $publicKeyPath;
    protected bool $verifyWebhooks;
    protected ?string $returnUrl;
    protected ?string $cancelUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.qicard.api_url');
        $this->username = config('services.qicard.username');
        $this->password = config('services.qicard.password');
        $this->terminalId = config('services.qicard.terminal_id');
        $this->currency = config('services.qicard.currency', 'IQD');
        $this->publicKeyPath = config('services.qicard.public_key_path');
        $this->verifyWebhooks = config('services.qicard.verify_webhooks', false);
        $this->returnUrl = config('services.qicard.return_url');
        $this->cancelUrl = config('services.qicard.cancel_url');
        
        // Validate configuration
        $this->validateConfiguration();
    }

    /**
     * Validate QiCard configuration
     *
     * @throws Exception
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->apiUrl)) {
            throw new Exception('QiCard API URL is not configured. Please set QICARD_BASE_URL in your .env file.');
        }
        
        if (empty($this->username)) {
            throw new Exception('QiCard Username is not configured. Please set QICARD_USERNAME in your .env file.');
        }
        
        if (empty($this->password)) {
            throw new Exception('QiCard Password is not configured. Please set QICARD_PASSWORD in your .env file.');
        }
        
        if (empty($this->terminalId)) {
            throw new Exception('QiCard Terminal ID is not configured. Please set QICARD_TERMINAL_ID in your .env file.');
        }
    }

    /**
     * Initialize a payment transaction
     *
     * @param Booking $booking
     * @param array $additionalData
     * @return array
     * @throws Exception
     */
    public function initiatePayment(Booking $booking, array $additionalData = []): array
    {
        try {
            // Validate booking has a valid total price
            if (empty($booking->total_price) || $booking->total_price <= 0) {
                throw new Exception('Booking total price must be greater than 0. Current value: ' . ($booking->total_price ?? 'null'));
            }

            $orderId = $this->generateOrderId($booking);
            $requestId = 'REQ-' . $booking->id . '-' . time(); // Unique request ID
            
            // QiCard Iraq API structure
            // Required: requestId, amount, currency
            // Optional: notificationUrl (webhook URL)
            // Terminal ID goes in X-Terminal-Id header
            $paymentData = [
                'requestId' => $requestId, // REQUIRED by QiCard API
                'amount' => $this->formatAmount($booking->total_price),
                'currency' => $this->currency,
                'merchantOrderId' => $orderId,
                 "finishPaymentUrl"=> $this->returnUrl,
                'description' => $this->generateDescription($booking),
                'returnUrl' => $this->returnUrl, // From config/env
                'notificationUrl' => config('services.qicard.webhook_url'), // Webhook URL for status updates
            ];

            // Create payment record
            $payment = $this->createPaymentRecord($booking, $orderId, $paymentData['amount']);

            // Log the request for debugging
            Log::info('QiCard Payment Request', [
                'url' => $this->apiUrl . 'payment',
                'terminal_id' => $this->terminalId,
                'username' => $this->username,
                'password_set' => !empty($this->password),
                'password_length' => strlen($this->password ?? ''),
                'payload' => $paymentData,
            ]);

            // Make API request to QiCard Iraq
            // Endpoint: POST /payment (singular, not /purchases)
            // Required header: X-Terminal-Id
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Terminal-Id' => $this->terminalId, // Required by QiCard API
                ])
                ->withBasicAuth($this->username, $this->password)
                ->post($this->apiUrl . 'payment', $paymentData);

            if (!$response->successful()) {
                $errorBody = $response->body();
                $statusCode = $response->status();
                
                Log::error('QiCard Payment Failed', [
                    'status' => $statusCode,
                    'body' => $errorBody,
                    'headers' => $response->headers(),
                    'request_url' => $this->apiUrl . 'payment',
                    'terminal_id' => $this->terminalId,
                    'terminal_id_type' => gettype($this->terminalId),
                    'username' => $this->username,
                    'password_set' => !empty($this->password),
                    'payload' => $paymentData,
                ]);
                
                // Provide user-friendly error messages
                if ($statusCode === 502 || $statusCode === 503 || $statusCode === 504) {
                    throw new Exception('QiCard payment gateway is temporarily unavailable. Please try again in a few moments. (Error: ' . $statusCode . ')');
                } elseif ($statusCode === 401 || $statusCode === 403) {
                    // Include more details for debugging
                    $debugInfo = config('app.debug') ? ' | Response: ' . $errorBody : '';
                    throw new Exception('Payment gateway authentication failed. Please contact support. (Error: ' . $statusCode . ')' . $debugInfo);
                } else {
                    throw new Exception('Payment gateway error: ' . $errorBody . ' (Status: ' . $statusCode . ')');
                }
            }

            $responseData = $response->json();

            // QiCard Iraq response structure:
            // {
            //   "requestId": "REQ-1-1765820810",
            //   "paymentId": "4b067aca-e806-4f13-96f9-e47a5ee5c299",
            //   "status": "CREATED",
            //   "amount": 100000,
            //   "currency": "IQD",
            //   "formUrl": "https://uat-sandbox-3ds-api.qi.iq/api/v1/payment/{paymentId}"
            // }
            
            // Update payment with transaction reference
            $payment->update([
                'transaction_ref' => $responseData['paymentId'] ?? null,
                'raw_response' => $responseData,
            ]);

            Log::info('QiCard payment initiated', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'transaction_ref' => $payment->transaction_ref,
                'response' => $responseData,
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'transaction_id' => $responseData['paymentId'] ?? null,
                'payment_url' => $responseData['formUrl'] ?? null, // URL to redirect user for payment
                'order_id' => $orderId,
                'request_id' => $responseData['requestId'] ?? null,
                'status' => $responseData['status'] ?? 'CREATED',
            ];

        } catch (Exception $e) {
            Log::error('QiCard payment initiation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Verify payment status
     *
     * @param string $transactionId (QiCard paymentId)
     * @return array
     * @throws Exception
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            // QiCard Iraq API - GET /payment/{PAYMENT_ID}/status
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Terminal-Id' => $this->terminalId,
                ])
                ->withBasicAuth($this->username, $this->password)
                ->get($this->apiUrl . 'payment/' . $transactionId . '/status');

            if (!$response->successful()) {
                throw new Exception('Payment verification failed: ' . $response->body());
            }

            $responseData = $response->json();

            // Find and update payment record
            $payment = Payment::where('transaction_ref', $transactionId)->first();
            
            if ($payment) {
                // Map QiCard status to our PaymentStatus enum
                $status = $this->mapPaymentStatus($responseData['status'] ?? 'PENDING');
                
                // Update payment record
                $payment->update([
                    'status' => $status,
                    'raw_response' => array_merge($payment->raw_response ?? [], $responseData),
                    'paid_at' => $status === PaymentStatus::COMPLETED ? now() : null,
                ]);

                // Update booking status if payment is completed
                if ($status === PaymentStatus::COMPLETED) {
                    $this->updateBookingAfterPayment($payment->booking);
                    
                    // Dispatch payment completed event
                    event(new PaymentCompleted($payment));
                } elseif ($status === PaymentStatus::FAILED) {
                    // Dispatch payment failed event
                    event(new PaymentFailed($payment, $responseData['failureReason'] ?? null));
                }

                Log::info('Payment verification completed and status updated', [
                    'transaction_id' => $transactionId,
                    'status' => $status->value,
                    'payment_id' => $payment->id,
                ]);
            } else {
                Log::warning('Payment record not found for verification', [
                    'transaction_id' => $transactionId,
                ]);
            }

            return $responseData;

        } catch (Exception $e) {
            Log::error('Payment verification error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle payment callback/webhook
     *
     * @param array $callbackData
     * @return Payment
     * @throws Exception
     */
    public function handleCallback(array $callbackData): Payment
    {
        try {
            Log::info('Processing callback in service', ['callback_data' => $callbackData]);
            
            // QiCard Iraq can send different field names for transaction ID
            // Try multiple possible field names
            $transactionId = $callbackData['orderId'] 
                ?? $callbackData['paymentId'] 
                ?? $callbackData['id'] 
                ?? $callbackData['transaction_id'] 
                ?? $callbackData['transactionId']
                ?? null;
            
            if (!$transactionId) {
                Log::error('Transaction ID not found in callback', ['callback_data' => $callbackData]);
                throw new Exception('Transaction ID not provided in callback. Received fields: ' . implode(', ', array_keys($callbackData)));
            }

            Log::info('Looking for payment with transaction_ref', ['transaction_id' => $transactionId]);

            // Find payment record
            $payment = Payment::where('transaction_ref', $transactionId)->first();
            
            if (!$payment) {
                Log::error('Payment not found for transaction ID', ['transaction_id' => $transactionId]);
                throw new Exception("Payment not found for transaction ID: {$transactionId}");
            }

            Log::info('Payment found', ['payment_id' => $payment->id, 'current_status' => $payment->status->value]);

            // QiCard Iraq can send status in different fields
            $statusString = $callbackData['status'] 
                ?? $callbackData['orderStatus'] 
                ?? $callbackData['paymentStatus']
                ?? $callbackData['state']
                ?? 'unknown';
            
            $status = $this->mapPaymentStatus($statusString);
            
            Log::info('Mapped status', ['raw_status' => $statusString, 'mapped_status' => $status->value]);
            
            $payment->update([
                'status' => $status,
                'raw_response' => array_merge($payment->raw_response ?? [], [
                    'webhook_received_at' => now()->toISOString(),
                    'webhook_data' => $callbackData,
                ]),
                'paid_at' => $status === PaymentStatus::COMPLETED ? now() : null,
            ]);

            Log::info('Payment updated', ['payment_id' => $payment->id, 'new_status' => $status->value]);

            // Update booking status if payment is completed
            if ($status === PaymentStatus::COMPLETED) {
                Log::info('Payment completed, updating booking', ['booking_id' => $payment->booking_id]);
                $this->updateBookingAfterPayment($payment->booking);
                
                // Dispatch payment completed event
                event(new PaymentCompleted($payment));
                
                Log::info('Booking updated successfully', ['booking_id' => $payment->booking_id]);
            } elseif ($status === PaymentStatus::FAILED) {
                Log::info('Payment failed', ['payment_id' => $payment->id]);
                // Dispatch payment failed event
                event(new PaymentFailed($payment, $callbackData['failure_reason'] ?? $callbackData['failureReason'] ?? null));
            }

            return $payment->fresh();

        } catch (Exception $e) {
            Log::error('Payment callback processing failed', [
                'callback_data' => $callbackData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Refund a payment
     *
     * @param Payment $payment
     * @param float|null $amount
     * @param string|null $reason
     * @return array
     * @throws Exception
     */
    public function refundPayment(Payment $payment, ?float $amount = null, ?string $reason = null): array
    {
        try {
            if ($payment->status !== PaymentStatus::COMPLETED) {
                throw new Exception('Only completed payments can be refunded');
            }

            $refundAmount = $amount ?? $payment->amount;

            // QiCard Iraq API - Refund structure
            $refundData = [
                'amount' => $this->formatAmount($refundAmount),
                'reason' => $reason ?? 'Booking cancelled',
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Terminal-Id' => $this->terminalId,
                ])
                ->withBasicAuth($this->username, $this->password)
                ->post($this->apiUrl . 'refunds/' . $payment->transaction_ref, $refundData);

            if (!$response->successful()) {
                throw new Exception('Refund request failed: ' . $response->body());
            }

            $responseData = $response->json();

            // Update payment record
            $payment->update([
                'status' => PaymentStatus::REFUNDED,
                'raw_response' => array_merge($payment->raw_response ?? [], [
                    'refund' => $responseData,
                ]),
            ]);

            // Dispatch payment refunded event
            event(new PaymentRefunded($payment, $refundAmount, $reason));

            Log::info('Payment refunded', [
                'payment_id' => $payment->id,
                'refund_amount' => $refundAmount,
            ]);

            return [
                'success' => true,
                'refund_id' => $responseData['refund_id'] ?? null,
                'message' => 'Payment refunded successfully',
            ];

        } catch (Exception $e) {
            Log::error('Payment refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate unique order ID
     *
     * @param Booking $booking
     * @return string
     */
    protected function generateOrderId(Booking $booking): string
    {
        return 'BK-' . $booking->id . '-' . time();
    }

    /**
     * Generate payment description
     *
     * @param Booking $booking
     * @return string
     */
    protected function generateDescription(Booking $booking): string
    {
        $venueName = $booking->venue->name ?? 'Unknown Venue';
        $date = $booking->booking_date ? $booking->booking_date->format('Y-m-d') : 'N/A';
        
        return "Booking for {$venueName} on {$date}";
    }

    /**
     * Format amount for QiCard API
     * 
     * @param float $amount
     * @return int
     */
    protected function formatAmount(float $amount): int
    {
        // Convert to smallest currency unit (e.g., cents/fils)
        // For IQD, typically no decimal places
        $formattedAmount = (int) round($amount);
        
        // QiCard requires amount > 0
        if ($formattedAmount <= 0) {
            throw new \Exception("Invalid amount: {$amount}. Amount must be greater than 0.");
        }
        
        return $formattedAmount;
    }

    /**
     * Generate signature for API requests
     *
     * @param array $data
     * @return string
     */
    protected function generateSignature(array $data): string
    {
        // Remove signature field if present
        unset($data['signature']);
        
        // Sort array by keys
        ksort($data);
        
        // Create string from data
        $signatureString = implode('|', $data) . '|' . $this->password;
        
        // Generate hash
        return hash('sha256', $signatureString);
    }

    /**
     * Verify callback signature
     *
     * @param array $callbackData
     * @return bool
     */
    protected function verifyCallbackSignature(array $callbackData): bool
    {
        if (!isset($callbackData['signature'])) {
            return false;
        }

        $receivedSignature = $callbackData['signature'];
        $expectedSignature = $this->generateSignature($callbackData);

        return hash_equals($expectedSignature, $receivedSignature);
    }

    /**
     * Map payment gateway status to internal status
     *
     * @param string $gatewayStatus
     * @return PaymentStatus
     */
    protected function mapPaymentStatus(string $gatewayStatus): PaymentStatus
    {
        return match (strtoupper($gatewayStatus)) {
            'SUCCESS', 'COMPLETED', 'APPROVED', 'PAID', 'CONFIRMED' => PaymentStatus::COMPLETED,
            'PENDING', 'PROCESSING', 'INITIATED', 'CREATED', 'AWAITING' => PaymentStatus::PENDING,
            'REFUNDED', 'REVERSED' => PaymentStatus::REFUNDED,
            'FAILED', 'DECLINED', 'REJECTED', 'CANCELLED', 'ERROR' => PaymentStatus::FAILED,
            default => PaymentStatus::FAILED,
        };
    }

    /**
     * Create payment record in database
     *
     * @param Booking $booking
     * @param string $orderId
     * @param int $amount
     * @return Payment
     */
    protected function createPaymentRecord(Booking $booking, string $orderId, int $amount): Payment
    {
        return Payment::create([
            'booking_id' => $booking->id,
            'method' => 'qicard',
            'amount' => $amount,
            'status' => PaymentStatus::PENDING,
            'transaction_ref' => $orderId,
            'raw_response' => [],
        ]);
    }

    /**
     * Update booking after successful payment
     *
     * @param Booking $booking
     * @return void
     */
    protected function updateBookingAfterPayment(Booking $booking): void
    {
        // Update booking status to confirmed if payment is successful
        // Find confirmed status
        $confirmedStatus = \App\Models\Status::where('slug', 'confirmed')->first();
        
        $updateData = [
            'payment_status' => PaymentStatus::COMPLETED->value,
        ];
        
        // Update booking status to confirmed if status exists
        if ($confirmedStatus) {
            $updateData['status_id'] = $confirmedStatus->id;
            $updateData['confirmed_at'] = now();
        }
        
        $booking->update($updateData);

        Log::info('Booking updated after payment', [
            'booking_id' => $booking->id,
            'payment_status' => 'completed',
            'booking_status' => $confirmedStatus ? 'confirmed' : 'unchanged',
        ]);
    }

    /**
     * Get payment status by order ID
     *
     * @param string $orderId
     * @return Payment|null
     */
    public function getPaymentByOrderId(string $orderId): ?Payment
    {
        return Payment::where('transaction_ref', $orderId)->first();
    }
}
