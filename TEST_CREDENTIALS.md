# Test User Credentials

This document contains test user credentials for development and testing purposes.

## Test Users

### Admin User

- **Email:** admin@test.com
- **Phone:** +9641234567890
- **OTP Code:** 123456
- **Role:** Admin

### Provider User

- **Email:** provider@test.com
- **Phone:** +9649876543210
- **OTP Code:** 123456
- **Role:** Provider (Owner)
- **Business:** Test Sports Venue
- **Status:** Approved

### Customer User

- **Email:** customer@test.com
- **Phone:** +9645555555555
- **OTP Code:** 123456
- **Role:** Customer
- **Status:** Email Verified

## How to Create Test Users

Run the following command to seed the test users:

```bash
php artisan db:seed --class=TestUserSeeder
```

## OTP Service

The OTP service is configured to automatically use the code `123456` for the test phone numbers listed above. For any other phone numbers, a random 6-digit OTP will be generated.

## Login Flow

### Admin Login

1. POST `/api/admin/auth/login`

   ```json
   {
     "email": "admin@test.com"
   }
   ```

2. POST `/api/admin/auth/verify-otp`
   ```json
   {
     "email": "admin@test.com",
     "otp": "123456"
   }
   ```

### Provider Login

1. POST `/api/provider/auth/login`

   ```json
   {
     "email": "provider@test.com"
   }
   ```

2. POST `/api/provider/auth/verify-otp`
   ```json
   {
     "email": "provider@test.com",
     "otp": "123456"
   }
   ```

### Customer Login

1. POST `/api/customer/auth/login`

   ```json
   {
     "phone": "+9645555555555"
   }
   ```

2. POST `/api/customer/auth/verify-otp`
   ```json
   {
     "phone": "+9645555555555",
     "code": "123456"
   }
   ```

## Security Note

⚠️ **Important:** These test credentials should only be used in development environments. Make sure to disable or remove them in production!
