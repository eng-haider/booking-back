# Customer Authentication - Independent System ✅

## Overview

Customers are now completely independent from the `users` table. They have their own authentication system using JWT tokens.

## Key Changes

### 1. **Customer Model**

- ✅ No longer depends on `user_id`
- ✅ Implements `JWTSubject` directly
- ✅ Has its own `phone`, `email`, `password` fields
- ✅ Independent authentication

### 2. **Database Schema**

**Customers Table:**

```php
- id
- full_name
- phone (unique)
- email (nullable)
- password (nullable)
- address
- city
- country
- date_of_birth
- gender
- profile_image
- notes
- is_active
- phone_verified_at (timestamp)
- created_at
- updated_at
```

### 3. **Authentication Flow**

#### **Customer Registration**

```http
POST /api/customer/auth/register

Body:
{
    "name": "John Doe",
    "phone": "+9641234567890",
    "email": "john@example.com" (optional)
}

Response:
{
    "success": true,
    "message": "Registration successful. OTP sent to your phone.",
    "data": {
        "customer": {...},
        "otp": {
            "expires_at": "2025-12-09T08:00:00.000000Z",
            "otp": "123456"
        }
    }
}
```

#### **Customer Login**

```http
POST /api/customer/auth/login

Body:
{
    "phone": "+9645555555555"
}

Response:
{
    "success": true,
    "message": "OTP sent successfully",
    "data": {
        "expires_at": "2025-12-09T08:00:00.000000Z",
        "otp": "123456"
    }
}
```

#### **Verify OTP**

```http
POST /api/customer/auth/verify-otp

Body:
{
    "phone": "+9645555555555",
    "code": "123456"
}

Response:
{
    "success": true,
    "message": "Login successful",
    "data": {
        "customer": {
            "id": 1,
            "full_name": "Customer Test User",
            "phone": "+9645555555555",
            "email": "customer@test.com",
            "phone_verified_at": "2025-12-09T07:30:00.000000Z",
            ...
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "bearer",
        "expires_in": 31536000
    }
}
```

### 4. **JWT Token Claims**

Customer JWT tokens include:

```json
{
  "sub": 1, // Customer ID
  "type": "customer",
  "phone": "+9645555555555",
  "email": "customer@test.com",
  "iat": 1733732400,
  "exp": 1765268400
}
```

### 5. **Protected Routes**

All customer routes use the `auth:customer` guard:

```php
Route::middleware('auth:customer')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    // ...
});
```

### 6. **Auth Configuration**

**config/auth.php:**

```php
'guards' => [
    'customer' => [
        'driver' => 'jwt',
        'provider' => 'customers',
    ],
],

'providers' => [
    'customers' => [
        'driver' => 'eloquent',
        'model' => App\Models\Customer::class,
    ],
],
```

### 7. **Getting Authenticated Customer**

In controllers:

```php
// Get authenticated customer
$customer = Auth::guard('customer')->user();

// Or using request
$customer = $request->user('customer');
```

## Test Customer

**Phone:** +9645555555555  
**OTP:** 123456 (fixed for development)  
**Email:** customer@test.com

## Differences from Admin/Provider Auth

| Feature      | Admin/Provider             | Customer      |
| ------------ | -------------------------- | ------------- |
| Base Model   | User                       | Customer      |
| Table        | users                      | customers     |
| Auth Guard   | auth:admin / auth:provider | auth:customer |
| Provider     | users                      | customers     |
| Password     | Required                   | Optional      |
| Login Method | Phone + OTP                | Phone + OTP   |
| Token Type   | JWT                        | JWT           |

## Benefits

1. **Independence**: Customers don't clutter the users table
2. **Flexibility**: Different fields and validation for customers
3. **Security**: Separate authentication system
4. **Scalability**: Can be moved to microservice easily
5. **Clean**: Clear separation of concerns

## API Testing

Use the updated Postman collection with:

1. Call `/api/customer/auth/register` or `/api/customer/auth/login`
2. Get OTP from response (or use 123456 for test user)
3. Call `/api/customer/auth/verify-otp` with phone + code
4. Use returned token in Authorization header: `Bearer <token>`
5. Access protected customer routes

## Production Notes

⚠️ **Before production:**

- Remove OTP from API responses
- Implement real SMS service
- Remove test user fixed OTP codes
- Add rate limiting on OTP endpoints
- Consider adding email verification
- Add password reset functionality
- Implement refresh tokens
