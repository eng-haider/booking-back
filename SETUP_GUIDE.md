# Setup Guide - Sports Booking System

This guide will walk you through setting up the Sports Fields, Pools, and Halls Booking System.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.2 or higher**

  ```bash
  php -v
  ```

- **Composer** (PHP dependency manager)

  ```bash
  composer -V
  ```

- **Node.js & NPM** (v18 or higher recommended)

  ```bash
  node -v
  npm -v
  ```

- **MySQL or PostgreSQL** database server

- **Git** (for version control)

## Quick Setup (Automated)

Run the setup script:

```bash
./setup.sh
```

This will:

1. Create .env file from .env.example
2. Install PHP dependencies
3. Install Laravel Sanctum
4. Generate application key
5. Install Node.js dependencies
6. Prompt for database migration
7. Build frontend assets

## Manual Setup

### Step 1: Clone or Download the Project

If you haven't already, get the project files:

```bash
git clone <repository-url>
cd Booking
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

This installs all Laravel and PHP packages defined in `composer.json`.

### Step 3: Install Laravel Sanctum

Laravel Sanctum is required for API authentication:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### Step 4: Configure Environment

Create your environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### Step 5: Database Configuration

Edit the `.env` file and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

**For PostgreSQL:**

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=booking_system
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### Step 6: Create Database

Create the database in MySQL:

```bash
mysql -u root -p
CREATE DATABASE booking_system;
EXIT;
```

Or in PostgreSQL:

```bash
psql -U postgres
CREATE DATABASE booking_system;
\q
```

### Step 7: Run Migrations

Run all database migrations:

```bash
php artisan migrate
```

This will create all the necessary tables:

- users
- venue_types
- venues
- resources
- amenities & amenity_venue
- photos
- bookings
- payments
- reviews
- schedules
- otps

### Step 8: Install Node.js Dependencies

```bash
npm install
```

### Step 9: Build Frontend Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

### Step 10: Start the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Additional Configuration

### App Configuration

In `.env`, you can customize:

```env
APP_NAME="Sports Booking"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC
```

### Sanctum Configuration

Configure Sanctum for API authentication in `.env`:

```env
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1
```

### Optional: Laravel Query Builder

For advanced filtering and search functionality:

```bash
composer require spatie/laravel-query-builder
```

### Optional: File Storage

If you plan to upload venue photos, configure storage:

```bash
php artisan storage:link
```

Add to `.env`:

```env
FILESYSTEM_DISK=public
```

## Testing the Setup

### Test Database Connection

```bash
php artisan migrate:status
```

Should show all migrations as "Ran".

### Test API Endpoints

1. **Health Check**

   ```bash
   curl http://localhost:8000/api/user
   ```

   Should return 401 Unauthenticated (expected without token)

2. **Send OTP**
   ```bash
   curl -X POST http://localhost:8000/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"phone":"+1234567890"}'
   ```
3. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   You should see the OTP code logged here.

## Seeding Sample Data (Optional)

Create a seeder to populate test data:

```bash
php artisan make:seeder VenueTypeSeeder
php artisan make:seeder AmenitySeeder
```

Then run:

```bash
php artisan db:seed
```

## Development Workflow

### Running the Development Server

Option 1 - Simple:

```bash
php artisan serve
```

Option 2 - With Hot Reload:

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Option 3 - Using Laravel's dev command (if configured):

```bash
composer run dev
```

### Checking for Errors

```bash
# View logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Production Deployment

### Before Deploying

1. **Set environment to production:**

   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Build optimized assets:**

   ```bash
   npm run build
   ```

3. **Optimize Laravel:**

   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Set proper permissions:**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

### Environment Variables

Ensure these are properly set in production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your_host
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

## Common Issues & Solutions

### Issue: "Class 'Laravel\Sanctum\HasApiTokens' not found"

**Solution:** Install Sanctum:

```bash
composer require laravel/sanctum
```

### Issue: Migration fails

**Solution:** Check database credentials in .env and ensure database exists.

### Issue: Permission denied on storage

**Solution:** Fix permissions:

```bash
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

### Issue: OTP not working

**Solution:** Check logs in `storage/logs/laravel.log` for the OTP code.

### Issue: CORS errors

**Solution:** Configure CORS in `config/cors.php` and add your frontend domain.

## Next Steps

After setup is complete:

1. **Read API Documentation:** `API_DOCUMENTATION.md`
2. **Implement SMS service** for OTP delivery
3. **Create venue management endpoints**
4. **Add booking logic**
5. **Integrate payment gateway**
6. **Build frontend application**

## Support

For issues or questions:

- Check Laravel documentation: https://laravel.com/docs
- Review the API_DOCUMENTATION.md file
- Check the code comments for implementation details

## Project Structure

```
app/
├── Enums/              # Enum classes (UserRole, VenueStatus, etc.)
├── Http/
│   ├── Controllers/
│   │   └── Api/        # API Controllers
│   └── Requests/
│       └── Auth/       # Validation requests
├── Models/             # Eloquent models
└── Services/           # Business logic (OtpService)

database/
└── migrations/         # Database schema

routes/
└── api.php            # API routes

storage/
└── logs/              # Application logs
```

## License

This project is open-source software licensed under the MIT license.
