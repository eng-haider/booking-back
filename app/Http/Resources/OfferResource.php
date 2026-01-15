<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
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
            'venue_id' => $this->venue_id,
            'title' => $this->title,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discount_display' => $this->discount_type === 'percentage' 
                ? $this->discount_value . '%' 
                : $this->discount_value . ' ' . ($this->venue->currency ?? 'USD'),
            'min_booking_hours' => $this->min_booking_hours,
            'max_uses' => $this->max_uses,
            'used_count' => $this->used_count,
            'remaining_uses' => $this->max_uses ? ($this->max_uses - $this->used_count) : null,
            'start_date' => $this->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date?->format('Y-m-d H:i:s'),
            'is_active' => $this->is_active,
            'is_valid' => $this->isValid(),
            'terms_and_conditions' => $this->terms_and_conditions,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'venue' => $this->whenLoaded('venue', function () {
                return [
                    'id' => $this->venue->id,
                    'name' => $this->venue->name,
                    'base_price' => $this->venue->base_price,
                    'currency' => $this->venue->currency,
                ];
            }),
        ];
    }
}
