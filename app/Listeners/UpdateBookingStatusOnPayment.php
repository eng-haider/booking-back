<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Enums\BookingStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateBookingStatusOnPayment implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;
        $booking = $payment->booking;

        if ($booking) {
            // Update booking payment status
            $booking->update([
                'payment_status' => 'completed',
            ]);

            Log::info('Booking payment status updated after payment completion', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(PaymentCompleted $event, \Throwable $exception): void
    {
        Log::error('Failed to update booking status after payment', [
            'payment_id' => $event->payment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
