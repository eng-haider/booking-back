# Enhanced Venue Scheduling with Real-Time Availability

## 🎯 What's New?

The scheduling endpoints now accept an optional `date` parameter that provides **real-time availability** by checking actual bookings and showing which time slots are available vs. booked.

---

## 📊 Two Modes of Operation

### Mode 1: Without Date (Weekly Overview)

Returns all available time slots for the entire week based on schedules only.

### Mode 2: With Date (Real Availability)

Returns availability for a specific date, marking slots as available or booked based on actual bookings.

---

## 🔗 API Endpoints

### Provider Endpoints

#### Get Available Time Periods

```
GET /api/provider/venues/{id}/available-time-periods
GET /api/provider/venues/{id}/available-time-periods?date=2025-12-28
```

### Customer Endpoints

#### View Venue

```
GET /api/customer/venues/{id}
GET /api/customer/venues/{id}?date=2025-12-28
```

#### Get Available Time Periods

```
GET /api/customer/venues/{id}/available-time-periods
GET /api/customer/venues/{id}/available-time-periods?date=2025-12-28
```

---

## 📝 Request Examples

### Without Date - Get Weekly Schedule

```bash
curl "http://localhost:8000/api/customer/venues/1/available-time-periods"
```

**Response:**

```json
{
  "success": true,
  "data": {
    "venue_id": 1,
    "date": null,
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
        ]
      },
      "Tuesday": {...},
      ...
    }
  }
}
```

### With Date - Get Real Availability

```bash
curl "http://localhost:8000/api/customer/venues/1/available-time-periods?date=2025-12-28"
```

**Response:**

```json
{
  "success": true,
  "data": {
    "venue_id": 1,
    "date": "2025-12-28",
    "available_time_periods": {
      "date": "2025-12-28",
      "day_name": "Saturday",
      "day_of_week": 6,
      "is_closed": false,
      "open_time": "10:00:00",
      "close_time": "22:00:00",
      "available_slots": [
        {
          "start_time": "10:00",
          "end_time": "14:00",
          "duration_hours": 4,
          "is_available": true
        },
        {
          "start_time": "14:30",
          "end_time": "18:30",
          "duration_hours": 4,
          "is_available": false
        },
        {
          "start_time": "19:00",
          "end_time": "23:00",
          "duration_hours": 4,
          "is_available": true
        }
      ],
      "booked_slots": [
        {
          "booking_id": 123,
          "start_time": "14:30",
          "end_time": "18:30",
          "status": "confirmed"
        }
      ]
    }
  }
}
```

---

## 🎨 Key Features

### 1. Real-Time Availability Check

- ✅ Checks actual bookings for the specified date
- ✅ Marks each slot as `is_available: true/false`
- ✅ Shows conflicting bookings in `booked_slots`

### 2. Smart Conflict Detection

- Detects time overlaps between slots and bookings
- Only considers `pending` and `confirmed` bookings
- Ignores `cancelled` and `completed` bookings

### 3. Flexible Usage

- **No date**: Get weekly schedule overview
- **With date**: Get real availability for specific day

### 4. Comprehensive Information

When date is provided, response includes:

- `date`: The requested date
- `day_name`: Day name (e.g., "Saturday")
- `day_of_week`: Day number (0-6)
- `available_slots`: All slots with availability status
- `booked_slots`: List of existing bookings

---

## 💻 Frontend Integration Examples

### React Example - Booking Calendar

```javascript
import React, { useState, useEffect } from "react";

function BookingCalendar({ venueId }) {
  const [selectedDate, setSelectedDate] = useState("2025-12-28");
  const [availability, setAvailability] = useState(null);

  useEffect(() => {
    // Fetch availability for selected date
    fetch(
      `/api/customer/venues/${venueId}/available-time-periods?date=${selectedDate}`
    )
      .then((res) => res.json())
      .then((data) => setAvailability(data.data.available_time_periods));
  }, [venueId, selectedDate]);

  if (!availability) return <div>Loading...</div>;

  return (
    <div className="booking-calendar">
      <h2>
        {availability.day_name}, {availability.date}
      </h2>

      {availability.is_closed ? (
        <p>Venue is closed on this day</p>
      ) : (
        <>
          <p>
            Hours: {availability.open_time} - {availability.close_time}
          </p>

          <div className="time-slots">
            {availability.available_slots.map((slot, index) => (
              <button
                key={index}
                className={slot.is_available ? "available" : "booked"}
                disabled={!slot.is_available}
              >
                {slot.start_time} - {slot.end_time}
                {!slot.is_available && " (Booked)"}
              </button>
            ))}
          </div>

          {availability.booked_slots.length > 0 && (
            <div className="booked-info">
              <h4>Existing Bookings:</h4>
              {availability.booked_slots.map((booking) => (
                <div key={booking.booking_id}>
                  {booking.start_time} - {booking.end_time} ({booking.status})
                </div>
              ))}
            </div>
          )}
        </>
      )}
    </div>
  );
}
```

### JavaScript - Date Selector

```javascript
// Get availability for next 7 days
async function getWeeklyAvailability(venueId) {
  const today = new Date();
  const availabilityByDate = {};

  for (let i = 0; i < 7; i++) {
    const date = new Date(today);
    date.setDate(date.getDate() + i);
    const dateString = date.toISOString().split("T")[0];

    const response = await fetch(
      `/api/customer/venues/${venueId}/available-time-periods?date=${dateString}`
    );
    const data = await response.json();

    availabilityByDate[dateString] = {
      dayName: data.data.available_time_periods.day_name,
      availableCount: data.data.available_time_periods.available_slots.filter(
        (slot) => slot.is_available
      ).length,
      totalSlots: data.data.available_time_periods.available_slots.length,
    };
  }

  return availabilityByDate;
}

// Usage
const availability = await getWeeklyAvailability(1);
console.log(availability);
// {
//   "2025-12-23": { dayName: "Monday", availableCount: 5, totalSlots: 6 },
//   "2025-12-24": { dayName: "Tuesday", availableCount: 4, totalSlots: 6 },
//   ...
// }
```

---

## 🔍 Use Cases

### Use Case 1: Browse Venue

Customer browses venue without selecting a date:

```
GET /api/customer/venues/1
```

→ Shows all weekly time slots (no booking check)

### Use Case 2: Select Specific Date

Customer selects December 28, 2025:

```
GET /api/customer/venues/1?date=2025-12-28
```

→ Shows only Saturday's slots with real availability

### Use Case 3: Compare Multiple Dates

Customer checks availability for multiple dates:

```javascript
const dates = ["2025-12-28", "2025-12-29", "2025-12-30"];
const results = await Promise.all(
  dates.map((date) =>
    fetch(`/api/customer/venues/1/available-time-periods?date=${date}`).then(
      (r) => r.json()
    )
  )
);
```

### Use Case 4: Provider Checks Bookings

Provider checks their venue's availability:

```
GET /api/provider/venues/1/available-time-periods?date=2025-12-28
Authorization: Bearer {provider_token}
```

→ See which slots are booked for a specific date

---

## 📋 Response Structure Differences

### Without Date Parameter

```json
{
  "available_time_periods": {
    "Monday": { "available_slots": [...] },
    "Tuesday": { "available_slots": [...] },
    // ... all 7 days
  }
}
```

### With Date Parameter

```json
{
  "available_time_periods": {
    "date": "2025-12-28",
    "day_name": "Saturday",
    "available_slots": [
      { "start_time": "10:00", "end_time": "14:00", "is_available": true },
      { "start_time": "14:30", "end_time": "18:30", "is_available": false }
    ],
    "booked_slots": [
      { "booking_id": 123, "start_time": "14:30", "end_time": "18:30" }
    ]
  }
}
```

---

## ✅ Validation

### Date Format

- **Required format**: `Y-m-d` (e.g., `2025-12-28`)
- **Invalid examples**: `12/28/2025`, `28-12-2025`, `2025-12-28 10:00:00`

### Error Response

```json
{
  "success": false,
  "message": "Invalid date format. Use Y-m-d format (e.g., 2025-12-25)"
}
```

---

## 🔐 Security Notes

- Only bookings with status `pending` or `confirmed` are considered
- Cancelled and completed bookings don't block slots
- Customer endpoints are public (no auth required)
- Provider endpoints require authentication

---

## 🎯 Benefits

1. **Real-Time Accuracy**: See actual availability, not just theoretical slots
2. **Better UX**: Customers don't pick unavailable times
3. **Reduced Errors**: Prevent double bookings
4. **Flexibility**: Can view weekly schedule OR specific date
5. **Transparency**: Shows which bookings conflict with slots

---

## 🚀 Performance Considerations

- Date-specific queries are optimized with database indexes
- Only one day is checked when date is provided
- Booking status filter reduces query load
- Results are not cached to ensure real-time accuracy

---

## 📊 Example Workflow

1. **Customer browses venues**

   ```
   GET /api/customer/venues?include=photos,amenities
   ```

2. **Customer views venue details**

   ```
   GET /api/customer/venues/1
   ```

   → Gets weekly schedule overview

3. **Customer selects date from calendar**

   ```
   GET /api/customer/venues/1/available-time-periods?date=2025-12-28
   ```

   → Gets real availability for that day

4. **Customer sees available slots and picks one**

   - 10:00-14:00 ✅ Available
   - 14:30-18:30 ❌ Booked
   - 19:00-23:00 ✅ Available

5. **Customer creates booking**
   ```
   POST /api/customer/bookings
   { "venue_id": 1, "booking_date": "2025-12-28", "start_time": "10:00" }
   ```

---

**Updated:** December 23, 2025  
**Status:** ✅ Implemented and Tested  
**Breaking Changes:** None (backward compatible - date is optional)
