<?php

namespace Database\Seeders;

use App\Models\VenueType;
use Illuminate\Database\Seeder;

class VenueTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Sports Field', 'slug' => 'sports-field'],
            ['name' => 'Swimming Pool', 'slug' => 'swimming-pool'],
            ['name' => 'Basketball Court', 'slug' => 'basketball-court'],
            ['name' => 'Tennis Court', 'slug' => 'tennis-court'],
            ['name' => 'Football Field', 'slug' => 'football-field'],
            ['name' => 'Event Hall', 'slug' => 'event-hall'],
            ['name' => 'Conference Room', 'slug' => 'conference-room'],
            ['name' => 'Gym', 'slug' => 'gym'],
            ['name' => 'Badminton Court', 'slug' => 'badminton-court'],
            ['name' => 'Volleyball Court', 'slug' => 'volleyball-court'],
        ];

        foreach ($types as $type) {
            VenueType::firstOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
