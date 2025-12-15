<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payment $payment;
    public ?string $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(Payment $payment, ?string $reason = null)
    {
        $this->payment = $payment;
        $this->reason = $reason;
    }
}
