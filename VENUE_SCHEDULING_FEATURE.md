# Venue Scheduling Feature Documentation

## Overview

This feature allows venues to have customizable schedules with booking duration and available time periods. When creating a venue, schedules are automatically created, and the system generates available time slots for customers to book.

## Features

### 1. Booking Duration

- Each venue can define a `booking_duration_hours` (default: 1 hour)
- This defines the length of each booking slot
- Can be set from 1 to 24 hours

### 2. Schedules

- Each venue has 7 schedules (one for each day of the week)
- Days are numbered 0-6 (0 = Sunday, 6 = Saturday)
- Each schedule includes:
  - `day_of_week`: Integer (0-6)
  - `open_time`: Time in H:i format (e.g., "09:00")
  - `close_time`: Time in H:i format (e.g., "21:00")
  - `is_closed`: Boolean (if true, venue is closed on that day)

### 3. Time Period Generation

- The system automatically generates available time slots based on:
  - Opening and closing times
  - Booking duration
  - Buffer minutes between bookings
- Time slots are returned in the response when creating or viewing a venue

## Database Changes

### Migration

A new field was added to the `venues` table:

- `booking_duration_hours` (integer, default: 1)

## API Endpoints

### 1. Create Venue with Schedule

**POST** `/api/provider/venues`

#### Request Body:

```json
{
  "category_id": 1,
  "description": "A beautiful venue for events",
  "base_price": 100,
  "currency": "USD",
  "buffer_minutes": 15,
  "booking_duration_hours": 2,
  "schedules": [
    {
      "day_of_week": 1,
      "open_time": "09:00",
      "close_time": "21:00",
      "is_closed": false
    },
    {
      "day_of_week": 2,
      "open_time": "09:00",
      "close_time": "21:00",
      "is_closed": false
    },
    {
      "day_of_week": 3,
      "open_time": "09:00",
      "close_time": "21:00",
      "is_closed": false
    },
    {
      "day_of_week": 4,
      "open_time": "09:00",
      "close_time": "21:00",
      "is_closed": false
    },
    {
      "day_of_week": 5,
      "open_time": "09:00",
      "close_time": "21:00",
      "is_closed": false
    },
    {
      "day_of_week": 6,
      "open_time": "10:00",
      "close_time": "18:00",
      "is_closed": false
    },
    {
      "day_of_week": 0,
      "open_time": "10:00",
      "close_time": "18:00",
      "is_closed": true
    }
  ],
  "amenity_ids": [1, 2, 3]
}
```

#### Response:

```json
{
  "success": true,
  "message": "Venue created successfully",
  "data": {
    "venue": {
      "id": 1,
      "provider_id": 1,
      "category_id": 1,
      "name": "Event Hall",
      "description": "A beautiful venue for events",
      "base_price": "100.00",
      "currency": "USD",
      "buffer_minutes": 15,
      "booking_duration_hours": 2,
      "status": "active",
      "created_at": "2025-12-23T19:53:10.000000Z",
      "updated_at": "2025-12-23T19:53:10.000000Z",
      "schedules": [
        {
          "id": 1,
          "venue_id": 1,
          "day_of_week": 1,
          "open_time": "09:00:00",
          "close_time": "21:00:00",
          "is_closed": false
        }
        // ... other schedules
      ]
    },
    "available_time_periods": {
      "Monday": {
        "day_of_week": 1,
        "is_closed": false,
        "open_time": "09:00:00",
        "close_time": "21:00:00",
        "available_slots": [
          {
            "start_time": "09:00",
            "end_time": "11:00",
            "duration_hours": 2
          },
          {
            "start_time": "11:15",
            "end_time": "13:15",
            "duration_hours": 2
          },
          {
            "start_time": "13:30",
            "end_time": "15:30",
            "duration_hours": 2
          },
          {
            "start_time": "15:45",
            "end_time": "17:45",
            "duration_hours": 2
          },
          {
            "start_time": "18:00",
            "end_time": "20:00",
            "duration_hours": 2
          }
        ]
      },
      "Tuesday": {
        // ... similar structure
      }
      // ... other days
    }
  }
}
```

### 2. Get Available Time Periods

**GET** `/api/provider/venues/{id}/available-time-periods`

#### Response:

```json
{
  "success": true,
  "data": {
    "venue_id": 1,
    "venue_name": "Event Hall",
    "booking_duration_hours": 2,
    "buffer_minutes": 15,
    "available_time_periods": {
      "Monday": {
        "day_of_week": 1,
        "is_closed": false,
        "open_time": "09:00:00",
        "close_time": "21:00:00",
        "available_slots": [
          {
            "start_time": "09:00",
            "end_time": "11:00",
            "duration_hours": 2
          },
          {
            "start_time": "11:15",
            "end_time": "13:15",
            "duration_hours": 2
          }
          // ... more slots
        ]
      }
      // ... other days
    }
  }
}
```

## Default Behavior

If no schedules are provided when creating a venue, the system automatically creates default schedules:

- **All days (Monday-Sunday)**: 09:00 - 21:00
- **All days open**: No days marked as closed

## Validation Rules

### Venue Creation:

- `booking_duration_hours`: Optional, integer, min: 1, max: 24
- `schedules`: Optional array, min: 1, max: 7 items
- `schedules.*.day_of_week`: Required with schedules, integer, 0-6
- `schedules.*.open_time`: Required unless closed, format: H:i
- `schedules.*.close_time`: Required unless closed, format: H:i, must be after open_time
- `schedules.*.is_closed`: Optional boolean

## Code Structure

### New Files:

1. **`app/Services/ScheduleService.php`**

   - Handles schedule creation
   - Generates time periods
   - Validates time slots

2. **Migration**: `2025_12_23_195310_add_booking_duration_hours_to_venues_table.php`
   - Adds `booking_duration_hours` field to venues table

### Modified Files:

1. **`app/Models/Venue.php`**

   - Added `booking_duration_hours` to fillable and casts

2. **`app/Repositories/Provider/VenueRepository.php`**

   - Added dependency injection for ScheduleService
   - Added `createSchedules()` method
   - Added `getAvailableTimePeriods()` method

3. **`app/Http/Controllers/Provider/VenueController.php`**

   - Updated `store()` method to create schedules
   - Added `availableTimePeriods()` endpoint

4. **`app/Http/Requests/Provider/StoreVenueRequest.php`**

   - Added validation for `booking_duration_hours` and `schedules`

5. **`routes/provider.php`**
   - Added route for available time periods endpoint

## Usage Example

### Creating a venue with custom schedule:

```bash
curl -X POST https://api.example.com/api/provider/venues \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "category_id": 1,
    "description": "Meeting Room",
    "base_price": 50,
    "booking_duration_hours": 1,
    "buffer_minutes": 15,
    "schedules": [
      {
        "day_of_week": 1,
        "open_time": "08:00",
        "close_time": "17:00",
        "is_closed": false
      },
      {
        "day_of_week": 0,
        "is_closed": true
      }
    ]
  }'
```

### Getting available time periods:

```bash
curl -X GET https://api.example.com/api/provider/venues/1/available-time-periods \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Benefits

1. **Flexibility**: Providers can set custom hours for each day
2. **Automation**: Time slots are automatically generated
3. **Buffer Time**: Prevents back-to-back bookings with configurable buffer
4. **User-Friendly**: Customers see clear available time slots
5. **Scalable**: Works with any booking duration from 1-24 hours

## Future Enhancements

Potential improvements:

- Support for multiple time blocks per day
- Holiday/special day schedules
- Real-time availability checking against existing bookings
- Dynamic pricing based on time slots
- Recurring schedule templates
