<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Venue;

class OfferService
{
    /**
     * Apply offer to a price
     */
    public function applyOffer(Offer $offer, float $basePrice, ?int $bookingHours = null): array
    {
        // Check if offer is valid
        if (!$offer->isValid()) {
            return [
                'applied' => false,
                'base_price' => $basePrice,
                'discount' => 0,
                'final_price' => $basePrice,
                'message' => 'Offer is not valid or has expired',
            ];
        }

        // Check minimum booking hours requirement
        if ($offer->min_booking_hours && $bookingHours) {
            if ($bookingHours < $offer->min_booking_hours) {
                return [
                    'applied' => false,
                    'base_price' => $basePrice,
                    'discount' => 0,
                    'final_price' => $basePrice,
                    'message' => "Minimum {$offer->min_booking_hours} hours booking required for this offer",
                ];
            }
        }

        // Calculate discount
        $discount = $offer->calculateDiscount($basePrice);
        $finalPrice = max(0, $basePrice - $discount);

        return [
            'applied' => true,
            'base_price' => $basePrice,
            'discount' => $discount,
            'final_price' => $finalPrice,
            'savings_percentage' => $basePrice > 0 ? round(($discount / $basePrice) * 100, 2) : 0,
            'offer' => [
                'id' => $offer->id,
                'title' => $offer->title,
                'discount_type' => $offer->discount_type,
                'discount_value' => $offer->discount_value,
            ],
            'message' => 'Offer applied successfully',
        ];
    }

    /**
     * Validate offer for booking
     */
    public function validateOfferForBooking(
        Offer $offer,
        Venue $venue,
        int $bookingHours
    ): array {
        $errors = [];

        // Check if offer is valid (active, within date range, not maxed out)
        if (!$offer->isValid()) {
            $errors[] = 'Offer is not valid or has expired';
        }

        // Check if offer belongs to the venue
        if ($offer->venue_id !== $venue->id) {
            $errors[] = 'Offer is not valid for this venue';
        }

        // Check minimum booking hours
        if ($offer->min_booking_hours && $bookingHours < $offer->min_booking_hours) {
            $errors[] = "Minimum booking of {$offer->min_booking_hours} hours required for this offer";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Calculate discount preview (without applying)
     */
    public function previewDiscount(Offer $offer, float $estimatedPrice): ?array
    {
        if (!$offer->isValid()) {
            return null;
        }

        $discountAmount = $offer->calculateDiscount($estimatedPrice);
        $finalPrice = $estimatedPrice - $discountAmount;
        $savingsPercentage = $estimatedPrice > 0 ? round(($discountAmount / $estimatedPrice) * 100, 2) : 0;

        return [
            'original_price' => number_format($estimatedPrice, 2),
            'discount_amount' => number_format($discountAmount, 2),
            'final_price' => number_format($finalPrice, 2),
            'you_save' => number_format($discountAmount, 2),
            'savings_percentage' => $savingsPercentage . '%',
            'offer_name' => $offer->title,
        ];
    }

    /**
     * Increment offer usage count (call this after booking is confirmed)
     */
    public function incrementOfferUsage(Offer $offer): void
    {
        $offer->incrementUsedCount();
    }

    /**
     * Get all applicable offers for a booking
     */
    public function getApplicableOffers(Venue $venue, int $bookingHours): array
    {
        $offers = $venue->activeOffers()
            ->where(function($q) use ($bookingHours) {
                $q->whereNull('min_booking_hours')
                  ->orWhere('min_booking_hours', '<=', $bookingHours);
            })
            ->get();

        return $offers->map(function($offer) {
            return [
                'id' => $offer->id,
                'title' => $offer->title,
                'discount_type' => $offer->discount_type,
                'discount_value' => $offer->discount_value,
                'discount_display' => $offer->discount_type === 'percentage' 
                    ? $offer->discount_value . '% OFF'
                    : '$' . $offer->discount_value . ' OFF',
            ];
        })->toArray();
    }
}
