# Venue Offers Feature

This feature allows providers to create and manage special offers/discounts for their venues.

## Database Migration

Run the migration to create the offers table:

```bash
php artisan migrate
```

## Features

### Offer Types

- **Percentage Discount**: e.g., 20% off
- **Fixed Amount Discount**: e.g., $50 off

### Offer Properties

- **Title & Description**: Marketing information
- **Discount Type & Value**: Percentage or fixed amount
- **Min Booking Hours**: Minimum booking duration to qualify
- **Max Uses**: Limit total usage (optional)
- **Date Range**: Start and end dates for the offer
- **Active Status**: Enable/disable offers
- **Terms & Conditions**: Additional restrictions

## API Endpoints

All endpoints are protected and require provider authentication.

### 1. List All Offers

```
GET /api/provider/offers
```

**Query Parameters:**

- `filter[venue_id]` - Filter by venue ID
- `filter[is_active]` - Filter by active status (true/false)
- `filter[active]` - Get only currently active offers
- `filter[available]` - Get available offers (active and not maxed out)
- `sort` - Sort by field (e.g., `-created_at`, `title`, `discount_value`)
- `include` - Include relationships (e.g., `venue`)
- `per_page` - Results per page (default: 15)

**Example:**

```bash
curl -X GET "https://api.example.com/api/provider/offers?filter[is_active]=true&include=venue" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response:**

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "venue_id": 5,
        "title": "Summer Special - 20% Off",
        "description": "Get 20% off on all bookings during summer",
        "discount_type": "percentage",
        "discount_value": "20.00",
        "min_booking_hours": 2,
        "max_uses": 100,
        "used_count": 15,
        "start_date": "2026-06-01 00:00:00",
        "end_date": "2026-08-31 23:59:59",
        "is_active": true,
        "terms_and_conditions": "Valid for new bookings only",
        "venue": {
          "id": 5,
          "name": "Grand Hall",
          "base_price": "500.00",
          "currency": "USD"
        }
      }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

### 2. Create New Offer

```
POST /api/provider/offers
```

**Request Body:**

```json
{
  "venue_id": 5,
  "title": "Weekend Special",
  "description": "Get 15% off on weekend bookings",
  "discount_type": "percentage",
  "discount_value": 15,
  "min_booking_hours": 3,
  "max_uses": 50,
  "start_date": "2026-02-01",
  "end_date": "2026-02-28",
  "is_active": true,
  "terms_and_conditions": "Valid only for Saturday and Sunday bookings"
}
```

**Validation Rules:**

- `venue_id`: Required, must exist and belong to the provider
- `title`: Required, max 255 characters
- `description`: Optional
- `discount_type`: Required, either "percentage" or "fixed"
- `discount_value`: Required, numeric, min 0 (max 100 for percentage)
- `min_booking_hours`: Optional, integer, min 1
- `max_uses`: Optional, integer, min 1
- `start_date`: Required, must be today or future date
- `end_date`: Required, must be after start_date
- `is_active`: Optional, boolean (default: true)
- `terms_and_conditions`: Optional

**Example:**

```bash
curl -X POST "https://api.example.com/api/provider/offers" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "venue_id": 5,
    "title": "Weekend Special",
    "discount_type": "percentage",
    "discount_value": 15,
    "start_date": "2026-02-01",
    "end_date": "2026-02-28"
  }'
```

### 3. Get Single Offer

```
GET /api/provider/offers/{id}
```

**Example:**

```bash
curl -X GET "https://api.example.com/api/provider/offers/1?include=venue" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Update Offer

```
PUT /api/provider/offers/{id}
```

**Request Body:** (all fields are optional)

```json
{
  "title": "Updated Weekend Special",
  "discount_value": 20,
  "is_active": false
}
```

**Example:**

```bash
curl -X PUT "https://api.example.com/api/provider/offers/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "discount_value": 20,
    "is_active": true
  }'
```

### 5. Delete Offer

```
DELETE /api/provider/offers/{id}
```

**Example:**

```bash
curl -X DELETE "https://api.example.com/api/provider/offers/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 6. Toggle Offer Active Status

```
PATCH /api/provider/offers/{id}/toggle-active
```

**Example:**

```bash
curl -X PATCH "https://api.example.com/api/provider/offers/1/toggle-active" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 7. Get Offers Statistics

```
GET /api/provider/offers/statistics
```

**Response:**

```json
{
  "success": true,
  "data": {
    "total_offers": 10,
    "active_offers": 7,
    "expired_offers": 2,
    "upcoming_offers": 1,
    "total_uses": 145
  }
}
```

### 8. Get Venue Offers

```
GET /api/provider/offers/venue/{venueId}
```

**Example:**

```bash
curl -X GET "https://api.example.com/api/provider/offers/venue/5" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 9. Get Active Venue Offers

```
GET /api/provider/offers/venue/{venueId}/active
```

Returns only currently active offers for a specific venue.

## Model Methods

### Offer Model

**Scopes:**

- `active()` - Get offers that are active and within date range
- `available()` - Get offers that are active and haven't reached max uses

**Methods:**

- `isValid()` - Check if offer is currently valid
- `calculateDiscount($price)` - Calculate discount amount for a given price
- `incrementUsedCount()` - Increment the usage counter

**Example Usage:**

```php
// Get active offers for a venue
$offers = Offer::where('venue_id', $venueId)->active()->get();

// Check if offer is valid
if ($offer->isValid()) {
    $discountAmount = $offer->calculateDiscount($bookingPrice);
}

// Apply offer to booking
$offer->incrementUsedCount();
```

## Integration with Bookings

To integrate offers with the booking system:

1. **Add offer_id to bookings table** (optional future enhancement):

```php
Schema::table('bookings', function (Blueprint $table) {
    $table->foreignId('offer_id')->nullable()->constrained('offers')->nullOnDelete();
    $table->decimal('discount_amount', 10, 2)->default(0);
});
```

2. **Calculate discount during booking**:

```php
$offer = Offer::find($request->offer_id);
if ($offer && $offer->isValid()) {
    $discountAmount = $offer->calculateDiscount($totalPrice);
    $finalPrice = $totalPrice - $discountAmount;
    $offer->incrementUsedCount();
}
```

## Frontend Integration Tips

### Display Offers

```javascript
// Fetch active offers for a venue
fetch("/api/provider/offers/venue/5/active", {
  headers: {
    Authorization: "Bearer " + token,
  },
})
  .then((response) => response.json())
  .then((data) => {
    // Display offers in UI
    data.data.forEach((offer) => {
      const displayText =
        offer.discount_type === "percentage"
          ? `${offer.discount_value}% OFF`
          : `$${offer.discount_value} OFF`;
      console.log(`${offer.title}: ${displayText}`);
    });
  });
```

### Create Offer Form

```html
<form id="offerForm">
  <select name="venue_id" required>
    <option value="">Select Venue</option>
  </select>

  <input type="text" name="title" placeholder="Offer Title" required />
  <textarea name="description" placeholder="Description"></textarea>

  <select name="discount_type" required>
    <option value="percentage">Percentage (%)</option>
    <option value="fixed">Fixed Amount</option>
  </select>

  <input
    type="number"
    name="discount_value"
    placeholder="Discount Value"
    required
  />
  <input
    type="number"
    name="min_booking_hours"
    placeholder="Min Booking Hours"
  />
  <input type="number" name="max_uses" placeholder="Max Uses" />

  <input type="datetime-local" name="start_date" required />
  <input type="datetime-local" name="end_date" required />

  <textarea
    name="terms_and_conditions"
    placeholder="Terms and Conditions"
  ></textarea>

  <button type="submit">Create Offer</button>
</form>
```

## Security Notes

- All endpoints require provider authentication
- Providers can only manage offers for their own venues
- The system validates that venues belong to the authenticated provider
- Start dates must be today or in the future for new offers
- End dates must be after start dates
- Percentage discounts are capped at 100%

## Future Enhancements

Potential features to add:

- Promo codes for offers
- Day-of-week restrictions
- Time-of-day restrictions
- Multiple venue offers
- Automatic offer application
- Customer-specific offers
- Offer analytics dashboard
- Email notifications for new offers
