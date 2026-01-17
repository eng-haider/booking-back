<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicOfferResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discount_display' => $this->getDiscountDisplay(),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'min_booking_hours' => $this->min_booking_hours,
            'terms_and_conditions' => $this->terms_and_conditions,
            'is_available' => $this->isValid(),
            'expires_in_days' => $this->getExpiresInDays(),
            'badge' => $this->getBadge(),
            'urgency' => $this->getUrgency(),
        ];
    }

    /**
     * Get formatted discount display for UI
     */
    private function getDiscountDisplay(): string
    {
        if ($this->discount_type === 'percentage') {
            return $this->discount_value . '% OFF';
        }
        
        // For fixed amount, get currency from venue if available
        $currency = $this->venue->currency ?? 'USD';
        $symbol = $currency === 'USD' ? '$' : $currency . ' ';
        
        return $symbol . number_format($this->discount_value, 0) . ' OFF';
    }

    /**
     * Get expires in days (negative if expired)
     */
    private function getExpiresInDays(): ?int
    {
        if (!$this->end_date) {
            return null;
        }
        
        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Get offer badge/label for UI
     */
    private function getBadge(): string
    {
        // High percentage discount
        if ($this->discount_type === 'percentage' && $this->discount_value >= 50) {
            return 'HOT DEAL';
        }
        
        // Ending soon
        $daysLeft = $this->getExpiresInDays();
        if ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 3) {
            return 'ENDING SOON';
        }
        
        // Limited availability
        if ($this->max_uses && ($this->max_uses - $this->used_count) <= 5) {
            return 'ALMOST GONE';
        }
        
        return 'SPECIAL OFFER';
    }

    /**
     * Get urgency level for UI styling
     */
    private function getUrgency(): string
    {
        $daysLeft = $this->getExpiresInDays();
        
        // Expiring very soon or almost sold out
        if (($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 2) ||
            ($this->max_uses && ($this->max_uses - $this->used_count) <= 3)) {
            return 'HIGH';
        }
        
        // Expiring soon
        if ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 7) {
            return 'MEDIUM';
        }
        
        return 'NORMAL';
    }
}
