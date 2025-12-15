<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sports Fields',
                'slug' => 'sports-fields',
                'description' => 'Football, basketball, volleyball, and other sports fields',
                'icon' => 'sports_soccer',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Swimming Pools',
                'slug' => 'swimming-pools',
                'description' => 'Indoor and outdoor swimming pools',
                'icon' => 'pool',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Event Halls',
                'slug' => 'event-halls',
                'description' => 'Halls for weddings, conferences, and events',
                'icon' => 'event',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Gyms & Fitness',
                'slug' => 'gyms-fitness',
                'description' => 'Gyms and fitness centers',
                'icon' => 'fitness_center',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Tennis Courts',
                'slug' => 'tennis-courts',
                'description' => 'Tennis and padel courts',
                'icon' => 'sports_tennis',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Meeting Rooms',
                'slug' => 'meeting-rooms',
                'description' => 'Professional meeting and conference rooms',
                'icon' => 'meeting_room',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Playgrounds',
                'slug' => 'playgrounds',
                'description' => 'Kids playgrounds and entertainment areas',
                'icon' => 'child_care',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Party Venues',
                'slug' => 'party-venues',
                'description' => 'Birthday parties and celebrations',
                'icon' => 'celebration',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        echo "✓ Categories seeded successfully\n";
    }
}
