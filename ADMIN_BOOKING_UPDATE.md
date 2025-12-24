# Admin Booking System Update

## Summary
Updated the admin booking system to match the customer booking system with automatic calculation of `end_time` and `total_price` based on venue's `booking_duration_hours`.

## Changes Made

### 1. StoreBookingRequest.php
**Changed:**
- ❌ Removed `end_time` (required) - now calculated automatically
- ❌ Removed `resource_id` (required) - simplified to venue-based bookings
- ❌ Changed `user_id` from required to nullable
- ❌ Changed `total_price` from required to nullable (auto-calculated)
- ✅ Added `number_of_guests` (optional)

**New Request Structure:**
```json
{
  "customer_id": 1,
  "user_id": null,
  "venue_id": 5,
  "booking_date": "2025-01-15",
  "start_time": "14:00:00",
  "number_of_guests": 50,
  "notes": "Birthday party",
  "special_requests": "Need projector"
}
```

### 2. BookingRepository.php (Admin)

**Added Imports:**
```php
use App\Models\Status;
use Illuminate\Support\Facades\DB;
```

**Updated `create()` Method:**
- Wrapped in database transaction
- Automatically calculates `end_time` based on venue's `booking_duration_hours`
- Automatically calculates `total_price` based on:
  - `price_per_hour * booking_duration_hours` (if available)
  - OR `base_price` (if available)
  - OR defaults to `0`
- Converts status string to `status_id` via Status model
- Generates `booking_reference` if not provided

**Updated `isTimeSlotAvailable()` Method:**
- Changed from `resource_id` to `venue_id`
- Changed from direct status check to relationship: `whereHas('status')`
- Fixed SQL error by using proper relationship query

### 3. BookingController.php (Admin)

**Updated `store()` Method:**
- Calculates `end_time` for availability check
- Uses `venue_id` instead of `resource_id`
- Passes only `start_time` (end_time calculated in repository)

**Updated `update()` Method:**
- Changed from `resource_id` to `venue_id`
- Automatically recalculates `end_time` when time/date changes
- Adds calculated `end_time` to update data

**Updated `checkAvailability()` Method:**
- Changed validation from `resource_id` to `venue_id`
- Removed `end_time` requirement
- Calculates `end_time` based on venue
- Returns additional info: `booking_duration_hours` and `calculated_end_time`

## How It Works Now

### Creating a Booking

**Step 1: Admin sends minimal data**
```bash
POST /api/admin/bookings
{
  "customer_id": 1,
  "venue_id": 5,
  "booking_date": "2025-01-15",
  "start_time": "14:00:00"
}
```

**Step 2: System automatically:**
1. Fetches venue with `booking_duration_hours` (e.g., 3 hours)
2. Calculates `end_time` = `14:00:00` + 3 hours = `17:00:00`
3. Checks if slot `14:00-17:00` is available
4. Calculates `total_price` = `price_per_hour * 3` or `base_price`
5. Sets status to "pending" via Status model
6. Generates unique `booking_reference`
7. Creates booking in database

**Step 3: Response includes calculated values**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "id": 123,
    "venue_id": 5,
    "start_time": "14:00:00",
    "end_time": "17:00:00",
    "total_price": 150.00,
    "booking_reference": "BKG-20250115-ABCD1234"
  }
}
```

### Checking Availability

**Request:**
```bash
POST /api/admin/bookings/check-availability
{
  "venue_id": 5,
  "booking_date": "2025-01-15",
  "start_time": "14:00:00"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "available": true,
    "booking_duration_hours": 3,
    "calculated_end_time": "17:00:00"
  }
}
```

## Benefits

1. **Consistency:** Admin and customer booking systems now work the same way
2. **Simplicity:** Less data required from frontend
3. **Accuracy:** End time always matches venue's booking duration
4. **Maintainability:** Single source of truth for booking duration
5. **Flexibility:** Can still override `total_price` if needed

## Migration Notes

- Existing bookings are not affected
- Old API calls with `end_time` will fail validation
- Update admin frontend to:
  - Remove `end_time` field from booking form
  - Make `resource_id` optional or remove it
  - Make `user_id` optional
  - Make `total_price` optional (or remove from form)

## Testing

Test the updated endpoints:

```bash
# Create booking
curl -X POST http://localhost:8000/api/admin/bookings \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "venue_id": 5,
    "booking_date": "2025-01-20",
    "start_time": "10:00:00",
    "number_of_guests": 30
  }'

# Check availability
curl -X POST http://localhost:8000/api/admin/bookings/check-availability \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "venue_id": 5,
    "booking_date": "2025-01-20",
    "start_time": "10:00:00"
  }'
```

## Related Files

- `/app/Http/Requests/Admin/StoreBookingRequest.php`
- `/app/Repositories/Admin/BookingRepository.php`
- `/app/Http/Controllers/Admin/BookingController.php`
- `/app/Services/ScheduleService.php` (used by customer bookings)

## See Also

- Customer booking system: `/app/Repositories/Customer/BookingRepository.php`
- Venue schedules: `QUICKSTART.md` section on scheduling
