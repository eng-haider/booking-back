<?php

/**
 * Example usage of the Venue Scheduling Feature
 * 
 * This file demonstrates how to use the new scheduling functionality
 */

// Example 1: Create a venue with default schedule (9 AM - 9 PM, all days)
$venueDataDefault = [
    'category_id' => 1,
    'description' => 'Conference Room',
    'base_price' => 75.00,
    'currency' => 'USD',
    'booking_duration_hours' => 1, // 1-hour slots
    'buffer_minutes' => 15, // 15 minutes between bookings
];

// Example 2: Create a venue with custom schedule
$venueDataCustom = [
    'category_id' => 2,
    'description' => 'Wedding Hall',
    'base_price' => 500.00,
    'currency' => 'USD',
    'booking_duration_hours' => 4, // 4-hour slots (typical for events)
    'buffer_minutes' => 30, // 30 minutes for cleanup
    'schedules' => [
        // Monday - Friday: 9 AM - 6 PM
        [
            'day_of_week' => 1, // Monday
            'open_time' => '09:00',
            'close_time' => '18:00',
            'is_closed' => false,
        ],
        [
            'day_of_week' => 2, // Tuesday
            'open_time' => '09:00',
            'close_time' => '18:00',
            'is_closed' => false,
        ],
        [
            'day_of_week' => 3, // Wednesday
            'open_time' => '09:00',
            'close_time' => '18:00',
            'is_closed' => false,
        ],
        [
            'day_of_week' => 4, // Thursday
            'open_time' => '09:00',
            'close_time' => '18:00',
            'is_closed' => false,
        ],
        [
            'day_of_week' => 5, // Friday
            'open_time' => '09:00',
            'close_time' => '18:00',
            'is_closed' => false,
        ],
        // Saturday: 10 AM - 10 PM (extended hours)
        [
            'day_of_week' => 6,
            'open_time' => '10:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ],
        // Sunday: Closed
        [
            'day_of_week' => 0,
            'open_time' => '00:00',
            'close_time' => '00:00',
            'is_closed' => true,
        ],
    ],
];

// Example 3: 24-hour venue with 2-hour slots
$venue24Hour = [
    'category_id' => 3,
    'description' => 'Co-working Space',
    'base_price' => 25.00,
    'currency' => 'USD',
    'booking_duration_hours' => 2,
    'buffer_minutes' => 0, // No buffer for continuous access
    'schedules' => [
        // Open 24/7
        ['day_of_week' => 0, 'open_time' => '00:00', 'close_time' => '23:59', 'is_closed' => false],
        ['day_of_week' => 1, 'open_time' => '00:00', 'close_time' => '23:59', 'is_closed' => false],
        ['day_of_week' => 2, 'open_time' => '00:00', 'close_time' => '23:59', 'is_closed' => false],
        ['day_of_week' => 3, 'open_time' => '00:00', 'close_time' => '23:59', 'is_closed' => false],
        ['day_of_week' => 4, 'open_time' => '00:00', 'close_time' => '23:59', 'is_closed' => false],
        ['day_of_week' => 5, 'open_time' => '00:00', 'close_time' => '23:59', 'is_closed' => false],
        ['day_of_week' => 6, 'open_time' => '00:00', 'close_time' => '23:59', 'is_closed' => false],
    ],
];

/*
 * Expected Response Format:
 * 
 * When creating a venue, the response includes:
 * {
 *   "success": true,
 *   "message": "Venue created successfully",
 *   "data": {
 *     "venue": { ... venue details with schedules ... },
 *     "available_time_periods": {
 *       "Monday": {
 *         "day_of_week": 1,
 *         "is_closed": false,
 *         "open_time": "09:00:00",
 *         "close_time": "18:00:00",
 *         "available_slots": [
 *           {
 *             "start_time": "09:00",
 *             "end_time": "10:00",
 *             "duration_hours": 1
 *           },
 *           {
 *             "start_time": "10:15",
 *             "end_time": "11:15",
 *             "duration_hours": 1
 *           },
 *           // ... more slots
 *         ]
 *       },
 *       // ... other days
 *     }
 *   }
 * }
 */

/*
 * How Time Slots are Calculated:
 * 
 * For a venue with:
 * - Opening: 09:00
 * - Closing: 17:00
 * - Booking Duration: 2 hours
 * - Buffer: 30 minutes
 * 
 * Available slots:
 * 1. 09:00 - 11:00 (2 hours)
 * 2. 11:30 - 13:30 (2 hours) [30 min buffer after previous]
 * 3. 14:00 - 16:00 (2 hours) [30 min buffer after previous]
 * 
 * Note: 16:00-18:00 is NOT available because it would end after closing time (17:00)
 */

/*
 * API Endpoints:
 * 
 * 1. Create Venue with Schedule:
 *    POST /api/provider/venues
 *    Body: JSON with venue data including schedules
 * 
 * 2. Get Available Time Periods:
 *    GET /api/provider/venues/{id}/available-time-periods
 *    Returns all available time slots for each day of the week
 * 
 * 3. View Venue with Schedule:
 *    GET /api/provider/venues/{id}
 *    Includes schedules in the response
 */
