<?php

namespace App\Traits;

use App\Enums\PaymentStatus;
use App\Models\Payment;

trait HasPayment
{
    /**
     * Check if the model has a completed payment
     *
     * @return bool
     */
    public function hasCompletedPayment(): bool
    {
        if (!$this->payment) {
            return false;
        }

        return $this->payment->status === PaymentStatus::COMPLETED;
    }

    /**
     * Check if the model has a pending payment
     *
     * @return bool
     */
    public function hasPendingPayment(): bool
    {
        if (!$this->payment) {
            return false;
        }

        return $this->payment->status === PaymentStatus::PENDING;
    }

    /**
     * Get the payment status label
     *
     * @return string
     */
    public function getPaymentStatusLabel(): string
    {
        if (!$this->payment) {
            return 'No Payment';
        }

        return $this->payment->status->label();
    }

    /**
     * Check if payment can be initiated
     *
     * @return bool
     */
    public function canInitiatePayment(): bool
    {
        // Can initiate if no payment exists or last payment failed
        if (!$this->payment) {
            return true;
        }

        return $this->payment->status === PaymentStatus::FAILED;
    }

    /**
     * Check if payment can be refunded
     *
     * @return bool
     */
    public function canRefundPayment(): bool
    {
        if (!$this->payment) {
            return false;
        }

        return $this->payment->status === PaymentStatus::COMPLETED;
    }

    /**
     * Get payment amount
     *
     * @return float|null
     */
    public function getPaymentAmount(): ?float
    {
        return $this->payment?->amount;
    }

    /**
     * Get payment transaction reference
     *
     * @return string|null
     */
    public function getPaymentTransactionRef(): ?string
    {
        return $this->payment?->transaction_ref;
    }

    /**
     * Check if payment is in a specific status
     *
     * @param PaymentStatus $status
     * @return bool
     */
    public function hasPaymentStatus(PaymentStatus $status): bool
    {
        if (!$this->payment) {
            return false;
        }

        return $this->payment->status === $status;
    }
}
