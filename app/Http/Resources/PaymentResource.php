<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'method' => $this->method,
            'amount' => [
                'value' => $this->amount,
                'formatted' => number_format($this->amount, 0) . ' IQD',
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'transaction_ref' => $this->transaction_ref,
            'paid_at' => $this->paid_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Conditional relationships
            'booking' => $this->whenLoaded('booking', function () {
                return [
                    'id' => $this->booking->id,
                    'reference' => $this->booking->booking_reference,
                    'booking_date' => $this->booking->booking_date?->format('Y-m-d'),
                    'start_time' => $this->booking->start_time,
                    'end_time' => $this->booking->end_time,
                    'total_price' => $this->booking->total_price,
                    'venue' => $this->booking->venue ? [
                        'id' => $this->booking->venue->id,
                        'name' => $this->booking->venue->name,
                    ] : null,
                ];
            }),
        ];
    }
}
