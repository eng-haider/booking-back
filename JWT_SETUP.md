# JWT Authentication Setup Complete ✅

## Overview

Successfully migrated from Laravel Sanctum to JWT (JSON Web Token) authentication using `tymon/jwt-auth` package.

## What Was Done

### 1. **Installed JWT Package**

```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

### 2. **Updated User Model**

- Implemented `JWTSubject` interface
- Added required JWT methods:
  - `getJWTIdentifier()` - Returns user ID
  - `getJWTCustomClaims()` - Returns custom claims (role, email, phone)
- Removed Laravel Sanctum's `HasApiTokens` trait

### 3. **Updated Auth Configuration**

File: `config/auth.php`

- Added JWT guards for:
  - `api` (default JWT guard)
  - `admin` (admin authentication)
  - `provider` (provider/owner authentication)
  - `customer` (customer authentication)

### 4. **Updated All Auth Controllers**

Updated token generation and logout methods in:

- `app/Http/Controllers/Admin/AuthController.php`
- `app/Http/Controllers/Provider/AuthController.php`
- `app/Http/Controllers/Customer/AuthController.php`

**Changes:**

- **Before:** `$user->createToken('admin-token')->plainTextToken`
- **After:** `JWTAuth::fromUser($user)`

Response now includes:

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 31536000
}
```

### 5. **Updated Route Middleware**

Changed authentication guards in route files:

- `routes/admin.php`: `auth:sanctum` → `auth:admin`
- `routes/provider.php`: `auth:sanctum` → `auth:provider`
- `routes/customer.php`: `auth:sanctum` → `auth:customer`

### 6. **Fixed Database Seeding**

Updated `TestUserSeeder.php` with correct field names:

- Provider: `business_name` → `name`, `business_license` → `license_number`
- Customer: `name` → `full_name`
- User role: `customer` → `user`
- Provider status: `approved` → `active`

### 7. **Database Migrations**

All migrations completed successfully:

- ✅ Countries table (15 countries seeded)
- ✅ Statuses table (7 booking statuses seeded)
- ✅ Fixed bookings table (timestamp fields + status_id FK)
- ✅ Venues table (includes country_id FK)
- ✅ All other tables

### 8. **Test Users Created**

Three test users with fixed OTP code (123456):

- **Admin**: +9641234567890
- **Provider**: +9649876543210
- **Customer**: +9645555555555

## How to Use JWT Authentication

### 1. **Login Flow**

#### Step 1: Request OTP

```bash
POST /api/admin/auth/login
POST /api/provider/auth/login
POST /api/customer/auth/login

Body:
{
    "phone": "+9641234567890"
}
```

#### Step 2: Verify OTP

```bash
POST /api/admin/auth/verify-otp
POST /api/provider/auth/verify-otp
POST /api/customer/auth/verify-otp

Body:
{
    "phone": "+9641234567890",
    "otp": "123456"
}

Response:
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {...},
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "bearer",
        "expires_in": 31536000
    }
}
```

### 2. **Making Authenticated Requests**

Include the JWT token in the Authorization header:

```bash
GET /api/admin/auth/me
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

### 3. **Logout**

```bash
POST /api/admin/auth/logout
POST /api/provider/auth/logout
POST /api/customer/auth/logout

Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

This invalidates the current token.

## Configuration

### JWT Settings

File: `config/jwt.php`

Key settings from `.env`:

```env
JWT_SECRET=HEfqQ9xlBMAvcoAdzz1M0SwTM19bMLsrQG0eomqnRL7CaWzzbnh3jnWO3jvPb71X
JWT_TTL=5256000  # Token lifetime in minutes (~10 years for development)
```

## Token Structure

The JWT token contains:

- **Header**: Token type and algorithm
- **Payload**:
  - `sub`: User ID
  - `role`: User role (admin, owner, user)
  - `email`: User email
  - `phone`: User phone
  - `iat`: Issued at timestamp
  - `exp`: Expiration timestamp
- **Signature**: Cryptographic signature

## Benefits of JWT

1. **Stateless**: No need to store sessions on server
2. **Scalable**: Works across multiple servers
3. **Secure**: Cryptographically signed tokens
4. **Self-contained**: Token includes user information
5. **Cross-domain**: Works with CORS and microservices

## Testing with Postman

1. Import `Booking_API.postman_collection.json`
2. Login with test credentials (OTP: 123456)
3. Copy the `token` from response
4. Add to Authorization header as `Bearer <token>`
5. Make authenticated requests

## Important Notes

⚠️ **Production Checklist:**

- [ ] Change `JWT_TTL` to reasonable value (e.g., 60 minutes)
- [ ] Remove OTP from login response
- [ ] Enable actual SMS OTP service
- [ ] Remove test user fixed OTP codes
- [ ] Use strong `JWT_SECRET` (already set)
- [ ] Enable token refresh mechanism
- [ ] Implement token blacklist for logout

## Next Steps

✅ All migrations completed
✅ JWT authentication implemented
✅ Test users created
✅ Server running on http://127.0.0.1:8001

**Ready to test the complete API!**
