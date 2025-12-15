<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Pending',
                'slug' => 'pending',
                'color' => 'yellow',
                'description' => 'Booking is awaiting confirmation',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Confirmed',
                'slug' => 'confirmed',
                'color' => 'blue',
                'description' => 'Booking has been confirmed',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'In Progress',
                'slug' => 'in-progress',
                'color' => 'purple',
                'description' => 'Booking is currently in progress',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Completed',
                'slug' => 'completed',
                'color' => 'green',
                'description' => 'Booking has been completed successfully',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Cancelled',
                'slug' => 'cancelled',
                'color' => 'red',
                'description' => 'Booking has been cancelled',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'No Show',
                'slug' => 'no-show',
                'color' => 'gray',
                'description' => 'Customer did not show up for the booking',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Refunded',
                'slug' => 'refunded',
                'color' => 'orange',
                'description' => 'Booking has been refunded',
                'is_active' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}
