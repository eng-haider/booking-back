<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing offers
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Offer::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $venues = Venue::limit(5)->get();

        if ($venues->isEmpty()) {
            $this->command->warn('No venues found. Please seed venues first.');
            return;
        }

        $offers = [
            [
                'venue_id' => $venues[0]->id,
                'title' => 'Early Bird Special - 20% Off',
                'description' => 'Book early and save 20% on your venue booking',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'min_booking_hours' => 2,
                'max_uses' => 100,
                'used_count' => 15,
                'start_date' => Carbon::now()->subDays(7),
                'end_date' => Carbon::now()->addMonths(2),
                'is_active' => true,
                'terms_and_conditions' => 'Valid for bookings made at least 7 days in advance',
            ],
            [
                'venue_id' => $venues[0]->id,
                'title' => 'Weekend Special',
                'description' => 'Get $100 off on weekend bookings',
                'discount_type' => 'fixed',
                'discount_value' => 100.00,
                'min_booking_hours' => 4,
                'max_uses' => 50,
                'used_count' => 8,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonth(),
                'is_active' => true,
                'terms_and_conditions' => 'Valid only for Saturday and Sunday bookings of 4 hours or more',
            ],
            [
                'venue_id' => $venues[1]->id ?? $venues[0]->id,
                'title' => 'Summer Promotion',
                'description' => 'Huge summer discount - 30% off all bookings',
                'discount_type' => 'percentage',
                'discount_value' => 30.00,
                'min_booking_hours' => null,
                'max_uses' => 200,
                'used_count' => 45,
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'terms_and_conditions' => 'Valid for all summer bookings',
            ],
            [
                'venue_id' => $venues[1]->id ?? $venues[0]->id,
                'title' => 'Limited Time Offer',
                'description' => 'First 20 customers get 50% off',
                'discount_type' => 'percentage',
                'discount_value' => 50.00,
                'min_booking_hours' => 3,
                'max_uses' => 20,
                'used_count' => 19,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(2),
                'is_active' => true,
                'terms_and_conditions' => 'Limited to first 20 customers only',
            ],
            [
                'venue_id' => $venues[2]->id ?? $venues[0]->id,
                'title' => 'New Customer Special',
                'description' => 'First booking? Get $75 off!',
                'discount_type' => 'fixed',
                'discount_value' => 75.00,
                'min_booking_hours' => 2,
                'max_uses' => null,
                'used_count' => 0,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(6),
                'is_active' => true,
                'terms_and_conditions' => 'Valid for new customers only',
            ],
            [
                'venue_id' => $venues[2]->id ?? $venues[0]->id,
                'title' => 'Extended Stay Discount',
                'description' => '15% off for bookings of 8 hours or more',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'min_booking_hours' => 8,
                'max_uses' => 75,
                'used_count' => 12,
                'start_date' => Carbon::now()->subDays(14),
                'end_date' => Carbon::now()->addMonths(4),
                'is_active' => true,
                'terms_and_conditions' => 'Minimum 8 hours booking required',
            ],
            [
                'venue_id' => $venues[3]->id ?? $venues[0]->id,
                'title' => 'Expired Offer',
                'description' => 'This offer has expired',
                'discount_type' => 'percentage',
                'discount_value' => 25.00,
                'min_booking_hours' => null,
                'max_uses' => 100,
                'used_count' => 87,
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => Carbon::now()->subDays(1),
                'is_active' => false,
                'terms_and_conditions' => 'Expired offer for testing',
            ],
            [
                'venue_id' => $venues[4]->id ?? $venues[0]->id,
                'title' => 'Upcoming Holiday Special',
                'description' => 'Holiday season discount coming soon',
                'discount_type' => 'percentage',
                'discount_value' => 35.00,
                'min_booking_hours' => 3,
                'max_uses' => 150,
                'used_count' => 0,
                'start_date' => Carbon::now()->addMonth(),
                'end_date' => Carbon::now()->addMonths(4),
                'is_active' => true,
                'terms_and_conditions' => 'Valid during holiday season',
            ],
        ];

        foreach ($offers as $offer) {
            Offer::create($offer);
        }

        $this->command->info('Offers seeded successfully!');
    }
}
