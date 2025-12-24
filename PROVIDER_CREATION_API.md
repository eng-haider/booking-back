# Provider Creation API

## Overview

The admin can now create both a user account and provider profile in a single API call.

## Endpoint

```
POST /admin/providers
```

## Authentication

Requires JWT token with `admin` role in header:

```
Authorization: Bearer {admin_jwt_token}
```

## Request Body

### User Fields (Required)

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "07912345678",
  "password": "securepassword123",
  "password_confirmation": "securepassword123"
}
```

### Provider Fields (Optional)

```json
{
  "provider_name": "John's Venue Company",
  "slug": "johns-venue-company",
  "description": "Premium venue provider in Baghdad",
  "provider_email": "contact@johnscompany.com",
  "provider_phone": "07987654321",
  "address": "123 Main Street, Baghdad",
  "governorate_id": 1,
  "lat": 33.3152,
  "lng": 44.3661,
  "website": "https://johnscompany.com",
  "logo": "path/to/logo.png",
  "license_number": "LIC123456",
  "status": "active",
  "settings": {
    "notification_enabled": true,
    "auto_approve_bookings": false
  }
}
```

## Field Descriptions

### User Fields

- **name** (required): Full name of the user
- **email** (required): Unique email for login (must not exist in users table)
- **phone** (required): Iraqi phone number format (07XXXXXXXXX), must be unique
- **password** (required): Minimum 8 characters
- **password_confirmation** (required): Must match password

### Provider Fields

- **provider_name** (optional): Company/provider name. If not provided, uses `name`
- **slug** (optional): URL-friendly identifier, must be unique and lowercase-with-hyphens
- **description** (optional): Description of the provider/company
- **provider_email** (optional): Business email. If not provided, uses `email`
- **provider_phone** (optional): Business phone number
- **address** (optional): Physical address
- **governorate_id** (optional): Reference to governorate (must exist in governorates table)
- **lat** (optional): Latitude coordinate
- **lng** (optional): Longitude coordinate
- **website** (optional): Valid URL
- **logo** (optional): Path to logo image
- **license_number** (optional): Business license number
- **status** (optional): One of: `active`, `inactive`, `suspended` (default: `active`)
- **settings** (optional): JSON object with custom settings

## Example Request

```bash
curl -X POST http://localhost:8000/api/admin/providers \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ahmed Hassan",
    "email": "ahmed.hassan@example.com",
    "phone": "07901234567",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "provider_name": "Hassan Venues LLC",
    "slug": "hassan-venues",
    "description": "Top-rated event venues in Baghdad",
    "provider_email": "info@hassanvenues.com",
    "address": "Al-Karrada District, Baghdad",
    "governorate_id": 1,
    "status": "active"
  }'
```

## Response

### Success (201 Created)

```json
{
  "success": true,
  "message": "Provider created successfully",
  "data": {
    "id": 1,
    "user_id": 15,
    "name": "Hassan Venues LLC",
    "slug": "hassan-venues",
    "description": "Top-rated event venues in Baghdad",
    "email": "info@hassanvenues.com",
    "phone": "07901234567",
    "address": "Al-Karrada District, Baghdad",
    "governorate_id": 1,
    "lat": null,
    "lng": null,
    "website": null,
    "logo": null,
    "license_number": null,
    "status": "active",
    "settings": null,
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z",
    "user": {
      "id": 15,
      "name": "Ahmed Hassan",
      "email": "ahmed.hassan@example.com",
      "phone": "07901234567",
      "role": "owner",
      "created_at": "2025-01-15T10:30:00.000000Z"
    }
  }
}
```

### Error (422 Validation Error)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "phone": ["The phone has already been taken."]
  }
}
```

## What Happens Behind the Scenes

1. **Transaction Started**: All operations wrapped in database transaction
2. **User Created**:
   - User account created with provided credentials
   - Password automatically hashed with bcrypt
   - Role set to `owner`
3. **Role Assigned**: Spatie permission role `owner` assigned to user
4. **Provider Created**:
   - Provider record created linked to new user
   - Uses `provider_name` if provided, otherwise uses `name`
   - Uses `provider_email` if provided, otherwise uses `email`
   - Uses `provider_phone` if provided, otherwise uses `phone`
5. **Response**: Returns provider with embedded user data
6. **Rollback on Error**: If any step fails, all changes are rolled back

## Notes

- User and provider are created atomically (all or nothing)
- The created user can immediately login with email/password
- User will have `owner` role and necessary permissions
- Provider status defaults to `active` if not specified
- Phone numbers must follow Iraqi format: `07XXXXXXXXX` (07 followed by 9 digits)
- Email and phone must be unique across all users
- Slug must be unique across all providers (if provided)
