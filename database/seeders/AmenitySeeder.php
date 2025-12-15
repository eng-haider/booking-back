<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            ['name_en' => 'Parking', 'name_ar' => 'موقف سيارات', 'icon' => 'parking'],
            ['name_en' => 'WiFi', 'name_ar' => 'واي فاي', 'icon' => 'wifi'],
            ['name_en' => 'Changing Rooms', 'name_ar' => 'غرف تبديل الملابس', 'icon' => 'changing-room'],
            ['name_en' => 'Showers', 'name_ar' => 'حمامات', 'icon' => 'shower'],
            ['name_en' => 'Lockers', 'name_ar' => 'خزائن', 'icon' => 'locker'],
            ['name_en' => 'Air Conditioning', 'name_ar' => 'تكييف', 'icon' => 'ac'],
            ['name_en' => 'Lighting', 'name_ar' => 'إضاءة', 'icon' => 'light'],
            ['name_en' => 'Seating Area', 'name_ar' => 'منطقة جلوس', 'icon' => 'seat'],
            ['name_en' => 'Food & Beverage', 'name_ar' => 'مطعم ومشروبات', 'icon' => 'food'],
            ['name_en' => 'First Aid', 'name_ar' => 'إسعافات أولية', 'icon' => 'first-aid'],
            ['name_en' => 'Equipment Rental', 'name_ar' => 'تأجير معدات', 'icon' => 'equipment'],
            ['name_en' => 'Wheelchair Accessible', 'name_ar' => 'مناسب للكراسي المتحركة', 'icon' => 'accessible'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::updateOrCreate(
                ['name_en' => $amenity['name_en']],
                $amenity
            );
        }
    }
}
