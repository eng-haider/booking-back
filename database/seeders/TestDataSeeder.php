<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Provider;
use App\Models\Customer;
use App\Models\Venue;
use App\Models\VenueType;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Country;
use App\Models\Category;
use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Get the provider user
            $providerUser = User::where('phone', '+9649876543210')->first();
            
            if (!$providerUser) {
                echo "❌ Provider user not found. Please run TestUserSeeder first.\n";
                return;
            }

            $provider = $providerUser->provider;
            
            if (!$provider) {
                echo "❌ Provider profile not found.\n";
                return;
            }

            echo "✓ Found provider: {$provider->name}\n";

            // Get reference data
            $country = Country::where('code', 'IQ')->first();
            $categories = Category::all();
            $statuses = Status::all();
            
            // Create venue types if they don't exist
            $venueTypes = $this->createVenueTypes();
            
            // Get test customers
            $customers = $this->getOrCreateCustomers();
            
            // Create venues for the provider
            $venues = $this->createVenues($provider, $country, $categories, $venueTypes);
            
            // Create bookings for the venues
            $bookings = $this->createBookings($venues, $customers, $statuses, $providerUser);
            
            // Create reviews for completed bookings
            $this->createReviews($bookings, $venues, $customers, $providerUser);
            
            echo "\n";
            echo "========================================\n";
            echo "Test Data Created Successfully!\n";
            echo "========================================\n";
            echo "Provider: {$provider->name}\n";
            echo "Venues: " . count($venues) . "\n";
            echo "Bookings: " . count($bookings) . "\n";
            echo "========================================\n";
        });
    }

    private function createVenueTypes(): array
    {
        $types = [
            ['name' => 'Football Field', 'slug' => 'football-field'],
            ['name' => 'Basketball Court', 'slug' => 'basketball-court'],
            ['name' => 'Tennis Court', 'slug' => 'tennis-court'],
            ['name' => 'Swimming Pool', 'slug' => 'swimming-pool'],
            ['name' => 'Gym', 'slug' => 'gym'],
            ['name' => 'Conference Hall', 'slug' => 'conference-hall'],
        ];

        $venueTypes = [];
        foreach ($types as $type) {
            $venueTypes[] = VenueType::firstOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }

        echo "✓ Created " . count($venueTypes) . " venue types\n";
        return $venueTypes;
    }

    private function getOrCreateCustomers(): array
    {
        $customers = [];
        
        // Get existing test customer
        $testCustomer = Customer::where('phone', '+9645555555555')->first();
        if ($testCustomer) {
            $customers[] = $testCustomer;
        }

        // Create additional test customers
        for ($i = 1; $i <= 5; $i++) {
            $customers[] = Customer::firstOrCreate(
                ['phone' => '+96450000000' . $i],
                [
                    'full_name' => "Customer Test {$i}",
                    'email' => "customer{$i}@test.com",
                    'phone' => '+96450000000' . $i,
                    'is_active' => true,
                ]
            );
        }

        echo "✓ Created/found " . count($customers) . " test customers\n";
        return $customers;
    }

    private function createVenues($provider, $country, $categories, $venueTypes): array
    {
        $venues = [];

        $venueData = [
            [
                'name' => 'Elite Sports Complex',
                'description' => 'Premium sports facility with modern amenities and professional-grade equipment',
                'venue_type' => 'football-field',
                'category' => 'Sports Fields',
                'base_price' => 50000,
                'city' => 'Baghdad',
                'address' => 'Karrada District, Near Zawra Park',
            ],
            [
                'name' => 'Champions Basketball Arena',
                'description' => 'Indoor basketball court with air conditioning and professional lighting',
                'venue_type' => 'basketball-court',
                'category' => 'Sports Fields',
                'base_price' => 40000,
                'city' => 'Baghdad',
                'address' => 'Mansour District, Main Street',
            ],
            [
                'name' => 'Aqua Paradise Pool',
                'description' => 'Olympic-size swimming pool with heated water and changing facilities',
                'venue_type' => 'swimming-pool',
                'category' => 'Swimming Pools',
                'base_price' => 35000,
                'city' => 'Baghdad',
                'address' => 'Jadiriya, Near University',
            ],
            [
                'name' => 'Tennis Pro Center',
                'description' => 'Professional tennis courts with night lighting and seating area',
                'venue_type' => 'tennis-court',
                'category' => 'Tennis Courts',
                'base_price' => 30000,
                'city' => 'Baghdad',
                'address' => 'Green Zone Area',
            ],
            [
                'name' => 'PowerFit Gym',
                'description' => 'Fully equipped gym with modern machines and free weights',
                'venue_type' => 'gym',
                'category' => 'Gyms & Fitness',
                'base_price' => 25000,
                'city' => 'Baghdad',
                'address' => 'Karada, Commercial Center',
            ],
            [
                'name' => 'Grand Conference Center',
                'description' => 'Spacious conference hall with AV equipment and catering services',
                'venue_type' => 'conference-hall',
                'category' => 'Meeting Rooms',
                'base_price' => 100000,
                'city' => 'Baghdad',
                'address' => 'Downtown, Business District',
            ],
        ];

        foreach ($venueData as $data) {
            $venueType = collect($venueTypes)->firstWhere('slug', $data['venue_type']);
            $category = $categories->firstWhere('name', $data['category']);

            $venue = Venue::firstOrCreate(
                [
                    'provider_id' => $provider->id,
                    'name' => $data['name'],
                ],
                [
                    'owner_id' => $provider->user_id,
                    'venue_type_id' => $venueType->id,
                    'category_id' => $category->id,
                    'description' => $data['description'],
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'country_id' => $country->id,
                    'base_price' => $data['base_price'],
                    'currency' => 'IQD',
                    'status' => 'active',
                    'buffer_minutes' => 15,
                    'timezone' => 'Asia/Baghdad',
                ]
            );

            $venues[] = $venue;
        }

        echo "✓ Created " . count($venues) . " venues\n";
        return $venues;
    }

    private function createBookings($venues, $customers, $statuses, $providerUser): array
    {
        $bookings = [];
        $statusPending = $statuses->firstWhere('name', 'Pending');
        $statusConfirmed = $statuses->firstWhere('name', 'Confirmed');
        $statusCompleted = $statuses->firstWhere('name', 'Completed');
        $statusCancelled = $statuses->firstWhere('name', 'Cancelled');

        // Create various bookings with different statuses
        $bookingData = [
            // Completed bookings (can be reviewed)
            ['days_ago' => 10, 'status' => $statusCompleted, 'hour' => 9, 'duration' => 120],
            ['days_ago' => 8, 'status' => $statusCompleted, 'hour' => 14, 'duration' => 120],
            ['days_ago' => 5, 'status' => $statusCompleted, 'hour' => 10, 'duration' => 120],
            ['days_ago' => 3, 'status' => $statusCompleted, 'hour' => 16, 'duration' => 120],
            
            // Confirmed bookings (upcoming)
            ['days_ago' => -2, 'status' => $statusConfirmed, 'hour' => 9, 'duration' => 120],
            ['days_ago' => -3, 'status' => $statusConfirmed, 'hour' => 15, 'duration' => 120],
            ['days_ago' => -5, 'status' => $statusConfirmed, 'hour' => 10, 'duration' => 120],
            
            // Pending bookings
            ['days_ago' => -1, 'status' => $statusPending, 'hour' => 8, 'duration' => 120],
            ['days_ago' => -4, 'status' => $statusPending, 'hour' => 13, 'duration' => 120],
            
            // Cancelled booking
            ['days_ago' => 2, 'status' => $statusCancelled, 'hour' => 11, 'duration' => 120],
        ];

        foreach ($bookingData as $index => $data) {
            $venue = $venues[$index % count($venues)];
            $customer = $customers[$index % count($customers)];
            
            $startDatetime = now()->addDays($data['days_ago'])->setHour($data['hour'])->setMinute(0)->setSecond(0);
            $endDatetime = $startDatetime->copy()->addMinutes($data['duration']);
            
            $booking = Booking::create([
                'user_id' => $providerUser->id, // Use provider user as the booking user
                'customer_id' => $customer->id,
                'venue_id' => $venue->id,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'duration_minutes' => $data['duration'],
                'total_amount' => $venue->base_price,
                'currency' => 'IQD',
                'status_id' => $data['status']->id,
            ]);

            $bookings[] = $booking;
        }

        echo "✓ Created " . count($bookings) . " bookings\n";
        return $bookings;
    }

    private function createReviews($bookings, $venues, $customers, $providerUser): void
    {
        $reviewCount = 0;
        $completedStatus = Status::where('name', 'Completed')->first();

        $reviewTexts = [
            [5, 'Excellent facility! Everything was perfect. Highly recommend!'],
            [5, 'Amazing experience! The staff was very helpful and the venue was clean.'],
            [4, 'Great venue, well maintained. Had a wonderful time.'],
            [4, 'Very good facilities. Would definitely book again.'],
            [5, 'Outstanding! Exceeded all expectations. Worth every penny.'],
            [3, 'Good venue but could use some improvements in facilities.'],
            [4, 'Nice place, good service. A bit pricey but worth it.'],
            [5, 'Perfect for our event! Everything went smoothly.'],
        ];

        foreach ($bookings as $index => $booking) {
            // Only create reviews for completed bookings
            if ($booking->status_id === $completedStatus->id && $index < count($reviewTexts)) {
                $reviewData = $reviewTexts[$index];
                
                Review::firstOrCreate(
                    [
                        'customer_id' => $booking->customer_id,
                        'venue_id' => $booking->venue_id,
                    ],
                    [
                        'user_id' => $providerUser->id, // Use provider user for reviews
                        'rating' => $reviewData[0],
                        'comment' => $reviewData[1],
                    ]
                );
                
                $reviewCount++;
            }
        }

        echo "✓ Created {$reviewCount} reviews\n";
    }
}
