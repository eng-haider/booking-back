<?php

/**
 * Example: How to integrate offers with booking system
 * 
 * This is a reference implementation showing how to apply offers
 * to bookings when you're ready to integrate.
 */

namespace App\Examples;

use App\Models\Offer;
use App\Models\Booking;
use App\Models\Venue;
use Carbon\Carbon;

class OfferIntegrationExample
{
    /**
     * Example 1: Apply offer to booking
     */
    public function applyOfferToBooking(int $offerId, array $bookingData)
    {
        // Get the offer
        $offer = Offer::with('venue')->find($offerId);
        
        if (!$offer) {
            return [
                'success' => false,
                'message' => 'Offer not found'
            ];
        }

        // Validate offer
        if (!$offer->isValid()) {
            return [
                'success' => false,
                'message' => 'Offer is not valid (expired, inactive, or maxed out)'
            ];
        }

        // Check minimum booking hours
        if ($offer->min_booking_hours) {
            $bookingHours = $this->calculateBookingHours(
                $bookingData['start_time'], 
                $bookingData['end_time']
            );
            
            if ($bookingHours < $offer->min_booking_hours) {
                return [
                    'success' => false,
                    'message' => "Minimum {$offer->min_booking_hours} hours required for this offer"
                ];
            }
        }

        // Calculate prices
        $originalPrice = $bookingData['total_price'];
        $discountAmount = $offer->calculateDiscount($originalPrice);
        $finalPrice = $originalPrice - $discountAmount;

        // Increment offer usage
        $offer->incrementUsedCount();

        return [
            'success' => true,
            'original_price' => $originalPrice,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'offer_title' => $offer->title,
            'savings' => $discountAmount
        ];
    }

    /**
     * Example 2: Get applicable offers for a venue
     */
    public function getApplicableOffers(int $venueId, int $bookingHours = null)
    {
        $query = Offer::where('venue_id', $venueId)
            ->available(); // active and not maxed out

        // Filter by minimum booking hours if provided
        if ($bookingHours) {
            $query->where(function($q) use ($bookingHours) {
                $q->whereNull('min_booking_hours')
                  ->orWhere('min_booking_hours', '<=', $bookingHours);
            });
        }

        return $query->get();
    }

    /**
     * Example 3: Show offer details to customer
     */
    public function displayOfferForCustomer(Offer $offer)
    {
        $display = [
            'title' => $offer->title,
            'description' => $offer->description,
            'discount' => $offer->discount_type === 'percentage' 
                ? "{$offer->discount_value}% OFF"
                : "\${$offer->discount_value} OFF",
            'valid_until' => $offer->end_date->format('M d, Y'),
            'terms' => $offer->terms_and_conditions,
        ];

        // Add usage info if limited
        if ($offer->max_uses) {
            $remaining = $offer->max_uses - $offer->used_count;
            $display['limited'] = true;
            $display['remaining_uses'] = $remaining;
            $display['urgency'] = $remaining < 10 ? 'HIGH' : 'NORMAL';
        }

        // Add minimum hours requirement
        if ($offer->min_booking_hours) {
            $display['minimum_hours'] = $offer->min_booking_hours;
        }

        return $display;
    }

    /**
     * Example 4: Preview discount before booking
     */
    public function previewDiscount(int $offerId, float $estimatedPrice)
    {
        $offer = Offer::find($offerId);
        
        if (!$offer || !$offer->isValid()) {
            return null;
        }

        $discountAmount = $offer->calculateDiscount($estimatedPrice);
        $finalPrice = $estimatedPrice - $discountAmount;
        
        $savingsPercentage = ($discountAmount / $estimatedPrice) * 100;

        return [
            'original_price' => number_format($estimatedPrice, 2),
            'discount_amount' => number_format($discountAmount, 2),
            'final_price' => number_format($finalPrice, 2),
            'you_save' => number_format($discountAmount, 2),
            'savings_percentage' => round($savingsPercentage, 1) . '%',
            'offer_name' => $offer->title
        ];
    }

    /**
     * Example 5: Validate offer code (if you add promo codes later)
     */
    public function validatePromoCode(string $code, int $venueId)
    {
        // Future enhancement: Add promo_code field to offers table
        // Then you can do:
        // $offer = Offer::where('promo_code', $code)
        //     ->where('venue_id', $venueId)
        //     ->available()
        //     ->first();

        return [
            'valid' => false,
            'message' => 'Promo code functionality coming soon!'
        ];
    }

    /**
     * Example 6: Get best offer for a booking
     */
    public function getBestOffer(int $venueId, float $bookingPrice, int $bookingHours)
    {
        $offers = $this->getApplicableOffers($venueId, $bookingHours);
        
        if ($offers->isEmpty()) {
            return null;
        }

        $bestOffer = null;
        $maxDiscount = 0;

        foreach ($offers as $offer) {
            $discount = $offer->calculateDiscount($bookingPrice);
            if ($discount > $maxDiscount) {
                $maxDiscount = $discount;
                $bestOffer = $offer;
            }
        }

        return [
            'offer' => $bestOffer,
            'discount_amount' => $maxDiscount,
            'final_price' => $bookingPrice - $maxDiscount
        ];
    }

    /**
     * Helper: Calculate booking hours
     */
    private function calculateBookingHours(string $startTime, string $endTime): float
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        return $start->diffInHours($end);
    }

    /**
     * Example 7: Handle offer expiration notification
     */
    public function checkExpiringOffers(int $providerId, int $daysThreshold = 7)
    {
        $expiringOffers = Offer::whereHas('venue', function($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            })
            ->where('is_active', true)
            ->where('end_date', '>', Carbon::now())
            ->where('end_date', '<=', Carbon::now()->addDays($daysThreshold))
            ->get();

        return $expiringOffers->map(function($offer) {
            return [
                'offer_id' => $offer->id,
                'title' => $offer->title,
                'venue_name' => $offer->venue->name,
                'expires_in_days' => Carbon::now()->diffInDays($offer->end_date),
                'used_count' => $offer->used_count,
                'max_uses' => $offer->max_uses,
                'action_needed' => 'Consider extending or creating a new offer'
            ];
        });
    }

    /**
     * Example 8: Offer performance metrics
     */
    public function getOfferPerformance(Offer $offer)
    {
        // This assumes you've added offer_id to bookings table
        // For now, this is a template for future implementation
        
        return [
            'offer_id' => $offer->id,
            'title' => $offer->title,
            'usage_rate' => $offer->max_uses 
                ? round(($offer->used_count / $offer->max_uses) * 100, 2) . '%'
                : 'Unlimited',
            'days_active' => Carbon::now()->diffInDays($offer->start_date),
            'days_remaining' => $offer->end_date->diffInDays(Carbon::now()),
            'is_popular' => $offer->used_count > 50,
            'status' => $this->getOfferStatus($offer)
        ];
    }

    /**
     * Helper: Get offer status
     */
    private function getOfferStatus(Offer $offer): string
    {
        if (!$offer->is_active) return 'Inactive';
        if ($offer->end_date < Carbon::now()) return 'Expired';
        if ($offer->start_date > Carbon::now()) return 'Upcoming';
        if ($offer->max_uses && $offer->used_count >= $offer->max_uses) return 'Sold Out';
        return 'Active';
    }
}

/*
 * USAGE EXAMPLES:
 * 
 * 1. In BookingController:
 * 
 *   $example = new OfferIntegrationExample();
 *   $result = $example->applyOfferToBooking($offerId, $bookingData);
 * 
 * 2. In VenueController (show available offers):
 * 
 *   $example = new OfferIntegrationExample();
 *   $offers = $example->getApplicableOffers($venueId, 4);
 * 
 * 3. Preview discount before booking:
 * 
 *   $preview = $example->previewDiscount($offerId, $estimatedPrice);
 * 
 * 4. Get best offer automatically:
 * 
 *   $best = $example->getBestOffer($venueId, $price, $hours);
 * 
 */
