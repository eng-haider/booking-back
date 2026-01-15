# Quick Reference - Offers API

## Authentication

All endpoints require Bearer token:

```
Authorization: Bearer YOUR_PROVIDER_TOKEN
```

## Base URL

```
/api/provider/offers
```

## Quick Examples

### Create Percentage Offer

```bash
curl -X POST https://your-domain.com/api/provider/offers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "venue_id": 1,
    "title": "Summer Sale - 25% Off",
    "description": "Get 25% off all summer bookings",
    "discount_type": "percentage",
    "discount_value": 25,
    "start_date": "2026-06-01",
    "end_date": "2026-08-31"
  }'
```

### Create Fixed Amount Offer

```bash
curl -X POST https://your-domain.com/api/provider/offers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "venue_id": 1,
    "title": "$100 Off Special",
    "discount_type": "fixed",
    "discount_value": 100,
    "min_booking_hours": 4,
    "max_uses": 50,
    "start_date": "2026-02-01",
    "end_date": "2026-02-29"
  }'
```

### List Active Offers

```bash
curl -X GET "https://your-domain.com/api/provider/offers?filter[active]=true&include=venue" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Update Offer

```bash
curl -X PUT https://your-domain.com/api/provider/offers/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "discount_value": 30,
    "is_active": true
  }'
```

### Toggle Active Status

```bash
curl -X PATCH https://your-domain.com/api/provider/offers/1/toggle-active \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Get Statistics

```bash
curl -X GET https://your-domain.com/api/provider/offers/statistics \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Get Venue Offers

```bash
curl -X GET https://your-domain.com/api/provider/offers/venue/1/active \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Delete Offer

```bash
curl -X DELETE https://your-domain.com/api/provider/offers/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## PHP Usage Examples

### Check if Offer is Valid

```php
$offer = Offer::find($offerId);
if ($offer->isValid()) {
    echo "Offer is valid!";
}
```

### Calculate Discount

```php
$offer = Offer::find($offerId);
$originalPrice = 500.00;
$discountAmount = $offer->calculateDiscount($originalPrice);
$finalPrice = $originalPrice - $discountAmount;

echo "Original: $$originalPrice\n";
echo "Discount: $$discountAmount\n";
echo "Final: $$finalPrice\n";
```

### Get Active Offers for Venue

```php
$offers = Offer::where('venue_id', $venueId)
    ->active()
    ->get();

foreach ($offers as $offer) {
    echo $offer->title . "\n";
}
```

### Apply Offer to Booking

```php
$offer = Offer::find($offerId);
if ($offer && $offer->isValid()) {
    $discountAmount = $offer->calculateDiscount($bookingPrice);
    $finalPrice = $bookingPrice - $discountAmount;

    // Increment usage counter
    $offer->incrementUsedCount();

    // Save booking with offer details
    $booking->offer_id = $offer->id;
    $booking->discount_amount = $discountAmount;
    $booking->total_price = $finalPrice;
    $booking->save();
}
```

## Common Filters

```
?filter[venue_id]=1          # Specific venue
?filter[is_active]=true       # Active offers only
?filter[active]=true          # Currently active (date range + status)
?filter[available]=true       # Available (active + not maxed out)
?sort=-created_at            # Newest first
?sort=discount_value         # Lowest discount first
?include=venue               # Include venue details
?per_page=20                 # 20 results per page
```

## Response Structure

### Success Response

```json
{
  "success": true,
  "data": { ... },
  "message": "..." // for create/update/delete
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... } // validation errors
}
```

## Status Codes

- 200: Success
- 201: Created
- 404: Not Found
- 422: Validation Error
- 401: Unauthorized
