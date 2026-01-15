# Offers API - Example Responses

## 1. Create Offer - Success

**Request:**

```json
POST /api/provider/offers
{
  "venue_id": 5,
  "title": "Weekend Special - 20% Off",
  "description": "Get 20% off on weekend bookings",
  "discount_type": "percentage",
  "discount_value": 20,
  "min_booking_hours": 2,
  "max_uses": 100,
  "start_date": "2026-02-01",
  "end_date": "2026-02-28",
  "is_active": true,
  "terms_and_conditions": "Valid for Saturday and Sunday only"
}
```

**Response (201):**

```json
{
  "success": true,
  "message": "Offer created successfully",
  "data": {
    "id": 1,
    "venue_id": 5,
    "title": "Weekend Special - 20% Off",
    "description": "Get 20% off on weekend bookings",
    "discount_type": "percentage",
    "discount_value": "20.00",
    "min_booking_hours": 2,
    "max_uses": 100,
    "used_count": 0,
    "start_date": "2026-02-01 00:00:00",
    "end_date": "2026-02-28 23:59:59",
    "is_active": true,
    "terms_and_conditions": "Valid for Saturday and Sunday only",
    "created_at": "2026-01-15 10:30:00",
    "updated_at": "2026-01-15 10:30:00",
    "venue": {
      "id": 5,
      "name": "Grand Conference Hall",
      "base_price": "500.00",
      "currency": "USD"
    }
  }
}
```

## 2. List All Offers - Paginated

**Request:**

```
GET /api/provider/offers?filter[is_active]=true&include=venue&per_page=2
```

**Response (200):**

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "venue_id": 5,
        "title": "Weekend Special - 20% Off",
        "description": "Get 20% off on weekend bookings",
        "discount_type": "percentage",
        "discount_value": "20.00",
        "min_booking_hours": 2,
        "max_uses": 100,
        "used_count": 15,
        "start_date": "2026-02-01 00:00:00",
        "end_date": "2026-02-28 23:59:59",
        "is_active": true,
        "terms_and_conditions": "Valid for Saturday and Sunday only",
        "created_at": "2026-01-15 10:30:00",
        "updated_at": "2026-01-15 10:30:00",
        "venue": {
          "id": 5,
          "name": "Grand Conference Hall",
          "base_price": "500.00",
          "currency": "USD"
        }
      },
      {
        "id": 2,
        "venue_id": 3,
        "title": "Early Bird - $100 Off",
        "description": "Book 2 weeks in advance and save $100",
        "discount_type": "fixed",
        "discount_value": "100.00",
        "min_booking_hours": 4,
        "max_uses": 50,
        "used_count": 8,
        "start_date": "2026-01-20 00:00:00",
        "end_date": "2026-12-31 23:59:59",
        "is_active": true,
        "terms_and_conditions": "Must book at least 14 days in advance",
        "created_at": "2026-01-10 09:15:00",
        "updated_at": "2026-01-14 14:22:00",
        "venue": {
          "id": 3,
          "name": "Rooftop Garden Venue",
          "base_price": "750.00",
          "currency": "USD"
        }
      }
    ],
    "first_page_url": "http://localhost/api/provider/offers?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "http://localhost/api/provider/offers?page=5",
    "next_page_url": "http://localhost/api/provider/offers?page=2",
    "path": "http://localhost/api/provider/offers",
    "per_page": 2,
    "prev_page_url": null,
    "to": 2,
    "total": 10
  }
}
```

## 3. Get Single Offer

**Request:**

```
GET /api/provider/offers/1?include=venue
```

**Response (200):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "venue_id": 5,
    "title": "Weekend Special - 20% Off",
    "description": "Get 20% off on weekend bookings",
    "discount_type": "percentage",
    "discount_value": "20.00",
    "min_booking_hours": 2,
    "max_uses": 100,
    "used_count": 15,
    "start_date": "2026-02-01 00:00:00",
    "end_date": "2026-02-28 23:59:59",
    "is_active": true,
    "terms_and_conditions": "Valid for Saturday and Sunday only",
    "created_at": "2026-01-15 10:30:00",
    "updated_at": "2026-01-15 10:30:00",
    "venue": {
      "id": 5,
      "name": "Grand Conference Hall",
      "base_price": "500.00",
      "currency": "USD"
    }
  }
}
```

## 4. Update Offer

**Request:**

```json
PUT /api/provider/offers/1
{
  "discount_value": 25,
  "max_uses": 150
}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Offer updated successfully",
  "data": {
    "id": 1,
    "venue_id": 5,
    "title": "Weekend Special - 20% Off",
    "description": "Get 20% off on weekend bookings",
    "discount_type": "percentage",
    "discount_value": "25.00",
    "min_booking_hours": 2,
    "max_uses": 150,
    "used_count": 15,
    "start_date": "2026-02-01 00:00:00",
    "end_date": "2026-02-28 23:59:59",
    "is_active": true,
    "terms_and_conditions": "Valid for Saturday and Sunday only",
    "created_at": "2026-01-15 10:30:00",
    "updated_at": "2026-01-15 11:45:00",
    "venue": {
      "id": 5,
      "name": "Grand Conference Hall",
      "base_price": "500.00",
      "currency": "USD"
    }
  }
}
```

## 5. Toggle Active Status

**Request:**

```
PATCH /api/provider/offers/1/toggle-active
```

**Response (200):**

```json
{
  "success": true,
  "message": "Offer status updated successfully",
  "data": {
    "id": 1,
    "venue_id": 5,
    "title": "Weekend Special - 20% Off",
    "description": "Get 20% off on weekend bookings",
    "discount_type": "percentage",
    "discount_value": "25.00",
    "min_booking_hours": 2,
    "max_uses": 150,
    "used_count": 15,
    "start_date": "2026-02-01 00:00:00",
    "end_date": "2026-02-28 23:59:59",
    "is_active": false,
    "terms_and_conditions": "Valid for Saturday and Sunday only",
    "created_at": "2026-01-15 10:30:00",
    "updated_at": "2026-01-15 12:00:00"
  }
}
```

## 6. Delete Offer

**Request:**

```
DELETE /api/provider/offers/1
```

**Response (200):**

```json
{
  "success": true,
  "message": "Offer deleted successfully"
}
```

## 7. Get Statistics

**Request:**

```
GET /api/provider/offers/statistics
```

**Response (200):**

```json
{
  "success": true,
  "data": {
    "total_offers": 15,
    "active_offers": 10,
    "expired_offers": 3,
    "upcoming_offers": 2,
    "total_uses": 234
  }
}
```

## 8. Get Venue Offers

**Request:**

```
GET /api/provider/offers/venue/5
```

**Response (200):**

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "venue_id": 5,
        "title": "Weekend Special - 20% Off",
        "description": "Get 20% off on weekend bookings",
        "discount_type": "percentage",
        "discount_value": "20.00",
        "min_booking_hours": 2,
        "max_uses": 100,
        "used_count": 15,
        "start_date": "2026-02-01 00:00:00",
        "end_date": "2026-02-28 23:59:59",
        "is_active": true,
        "terms_and_conditions": "Valid for Saturday and Sunday only",
        "created_at": "2026-01-15 10:30:00",
        "updated_at": "2026-01-15 10:30:00"
      },
      {
        "id": 5,
        "venue_id": 5,
        "title": "Summer Sale - 30% Off",
        "description": "Huge summer discount",
        "discount_type": "percentage",
        "discount_value": "30.00",
        "min_booking_hours": null,
        "max_uses": 200,
        "used_count": 45,
        "start_date": "2026-06-01 00:00:00",
        "end_date": "2026-08-31 23:59:59",
        "is_active": true,
        "terms_and_conditions": "Valid all summer",
        "created_at": "2026-01-10 15:20:00",
        "updated_at": "2026-01-10 15:20:00"
      }
    ],
    "total": 2
  }
}
```

## 9. Get Active Venue Offers

**Request:**

```
GET /api/provider/offers/venue/5/active
```

**Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "venue_id": 5,
      "title": "Weekend Special - 20% Off",
      "description": "Get 20% off on weekend bookings",
      "discount_type": "percentage",
      "discount_value": "20.00",
      "min_booking_hours": 2,
      "max_uses": 100,
      "used_count": 15,
      "start_date": "2026-02-01 00:00:00",
      "end_date": "2026-02-28 23:59:59",
      "is_active": true,
      "terms_and_conditions": "Valid for Saturday and Sunday only",
      "created_at": "2026-01-15 10:30:00",
      "updated_at": "2026-01-15 10:30:00"
    }
  ]
}
```

## Error Responses

### Validation Error (422)

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "discount_value": [
      "The discount value cannot exceed 100% for percentage discounts."
    ],
    "end_date": ["The end date must be after the start date."]
  }
}
```

### Not Found (404)

```json
{
  "success": false,
  "message": "Offer not found"
}
```

### Unauthorized Venue (404)

```json
{
  "success": false,
  "message": "Venue not found or does not belong to you"
}
```

### Unauthorized (401)

```json
{
  "success": false,
  "message": "Unauthenticated."
}
```
