<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;
        $booking = $payment->booking;
        $customer = $booking?->customer;

        if (!$customer) {
            Log::warning('Cannot send payment confirmation: customer not found', [
                'payment_id' => $payment->id,
            ]);
            return;
        }

        // TODO: Implement email sending logic
        // Example:
        // Mail::to($customer->email)
        //     ->send(new PaymentConfirmationMail($payment));

        Log::info('Payment confirmation email queued', [
            'payment_id' => $payment->id,
            'customer_email' => $customer->email,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(PaymentCompleted $event, \Throwable $exception): void
    {
        Log::error('Failed to send payment confirmation email', [
            'payment_id' => $event->payment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
