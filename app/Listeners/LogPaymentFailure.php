<?php

namespace App\Listeners;

use App\Events\PaymentFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogPaymentFailure implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentFailed $event): void
    {
        $payment = $event->payment;

        Log::error('Payment failed', [
            'payment_id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'transaction_ref' => $payment->transaction_ref,
            'reason' => $event->reason,
            'amount' => $payment->amount,
        ]);

        // Update booking payment status
        if ($payment->booking) {
            $payment->booking->update([
                'payment_status' => 'failed',
            ]);
        }
    }
}
