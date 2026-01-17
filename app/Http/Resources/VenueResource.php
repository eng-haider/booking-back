<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'price_per_hour' => $this->price_per_hour,
            'base_price' => $this->base_price,
            'currency' => $this->currency,
            'capacity' => $this->capacity,
            'buffer_minutes' => $this->buffer_minutes,
            'booking_duration_hours' => $this->booking_duration_hours,
            'status' => $this->status,
            'timezone' => $this->timezone,
            
            // Offers information
            'has_offers' => $this->hasActiveOffers(),
            'best_offer' => $this->when(
                $this->relationLoaded('activeOffers') || method_exists($this, 'bestOffer'),
                function() {
                    $bestOffer = $this->bestOffer();
                    return $bestOffer ? new PublicOfferResource($bestOffer) : null;
                }
            ),
            'active_offers' => PublicOfferResource::collection(
                $this->whenLoaded('activeOffers')
            ),
            'offers_count' => $this->when(
                $this->relationLoaded('activeOffers'),
                function() {
                    return $this->activeOffers->count();
                }
            ),
            
            // Relationships
            'provider' => $this->whenLoaded('provider', function() {
                return [
                    'id' => $this->provider->id,
                    'name' => $this->provider->name,
                    'phone' => $this->provider->phone,
                    'email' => $this->provider->email,
                ];
            }),
            'category' => $this->whenLoaded('category', function() {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'amenities' => $this->whenLoaded('amenities'),
            'photos' => $this->whenLoaded('photos', function() {
                return $this->photos->map(function($photo) {
                    return [
                        'id' => $photo->id,
                        'url' => $photo->url,
                        'is_primary' => $photo->is_primary,
                    ];
                });
            }),
            'schedules' => $this->whenLoaded('schedules'),
            'reviews' => $this->whenLoaded('reviews'),
            
            // Metadata
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
