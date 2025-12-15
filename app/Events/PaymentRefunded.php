<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentRefunded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payment $payment;
    public float $amount;
    public ?string $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(Payment $payment, float $amount, ?string $reason = null)
    {
        $this->payment = $payment;
        $this->amount = $amount;
        $this->reason = $reason;
    }
}
