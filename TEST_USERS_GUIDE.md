# Test Users with Full Permissions

## ✅ All Test Users Created Successfully!

All test users have been created with **FULL PERMISSIONS** assigned to their respective guards.

---

## 1️⃣ Admin User

**Authentication:**

- **Phone:** `+9641234567890`
- **Email:** `admin@test.com`
- **Password:** `password`
- **OTP Code:** `123456`

**Permissions:**

- ✅ 49 admin permissions
- ✅ Admin role (admin guard)
- ✅ Full system access

**API Endpoint:**

- Login: `POST /api/admin/login`

---

## 2️⃣ Provider User

**Authentication:**

- **Phone:** `+9649876543210`
- **Email:** `provider@test.com`
- **Password:** `password`
- **OTP Code:** `123456`

**Provider Profile:**

- **Name:** Test Sports Venue
- **Slug:** test-sports-venue
- **Status:** Active
- **License:** TEST-LIC-001
- **Address:** 123 Test Street, Al-Mansour

**Permissions:**

- ✅ 49 provider permissions
- ✅ Owner role (provider guard)
- ✅ Full provider access (venues, bookings, etc.)

**API Endpoint:**

- Login: `POST /api/provider/login`

---

## 3️⃣ Customer User

**Authentication:**

- **Phone:** `+9645555555555`
- **Email:** `customer@test.com`
- **Password:** `password`
- **OTP Code:** `123456`

**Customer Profile:**

- **Full Name:** Customer Test User
- **Status:** Active
- **Verified:** Yes

**Permissions:**

- ✅ 49 customer permissions
- ✅ Customer role (customer guard)
- ✅ Full customer access (bookings, reviews, etc.)

**API Endpoint:**

- Login: `POST /api/customer/login`

---

## 🔑 Quick Test Login

### Provider Login Example:

```bash
curl -X POST http://localhost:8000/api/provider/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+9649876543210",
    "otp": "123456"
  }'
```

### Customer Login Example:

```bash
curl -X POST http://localhost:8000/api/customer/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+9645555555555",
    "otp": "123456"
  }'
```

### Admin Login Example:

```bash
curl -X POST http://localhost:8000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+9641234567890",
    "otp": "123456"
  }'
```

---

## 📋 Permission Details

Each test user has **49 permissions** including:

### Venue Management

- view venues, create venues, edit venues, delete venues
- manage own venues, search venues, feature venues

### Booking Management

- view bookings, create bookings, edit bookings, delete bookings
- manage own bookings, confirm bookings, cancel bookings, complete bookings
- search bookings, check availability

### Customer Management

- view customers, create customers, edit customers, delete customers
- manage customer status, search customers, verify customers

### Provider Management

- view providers, create providers, edit providers, delete providers
- manage provider status, manage own provider

### Category Management

- view categories, create categories, edit categories, delete categories
- reorder categories

### Review Management

- view reviews, create reviews, edit reviews, delete reviews
- manage own reviews

### Payment Management

- view payments, process payments, refund payments

### Statistics & Reports

- view statistics, view reports, export data

### System Settings

- manage settings, manage roles, manage permissions

---

## 🔄 Re-seeding Test Users

To recreate test users with all permissions:

```bash
# Seed roles and permissions first
php artisan db:seed --class=RolesAndPermissionsSeeder

# Seed governorates (required for provider)
php artisan db:seed --class=GovernorateSeeder

# Create test users with all permissions
php artisan db:seed --class=TestUserSeeder
```

---

## ✅ Status

- ✅ Admin user created with full admin permissions
- ✅ Provider user created with full provider permissions
- ✅ Provider profile created and active
- ✅ Customer user created with full customer permissions
- ✅ All users verified and active
- ✅ Ready for API testing

**Last Updated:** December 13, 2025
