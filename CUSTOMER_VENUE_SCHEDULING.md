# Customer Venue Scheduling API Documentation

## Overview

Customers can now view available time periods for venues when browsing or booking. This makes it easy for customers to see all available booking slots based on the venue's schedule and booking duration.

## Customer API Endpoints

### 1. View Venue with Available Time Periods

**GET** `/api/customer/venues/{id}`

Returns venue details along with all available time periods for the week.

#### Response:

```json
{
  "success": true,
  "data": {
    "venue": {
      "id": 1,
      "provider_id": 1,
      "category_id": 1,
      "name": "Grand Event Hall",
      "description": "A beautiful venue for events and conferences",
      "base_price": "150.00",
      "currency": "USD",
      "buffer_minutes": 30,
      "booking_duration_hours": 2,
      "status": "active",
      "schedules": [
        {
          "id": 1,
          "venue_id": 1,
          "day_of_week": 1,
          "open_time": "09:00:00",
          "close_time": "21:00:00",
          "is_closed": false
        },
        // ... other schedules
      ],
      "amenities": [...],
      "photos": [...],
      "reviews": [...]
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
            "start_time": "11:30",
            "end_time": "13:30",
            "duration_hours": 2
          },
          {
            "start_time": "14:00",
            "end_time": "16:00",
            "duration_hours": 2
          },
          {
            "start_time": "16:30",
            "end_time": "18:30",
            "duration_hours": 2
          },
          {
            "start_time": "19:00",
            "end_time": "21:00",
            "duration_hours": 2
          }
        ]
      },
      "Tuesday": {
        "day_of_week": 2,
        "is_closed": false,
        "open_time": "09:00:00",
        "close_time": "21:00:00",
        "available_slots": [...]
      },
      // ... other days
      "Sunday": {
        "day_of_week": 0,
        "is_closed": true,
        "open_time": null,
        "close_time": null,
        "available_slots": []
      }
    }
  }
}
```

### 2. Get Available Time Periods Only

**GET** `/api/customer/venues/{id}/available-time-periods`

Returns only the available time periods for a venue without all venue details.

#### Response:

```json
{
  "success": true,
  "data": {
    "venue_id": 1,
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
            "start_time": "11:30",
            "end_time": "13:30",
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

### 3. Browse All Venues (with schedules)

**GET** `/api/customer/venues?include=schedules`

You can include schedules when browsing venues:

#### Request:

```
GET /api/customer/venues?include=schedules,photos,amenities
```

#### Response:

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Event Hall",
        "base_price": "100.00",
        "booking_duration_hours": 2,
        "schedules": [
          {
            "day_of_week": 1,
            "open_time": "09:00:00",
            "close_time": "21:00:00",
            "is_closed": false
          }
          // ... more schedules
        ],
        "photos": [...],
        "amenities": [...]
      }
      // ... more venues
    ],
    "links": {...},
    "meta": {...}
  }
}
```

## Usage Examples

### For Frontend/Mobile App Developers

#### 1. Display venue details with booking options:

```javascript
// Fetch venue with available time periods
const response = await fetch("/api/customer/venues/1");
const { data } = await response.json();

console.log("Venue:", data.venue.name);
console.log("Booking Duration:", data.venue.booking_duration_hours, "hours");

// Display available slots for each day
Object.entries(data.available_time_periods).forEach(([day, schedule]) => {
  console.log(`\n${day}:`);
  if (schedule.is_closed) {
    console.log("  Closed");
  } else {
    console.log(`  Open: ${schedule.open_time} - ${schedule.close_time}`);
    schedule.available_slots.forEach((slot) => {
      console.log(`  - ${slot.start_time} to ${slot.end_time}`);
    });
  }
});
```

#### 2. Show booking slots in a calendar or time picker:

```javascript
// Get only the time periods
const response = await fetch("/api/customer/venues/1/available-time-periods");
const { data } = await response.json();

// Create time slot options for a booking form
const mondaySlots = data.available_time_periods.Monday.available_slots;

mondaySlots.forEach((slot) => {
  // Add to your booking form dropdown/calendar
  addTimeSlotOption({
    label: `${slot.start_time} - ${slot.end_time}`,
    value: slot.start_time,
    duration: slot.duration_hours,
  });
});
```

#### 3. Filter venues by availability on a specific day:

```javascript
// Fetch multiple venues
const response = await fetch("/api/customer/venues?include=schedules");
const { data } = await response.json();

// Filter venues open on Monday
const mondayVenues = data.data.filter((venue) => {
  const mondaySchedule = venue.schedules.find((s) => s.day_of_week === 1);
  return mondaySchedule && !mondaySchedule.is_closed;
});
```

## Benefits for Customers

1. **Clear Availability**: See exactly when a venue is available
2. **Easy Booking**: Pick from pre-defined time slots
3. **Time Transparency**: Know the booking duration and buffer time
4. **Day Planning**: See different availability for different days
5. **Quick Decisions**: Compare multiple venues' schedules at a glance

## Integration with Booking Flow

When creating a booking, customers can now:

1. **View venue details** → See available time periods
2. **Select a day** → See available slots for that day
3. **Choose a time slot** → Use the start_time from available_slots
4. **Create booking** with the selected date and start time

### Example Booking Flow:

```javascript
// Step 1: View venue and available times
GET /api/customer/venues/1

// Step 2: Customer selects Monday, 11:30-13:30 slot

// Step 3: Create booking with selected time
POST /api/customer/bookings
{
  "venue_id": 1,
  "booking_date": "2025-12-29",  // A Monday
  "start_time": "11:30",
  "duration_hours": 2
}
```

## Public Access

All customer venue endpoints are **public** (no authentication required), making it easy for:

- Guest browsing
- Search engines to index venues
- Marketing integrations
- Mobile apps with guest mode

## Technical Details

### Modified Files:

1. **`app/Repositories/Customer/VenueRepository.php`**

   - Added ScheduleService dependency
   - Added `getAvailableTimePeriods()` method

2. **`app/Http/Controllers/Customer/VenueController.php`**

   - Updated `show()` to include time periods
   - Added `availableTimePeriods()` method

3. **`routes/customer.php`**
   - Added `/venues/{id}/available-time-periods` route

### Response Structure:

- Day names: Sunday, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday
- Times: 24-hour format (H:i) e.g., "09:00", "14:30"
- Closed days return empty `available_slots` array

## Related Documentation

- See `VENUE_SCHEDULING_FEATURE.md` for provider-side documentation
- See `VENUE_SCHEDULING_EXAMPLES.php` for code examples
