<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolesAndPermissionsSeeder::class,
            GovernorateSeeder::class,
        ]);

        // Seed basic data
        $this->call([
            CountrySeeder::class,
            StatusSeeder::class,
            CategorySeeder::class,
            AmenitySeeder::class,
        ]);

        // Create test users
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@booking.app',
            'phone' => '+1234567890',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Venue Owner',
            'email' => 'owner@booking.app',
            'phone' => '+1234567891',
            'role' => 'provider',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@booking.app',
            'phone' => '+1234567892',
            'role' => 'user',
        ]);

        // Seed Arabic data
        $this->call([
            TestUserSeeder::class,
            CustomerSeeder::class,
            VenueSeeder::class,
            BookingSeeder::class,
            VenueAmenitySeeder::class,
            VenueReviewSeeder::class,
        ]);
    }
}

