# Enhanced Booking System with Schedule Integration

## 🎯 What Changed?

The booking creation process has been **completely integrated** with the new scheduling system. Customers no longer need to specify `end_time` - it's calculated automatically based on the venue's `booking_duration_hours`.

---

## 🔄 Before vs After

### ❌ Old System (Before)

**Request:**

```json
POST /api/customer/bookings
{
  "venue_id": 1,
  "booking_date": "2025-12-28",
  "start_time": "14:30",
  "end_time": "18:30",     ← Customer had to calculate
  "notes": "Wedding reception"
}
```

**Problems:**

- Customer had to calculate end_time manually
- No validation against venue schedules
- Could book outside operating hours
- Manual calculation errors
- Inconsistent with booking_duration_hours

---

### ✅ New System (After)

**Request:**

```json
POST /api/customer/bookings
{
  "venue_id": 1,
  "booking_date": "2025-12-28",
  "start_time": "14:30",   ← Just pick from available slots
  "number_of_guests": 150,
  "notes": "Wedding reception"
}
```

**Automatic Processing:**

1. ✅ Fetches venue's `booking_duration_hours` (e.g., 4 hours)
2. ✅ Calculates `end_time` automatically (14:30 + 4h = 18:30)
3. ✅ Validates against venue's schedule
4. ✅ Checks for booking conflicts
5. ✅ Calculates total price
6. ✅ Creates booking with all details

---

## 📊 Complete Booking Flow

### Step 1: Customer Views Available Times

```bash
GET /api/customer/venues/1/available-time-periods?date=2025-12-28
```

**Response:**

```json
{
  "available_slots": [
    { "start_time": "10:00", "end_time": "14:00", "is_available": true },
    { "start_time": "14:30", "end_time": "18:30", "is_available": true },
    { "start_time": "19:00", "end_time": "23:00", "is_available": false }
  ]
}
```

### Step 2: Customer Selects a Slot

Customer picks: **14:30 - 18:30**

### Step 3: Customer Creates Booking

```bash
POST /api/customer/bookings
Authorization: Bearer {token}
Content-Type: application/json

{
  "venue_id": 1,
  "booking_date": "2025-12-28",
  "start_time": "14:30",
  "number_of_guests": 150,
  "notes": "Wedding reception for 150 guests",
  "special_requests": "Need extra chairs"
}
```

### Step 4: System Response

#### ✅ Success Response:

```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "id": 456,
    "booking_reference": "BK8A9F7E21",
    "customer_id": 5,
    "venue_id": 1,
    "booking_date": "2025-12-28",
    "start_time": "14:30:00",
    "end_time": "18:30:00",      ← Calculated automatically
    "total_price": "2000.00",    ← Calculated automatically
    "number_of_guests": 150,
    "status_id": 1,
    "status": {
      "id": 1,
      "name": "Pending",
      "slug": "pending"
    },
    "venue": {
      "id": 1,
      "name": "Grand Wedding Hall",
      "booking_duration_hours": 4,
      "price_per_hour": 500.00
    },
    "notes": "Wedding reception for 150 guests",
    "special_requests": "Need extra chairs",
    "created_at": "2025-12-23T20:00:00.000000Z"
  }
}
```

#### ❌ Error Response (Time Slot Booked):

```json
{
  "success": false,
  "message": "Time slot is already booked",
  "data": {
    "end_time": "18:30",
    "conflicting_booking": {
      "id": 123,
      "start_time": "14:30",
      "end_time": "18:30"
    }
  }
}
```

#### ❌ Error Response (Outside Operating Hours):

```json
{
  "success": false,
  "message": "Time slot is outside venue operating hours",
  "data": {
    "end_time": "18:30"
  }
}
```

---

## 🔍 What Gets Validated?

### 1. Venue Schedule Validation

```
✓ Is the venue open on this day?
✓ Is start_time within operating hours?
✓ Is end_time within operating hours?
✓ Does it match the booking_duration_hours?
```

### 2. Booking Conflict Validation

```
✓ Are there existing confirmed bookings?
✓ Are there pending bookings?
✓ Does the time overlap with any booking?
```

### 3. Automatic Calculations

```
✓ end_time = start_time + booking_duration_hours
✓ total_price = price_per_hour × duration
✓ booking_reference = auto-generated
```

---

## 📋 API Changes Summary

### Updated Request Validation

#### Required Fields:

- `venue_id` - Venue to book
- `booking_date` - Date of booking (Y-m-d format)
- `start_time` - Start time (H:i format, e.g., "14:30")

#### Optional Fields:

- `number_of_guests` - Number of guests attending
- `notes` - Booking notes
- `special_requests` - Special requests

#### Removed Fields:

- ~~`end_time`~~ - Now calculated automatically ✨

---

## 💻 Frontend Integration Examples

### React Example

```javascript
import React, { useState, useEffect } from "react";

function BookingForm({ venueId, selectedDate }) {
  const [venue, setVenue] = useState(null);
  const [availability, setAvailability] = useState(null);
  const [selectedSlot, setSelectedSlot] = useState(null);

  useEffect(() => {
    // Fetch venue details
    fetch(`/api/customer/venues/${venueId}`)
      .then((res) => res.json())
      .then((data) => setVenue(data.data.venue));

    // Fetch availability for selected date
    fetch(
      `/api/customer/venues/${venueId}/available-time-periods?date=${selectedDate}`
    )
      .then((res) => res.json())
      .then((data) => setAvailability(data.data.available_time_periods));
  }, [venueId, selectedDate]);

  const handleBooking = async (e) => {
    e.preventDefault();

    if (!selectedSlot) {
      alert("Please select a time slot");
      return;
    }

    const bookingData = {
      venue_id: venueId,
      booking_date: selectedDate,
      start_time: selectedSlot.start_time, // No need for end_time!
      number_of_guests: document.getElementById("guests").value,
      notes: document.getElementById("notes").value,
    };

    try {
      const response = await fetch("/api/customer/bookings", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
        body: JSON.stringify(bookingData),
      });

      const result = await response.json();

      if (result.success) {
        alert("Booking created successfully!");
        // Redirect to booking details or payment
        window.location.href = `/bookings/${result.data.id}`;
      } else {
        alert(`Booking failed: ${result.message}`);
      }
    } catch (error) {
      alert("An error occurred. Please try again.");
    }
  };

  if (!venue || !availability) return <div>Loading...</div>;

  return (
    <form onSubmit={handleBooking}>
      <h2>{venue.name}</h2>
      <p>Date: {selectedDate}</p>
      <p>Duration: {venue.booking_duration_hours} hours per booking</p>
      <p>Price: ${venue.price_per_hour}/hour</p>

      <h3>Select Time Slot</h3>
      <div className="time-slots">
        {availability.available_slots
          .filter((slot) => slot.is_available)
          .map((slot, index) => (
            <button
              key={index}
              type="button"
              className={selectedSlot === slot ? "selected" : ""}
              onClick={() => setSelectedSlot(slot)}
            >
              {slot.start_time} - {slot.end_time}
              <br />
              (${venue.price_per_hour * venue.booking_duration_hours})
            </button>
          ))}
      </div>

      {selectedSlot && (
        <div className="booking-details">
          <h4>Booking Summary</h4>
          <p>
            Time: {selectedSlot.start_time} - {selectedSlot.end_time}
          </p>
          <p>Duration: {venue.booking_duration_hours} hours</p>
          <p>Total: ${venue.price_per_hour * venue.booking_duration_hours}</p>

          <label>Number of Guests:</label>
          <input type="number" id="guests" min="1" required />

          <label>Notes:</label>
          <textarea id="notes"></textarea>

          <button type="submit">Confirm Booking</button>
        </div>
      )}
    </form>
  );
}
```

### Simple JavaScript Example

```javascript
// Step 1: Get available time slots
async function getAvailableSlots(venueId, date) {
  const response = await fetch(
    `/api/customer/venues/${venueId}/available-time-periods?date=${date}`
  );
  const data = await response.json();
  return data.data.available_time_periods.available_slots.filter(
    (slot) => slot.is_available
  );
}

// Step 2: Create booking (simplified)
async function createBooking(venueId, date, startTime) {
  const response = await fetch("/api/customer/bookings", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({
      venue_id: venueId,
      booking_date: date,
      start_time: startTime,
      number_of_guests: 100,
    }),
  });

  return await response.json();
}

// Usage
const slots = await getAvailableSlots(1, "2025-12-28");
console.log("Available slots:", slots);

// Customer selects first available slot
const result = await createBooking(1, "2025-12-28", slots[0].start_time);
console.log("Booking result:", result);
```

---

## 🔧 Technical Details

### Files Modified:

1. **`app/Http/Requests/Customer/StoreBookingRequest.php`**

   - Removed `end_time` validation
   - Added `number_of_guests` validation
   - Added custom error messages

2. **`app/Repositories/Customer/BookingRepository.php`**

   - Added `ScheduleService` dependency injection
   - Updated `create()` method to calculate `end_time`
   - Enhanced `isTimeSlotAvailable()` to validate schedules
   - Returns detailed availability information

3. **`app/Http/Controllers/Customer/BookingController.php`**
   - Updated `store()` method to use new availability check
   - Improved error responses with details

---

## ✅ Benefits

1. **Simpler for Customers**

   - Just pick start_time from available slots
   - No manual time calculation needed
   - Less room for error

2. **Schedule-Aware**

   - Validates against venue operating hours
   - Respects booking_duration_hours
   - Prevents invalid bookings

3. **Better Error Messages**

   - Clear reason why booking failed
   - Shows conflicting booking details
   - Helps customer choose alternative

4. **Automatic Calculations**

   - End time calculated correctly
   - Price calculated based on duration
   - Consistent with venue settings

5. **Race Condition Prevention**
   - Validates availability at booking time
   - Checks for conflicts
   - Atomic transaction

---

## 🧪 Testing the New System

### Test 1: Successful Booking

```bash
curl -X POST http://localhost:8000/api/customer/bookings \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "venue_id": 1,
    "booking_date": "2025-12-28",
    "start_time": "14:30",
    "number_of_guests": 100
  }'
```

### Test 2: Booking Outside Operating Hours

```bash
curl -X POST http://localhost:8000/api/customer/bookings \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "venue_id": 1,
    "booking_date": "2025-12-28",
    "start_time": "23:00",
    "number_of_guests": 100
  }'
```

### Test 3: Booking Conflicting Time

```bash
# First create a booking at 14:30
# Then try to create another at the same time
curl -X POST http://localhost:8000/api/customer/bookings \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "venue_id": 1,
    "booking_date": "2025-12-28",
    "start_time": "14:30",
    "number_of_guests": 50
  }'
```

---

## 📊 Comparison Table

| Feature                 | Old System   | New System        |
| ----------------------- | ------------ | ----------------- |
| **end_time input**      | Required     | Not needed ✨     |
| **Schedule validation** | No           | Yes ✅            |
| **Duration control**    | Manual       | Automatic ✅      |
| **Price calculation**   | Manual hours | Venue settings ✅ |
| **Error details**       | Simple       | Detailed ✅       |
| **Conflict info**       | No           | Shows details ✅  |
| **User experience**     | Complex      | Simple ✅         |

---

## 🚀 Migration Guide

If you have existing frontend code, here's how to update it:

### Old Code:

```javascript
const booking = {
  venue_id: 1,
  booking_date: "2025-12-28",
  start_time: "14:30",
  end_time: "18:30", // Remove this
  notes: "Event",
};
```

### New Code:

```javascript
const booking = {
  venue_id: 1,
  booking_date: "2025-12-28",
  start_time: "14:30", // Keep this
  number_of_guests: 100, // Optional: add this
  notes: "Event",
};
```

That's it! Just remove the `end_time` field. Everything else works automatically! 🎉

---

**Status:** ✅ Complete and Tested  
**Breaking Changes:** `end_time` no longer accepted in request (backward incompatible)  
**Benefits:** Simpler, safer, schedule-aware booking system
