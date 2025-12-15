<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    /**
     * Create a new payment exception for failed initiation
     *
     * @param string $message
     * @param int $code
     * @return static
     */
    public static function initiationFailed(string $message = 'Payment initiation failed', int $code = 500): static
    {
        return new static($message, $code);
    }

    /**
     * Create a new payment exception for verification failure
     *
     * @param string $transactionId
     * @return static
     */
    public static function verificationFailed(string $transactionId): static
    {
        return new static("Payment verification failed for transaction: {$transactionId}", 422);
    }

    /**
     * Create a new payment exception for invalid signature
     *
     * @return static
     */
    public static function invalidSignature(): static
    {
        return new static('Invalid payment signature', 401);
    }

    /**
     * Create a new payment exception for already paid booking
     *
     * @return static
     */
    public static function alreadyPaid(): static
    {
        return new static('This booking has already been paid', 400);
    }

    /**
     * Create a new payment exception for refund failure
     *
     * @param string $reason
     * @return static
     */
    public static function refundFailed(string $reason): static
    {
        return new static("Refund failed: {$reason}", 500);
    }

    /**
     * Create a new payment exception for invalid payment status
     *
     * @param string $currentStatus
     * @param string $requiredStatus
     * @return static
     */
    public static function invalidStatus(string $currentStatus, string $requiredStatus): static
    {
        return new static("Payment status must be '{$requiredStatus}' but is currently '{$currentStatus}'", 400);
    }

    /**
     * Create a new payment exception for gateway timeout
     *
     * @return static
     */
    public static function gatewayTimeout(): static
    {
        return new static('Payment gateway timeout. Please try again.', 504);
    }

    /**
     * Create a new payment exception for missing configuration
     *
     * @param string $configKey
     * @return static
     */
    public static function missingConfiguration(string $configKey): static
    {
        return new static("Missing payment configuration: {$configKey}", 500);
    }
}
