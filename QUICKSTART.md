# ⚡ Quick Start Guide

Get the Sports Booking System up and running in 5 minutes!

## Prerequisites Check

```bash
# Check PHP version (needs 8.2+)
php -v

# Check Composer
composer -V

# Check Node.js
node -v
```

## 🚀 Installation (3 Steps)

### Step 1: Install Dependencies & Sanctum

```bash
# Install PHP dependencies
composer install

# Install Laravel Sanctum (REQUIRED for auth)
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Install Node dependencies
npm install
```

### Step 2: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate
```

**Edit `.env` file** - Set your database:

```env
DB_DATABASE=booking_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 3: Setup Database

```bash
# Create database (MySQL example)
mysql -u root -p -e "CREATE DATABASE booking_system;"

# Run migrations
php artisan migrate

# Seed sample data (optional)
php artisan db:seed
```

## ✅ You're Done! Start the Server

```bash
php artisan serve
```

Visit: **http://localhost:8000**

---

## 🧪 Test the API

### 1. Send OTP

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"phone":"+1234567890"}'
```

### 2. Get OTP Code from Logs

```bash
tail -f storage/logs/laravel.log
```

Look for: `OTP Code for +1234567890: 123456`

### 3. Verify OTP & Login

```bash
curl -X POST http://localhost:8000/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"phone":"+1234567890","code":"123456","name":"John Doe"}'
```

You'll get back a **token** - save it!

### 4. Use Token to Get User Info

```bash
curl http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 📱 Import to Postman (Easy Testing)

1. Open Postman
2. Import `postman_collection.json`
3. Set `base_url` variable to `http://localhost:8000`
4. Test all endpoints!

---

## 🐛 Common Issues

### "Class HasApiTokens not found"

**Fix:** Install Sanctum

```bash
composer require laravel/sanctum
```

### Database connection error

**Fix:** Check `.env` file database credentials

### Permission errors

**Fix:** Set permissions

```bash
chmod -R 775 storage bootstrap/cache
```

---

## 📚 What's Included?

✅ **11 Database Tables** - Users, venues, bookings, payments, etc.  
✅ **OTP Authentication** - Phone-based login  
✅ **API Routes** - RESTful API ready  
✅ **Complete Models** - All relationships defined  
✅ **Enums** - Type-safe status values  
✅ **Seeders** - Sample data for testing

---

## 📖 Read More

- **Full Setup**: `SETUP_GUIDE.md`
- **API Docs**: `API_DOCUMENTATION.md`
- **Project Details**: `PROJECT_SUMMARY.md`

---

## 🎯 Next Steps

1. ✅ Install and test (you're here!)
2. 🔧 Implement SMS service for real OTP delivery
3. 🏢 Build venue management endpoints
4. 📅 Add booking logic
5. 💳 Integrate payment gateway
6. 🎨 Build frontend

---

**Need help?** Check the other documentation files or Laravel docs at https://laravel.com/docs

Happy coding! 🚀
