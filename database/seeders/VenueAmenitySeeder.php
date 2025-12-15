<?php

namespace Database\Seeders;

use App\Models\Venue;
use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VenueAmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all venues
        $venues = Venue::all();
        
        if ($venues->isEmpty()) {
            $this->command->warn('No venues found. Please run VenueSeeder first.');
            return;
        }

        // Create amenities if they don't exist
        $amenitiesData = [
            ['name_en' => 'Swimming Pool', 'name_ar' => 'مسبح', 'icon' => 'pool'],
            ['name_en' => 'Parking', 'name_ar' => 'موقف سيارات', 'icon' => 'local_parking'],
            ['name_en' => 'Free WiFi', 'name_ar' => 'واي فاي مجاني', 'icon' => 'wifi'],
            ['name_en' => 'Restaurant', 'name_ar' => 'مطعم', 'icon' => 'restaurant'],
            ['name_en' => 'Spa', 'name_ar' => 'سبا', 'icon' => 'spa'],
            ['name_en' => 'Gym', 'name_ar' => 'صالة رياضية', 'icon' => 'fitness_center'],
            ['name_en' => 'Family Rooms', 'name_ar' => 'غرف عائلية', 'icon' => 'family_restroom'],
            ['name_en' => 'Sauna', 'name_ar' => 'ساونا', 'icon' => 'hot_tub'],
            ['name_en' => 'Jacuzzi', 'name_ar' => 'جاكوزي', 'icon' => 'bathtub'],
            ['name_en' => 'Garden', 'name_ar' => 'حديقة', 'icon' => 'park'],
            ['name_en' => 'Kids Playground', 'name_ar' => 'ملاعب أطفال', 'icon' => 'child_care'],
            ['name_en' => 'BBQ Grill', 'name_ar' => 'شواية', 'icon' => 'outdoor_grill'],
            ['name_en' => 'Air Conditioning', 'name_ar' => 'تكييف', 'icon' => 'ac_unit'],
            ['name_en' => 'Television', 'name_ar' => 'تلفزيون', 'icon' => 'tv'],
            ['name_en' => 'Room Service', 'name_ar' => 'خدمة الغرف', 'icon' => 'room_service'],
            ['name_en' => 'Security', 'name_ar' => 'أمن وحراسة', 'icon' => 'security'],
            ['name_en' => 'Football Field', 'name_ar' => 'ملعب كرة قدم', 'icon' => 'sports_soccer'],
            ['name_en' => 'Basketball Court', 'name_ar' => 'ملعب كرة سلة', 'icon' => 'sports_basketball'],
            ['name_en' => 'Horse Riding', 'name_ar' => 'ركوب الخيل', 'icon' => 'sports'],
            ['name_en' => 'Boats', 'name_ar' => 'قوارب', 'icon' => 'sailing'],
        ];

        // Clear existing amenities
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('amenity_venue')->truncate();
        Amenity::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($amenitiesData as $amenityData) {
            Amenity::create($amenityData);
        }

        // Assign amenities to venues using pivot table
        foreach ($venues as $venue) {
            // Get random amenities (between 5 and 12 amenities per venue)
            $randomAmenities = Amenity::inRandomOrder()
                ->limit(rand(5, 12))
                ->pluck('id')
                ->toArray();
            
            // Attach amenities to venue using the pivot table
            foreach ($randomAmenities as $amenityId) {
                DB::table('amenity_venue')->insert([
                    'amenity_id' => $amenityId,
                    'venue_id' => $venue->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $totalAmenities = Amenity::count();
        $this->command->info("تم إنشاء {$totalAmenities} من المرافق وربطها بالمنتجعات!");
    }
}
