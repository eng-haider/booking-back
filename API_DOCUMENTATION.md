# API Documentation

## Database Schema

### Tables Overview

#### 1. users

| Column            | Type      | Description                     |
| ----------------- | --------- | ------------------------------- |
| id                | bigint    | Primary key                     |
| name              | string    | User's full name                |
| email             | string    | Email address (unique)          |
| phone             | string    | Phone number (unique, nullable) |
| role              | enum      | user, owner, admin              |
| timezone          | string    | User timezone (default: UTC)    |
| password          | string    | Hashed password                 |
| email_verified_at | timestamp | Email verification time         |
| remember_token    | string    | Remember me token               |
| created_at        | timestamp |                                 |
| updated_at        | timestamp |                                 |

#### 2. venue_types

| Column     | Type      | Description                      |
| ---------- | --------- | -------------------------------- |
| id         | bigint    | Primary key                      |
| name       | string    | Type name (e.g., "Sports Field") |
| slug       | string    | URL-friendly slug (unique)       |
| created_at | timestamp |                                  |
| updated_at | timestamp |                                  |

#### 3. venues

| Column         | Type          | Description                     |
| -------------- | ------------- | ------------------------------- |
| id             | bigint        | Primary key                     |
| owner_id       | bigint        | Foreign key to users (nullable) |
| venue_type_id  | bigint        | Foreign key to venue_types      |
| name           | string        | Venue name                      |
| description    | text          | Venue description               |
| address        | string        | Street address                  |
| city           | string        | City name                       |
| lat            | decimal(10,7) | Latitude                        |
| lng            | decimal(10,7) | Longitude                       |
| base_price     | decimal(10,2) | Base price per hour             |
| currency       | string(3)     | Currency code (default: USD)    |
| status         | enum          | active, disabled                |
| buffer_minutes | integer       | Setup/cleanup time (default: 0) |
| timezone       | string        | Venue timezone (default: UTC)   |
| created_at     | timestamp     |                                 |
| updated_at     | timestamp     |                                 |

#### 4. resources

| Column         | Type          | Description                     |
| -------------- | ------------- | ------------------------------- |
| id             | bigint        | Primary key                     |
| venue_id       | bigint        | Foreign key to venues           |
| name           | string        | Resource name (e.g., "Field 1") |
| capacity       | integer       | Maximum capacity                |
| price_per_hour | decimal(10,2) | Hourly rate (nullable)          |
| created_at     | timestamp     |                                 |
| updated_at     | timestamp     |                                 |

#### 5. amenities

| Column     | Type      | Description                |
| ---------- | --------- | -------------------------- |
| id         | bigint    | Primary key                |
| name       | string    | Amenity name               |
| icon       | string    | Icon identifier (nullable) |
| created_at | timestamp |                            |
| updated_at | timestamp |                            |

#### 6. amenity_venue (pivot)

| Column     | Type      | Description              |
| ---------- | --------- | ------------------------ |
| id         | bigint    | Primary key              |
| amenity_id | bigint    | Foreign key to amenities |
| venue_id   | bigint    | Foreign key to venues    |
| created_at | timestamp |                          |
| updated_at | timestamp |                          |

#### 7. photos

| Column     | Type      | Description                         |
| ---------- | --------- | ----------------------------------- |
| id         | bigint    | Primary key                         |
| venue_id   | bigint    | Foreign key to venues               |
| path       | string    | File path                           |
| is_primary | boolean   | Primary photo flag (default: false) |
| created_at | timestamp |                                     |
| updated_at | timestamp |                                     |

#### 8. bookings

| Column           | Type          | Description                              |
| ---------------- | ------------- | ---------------------------------------- |
| id               | bigint        | Primary key                              |
| user_id          | bigint        | Foreign key to users                     |
| venue_id         | bigint        | Foreign key to venues                    |
| resource_id      | bigint        | Foreign key to resources (nullable)      |
| start_datetime   | timestamp     | Booking start time (UTC)                 |
| end_datetime     | timestamp     | Booking end time (UTC)                   |
| duration_minutes | integer       | Duration in minutes                      |
| total_amount     | decimal(10,2) | Total cost                               |
| currency         | string(3)     | Currency code                            |
| status           | enum          | pending, confirmed, cancelled, completed |
| payment_id       | string        | Payment reference (nullable)             |
| created_at       | timestamp     |                                          |
| updated_at       | timestamp     |                                          |

**Indexes:**

- (resource_id, start_datetime, end_datetime)
- start_datetime
- status

#### 9. payments

| Column          | Type          | Description                          |
| --------------- | ------------- | ------------------------------------ |
| id              | bigint        | Primary key                          |
| booking_id      | bigint        | Foreign key to bookings              |
| method          | string        | Payment method                       |
| amount          | decimal(10,2) | Payment amount                       |
| status          | enum          | pending, completed, failed, refunded |
| transaction_ref | string        | Transaction reference                |
| raw_response    | json          | Gateway response                     |
| paid_at         | timestamp     | Payment completion time              |
| created_at      | timestamp     |                                      |
| updated_at      | timestamp     |                                      |

#### 10. reviews

| Column     | Type      | Description           |
| ---------- | --------- | --------------------- |
| id         | bigint    | Primary key           |
| user_id    | bigint    | Foreign key to users  |
| venue_id   | bigint    | Foreign key to venues |
| rating     | tinyint   | Rating (1-5)          |
| comment    | text      | Review comment        |
| created_at | timestamp |                       |
| updated_at | timestamp |                       |

**Unique constraint:** (user_id, venue_id)

#### 11. schedules

| Column      | Type      | Description                         |
| ----------- | --------- | ----------------------------------- |
| id          | bigint    | Primary key                         |
| venue_id    | bigint    | Foreign key to venues               |
| day_of_week | tinyint   | 0=Sunday, 1=Monday, ..., 6=Saturday |
| open_time   | time      | Opening time                        |
| close_time  | time      | Closing time                        |
| is_closed   | boolean   | Closed for the day (default: false) |
| created_at  | timestamp |                                     |
| updated_at  | timestamp |                                     |

**Unique constraint:** (venue_id, day_of_week)

## OTP System (Cache-Based)

OTPs are **stored in cache** (not database) for security:

- **Storage**: Redis/Memcached/File cache
- **Expiry**: 10 minutes (auto-deleted)
- **Max Attempts**: 5 attempts per OTP
- **Security**: Hashed code stored in cache
- **Cache Key Format**: `otp:{phone_number}`

**Cache Data Structure:**

```php
[
    'code' => 'hashed_otp_code',
    'expires_at' => timestamp,
    'attempts' => 0
]
```

## API Endpoints

### Authentication

#### Send OTP

```http
POST /api/auth/login
Content-Type: application/json

{
  "phone": "+1234567890"
}
```

**Response:**

```json
{
  "success": true,
  "message": "OTP sent successfully",
  "data": {
    "expires_at": "2024-12-08T10:10:00.000000Z"
  }
}
```

#### Verify OTP

```http
POST /api/auth/verify-otp
Content-Type: application/json

{
  "phone": "+1234567890",
  "code": "123456",
  "name": "John Doe"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "phone": "+1234567890",
      "role": "user",
      "timezone": "UTC"
    },
    "token": "1|abcdef..."
  }
}
```

#### Get Current User

```http
GET /api/auth/me
Authorization: Bearer {token}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "phone": "+1234567890",
    "role": "user",
    "timezone": "UTC"
  }
}
```

#### Logout

```http
POST /api/auth/logout
Authorization: Bearer {token}
```

**Response:**

```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

## Model Relationships

### User Model

- `venues()` - HasMany: Venues owned by the user
- `bookings()` - HasMany: Bookings made by the user
- `reviews()` - HasMany: Reviews written by the user

### Venue Model

- `owner()` - BelongsTo: User who owns the venue
- `venueType()` - BelongsTo: Type of venue
- `resources()` - HasMany: Resources in the venue
- `photos()` - HasMany: Photos of the venue
- `bookings()` - HasMany: Bookings for the venue
- `reviews()` - HasMany: Reviews for the venue
- `schedules()` - HasMany: Operating schedules
- `amenities()` - BelongsToMany: Amenities available
- `primaryPhoto()` - HasMany: Primary photo

### Resource Model

- `venue()` - BelongsTo: Venue that owns the resource
- `bookings()` - HasMany: Bookings for this resource

### Booking Model

- `user()` - BelongsTo: User who made the booking
- `venue()` - BelongsTo: Venue being booked
- `resource()` - BelongsTo: Specific resource booked
- `payment()` - HasOne: Payment for the booking

### Payment Model

- `booking()` - BelongsTo: Associated booking

### Review Model

- `user()` - BelongsTo: User who wrote the review
- `venue()` - BelongsTo: Venue being reviewed

### Schedule Model

- `venue()` - BelongsTo: Venue with this schedule

### Amenity Model

- `venues()` - BelongsToMany: Venues with this amenity

### VenueType Model

- `venues()` - HasMany: Venues of this type

## Enums

### UserRole (App\Enums\UserRole)

- `USER` = 'user'
- `OWNER` = 'owner'
- `ADMIN` = 'admin'

### VenueStatus (App\Enums\VenueStatus)

- `ACTIVE` = 'active'
- `DISABLED` = 'disabled'

### BookingStatus (App\Enums\BookingStatus)

- `PENDING` = 'pending'
- `CONFIRMED` = 'confirmed'
- `CANCELLED` = 'cancelled'
- `COMPLETED` = 'completed'

### PaymentStatus (App\Enums\PaymentStatus)

- `PENDING` = 'pending'
- `COMPLETED` = 'completed'
- `FAILED` = 'failed'
- `REFUNDED` = 'refunded'

## Services

### OtpService (App\Services\OtpService)

**Cache-based OTP system for enhanced security**

#### Constants

- `OTP_EXPIRY_MINUTES` = 10 minutes
- `MAX_ATTEMPTS` = 5 attempts per OTP

#### Methods

**generateOtp(string $phone): array**

- Generates a 6-digit OTP code
- Stores hashed code in cache with 10-minute expiration
- Logs the code (for development)
- Returns array: `['expires_at' => Carbon]`

**verifyOtp(string $phone, string $code): bool**

- Verifies OTP code against cache
- Checks expiration (auto-deleted after 10 minutes)
- Tracks and limits verification attempts (max 5)
- Deletes OTP from cache on successful verification
- Returns true/false

**getOrCreateUser(string $phone, ?string $name = null): User**

- Finds existing user by phone
- Creates new user if not found
- Generates temporary email
- Returns User model

**getOtpCacheKey(string $phone): string** (private)

- Generates cache key: `otp:{phone}`
- Used internally for cache operations

#### Security Features

✅ **No database storage** - OTPs stored only in cache  
✅ **Auto-expiry** - Automatically deleted after 10 minutes  
✅ **Hashed codes** - OTP codes are hashed before storage  
✅ **Attempt limiting** - Maximum 5 verification attempts  
✅ **Single use** - OTP deleted after successful verification

## Next Steps

To complete the booking system, you should implement:

1. **Venue Management**

   - CRUD operations for venues
   - File upload for photos
   - Amenity assignment

2. **Booking Management**

   - Create booking endpoint
   - Check availability
   - Handle conflicts
   - Calculate pricing

3. **Payment Integration**

   - Payment gateway integration
   - Webhook handling
   - Refund processing

4. **Search & Filtering**

   - Laravel Query Builder integration
   - Location-based search
   - Price range filtering
   - Availability filtering

5. **Notifications**

   - Email notifications
   - SMS notifications
   - Push notifications

6. **Admin Panel**
   - Dashboard
   - Analytics
   - User management
   - Venue approval
