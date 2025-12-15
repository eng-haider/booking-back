<?php

namespace Database\Seeders;

use App\Models\Governorate;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governorates = [
            ['name_ar' => 'بغداد', 'name_en' => 'Baghdad'],
            ['name_ar' => 'البصرة', 'name_en' => 'Basra'],
            ['name_ar' => 'نينوى', 'name_en' => 'Nineveh'],
            ['name_ar' => 'أربيل', 'name_en' => 'Erbil'],
            ['name_ar' => 'النجف', 'name_en' => 'Najaf'],
            ['name_ar' => 'كربلاء', 'name_en' => 'Karbala'],
            ['name_ar' => 'السليمانية', 'name_en' => 'Sulaymaniyah'],
            ['name_ar' => 'الأنبار', 'name_en' => 'Anbar'],
            ['name_ar' => 'دهوك', 'name_en' => 'Dohuk'],
            ['name_ar' => 'ذي قار', 'name_en' => 'Dhi Qar'],
            ['name_ar' => 'بابل', 'name_en' => 'Babylon'],
            ['name_ar' => 'كركوك', 'name_en' => 'Kirkuk'],
            ['name_ar' => 'ديالى', 'name_en' => 'Diyala'],
            ['name_ar' => 'واسط', 'name_en' => 'Wasit'],
            ['name_ar' => 'صلاح الدين', 'name_en' => 'Saladin'],
            ['name_ar' => 'ميسان', 'name_en' => 'Maysan'],
            ['name_ar' => 'المثنى', 'name_en' => 'Al-Muthanna'],
            ['name_ar' => 'القادسية', 'name_en' => 'Al-Qadisiyyah'],
        ];

        foreach ($governorates as $governorate) {
            Governorate::firstOrCreate(
                ['name_en' => $governorate['name_en']],
                [
                    'name_ar' => $governorate['name_ar'],
                    'is_active' => true,
                ]
            );
        }

        echo "✓ Iraq governorates created successfully\n";
    }
}
