# QiCard Payment Gateway - Production Server Fix

## Problem

403 Authentication Error on production server: `apibooking.tctate.com`

## Cause

The production server's `.env` file has incorrect, missing, or outdated QiCard credentials.

## Solution

### Step 1: SSH into Production Server

```bash
ssh user@apibooking.tctate.com
cd /home/u357511305/domains/tctate.com/public_html/booking_back
```

### Step 2: Update .env File on Production

Edit the `.env` file and ensure these values are set correctly:

```bash
QICARD_BASE_URL=https://uat-sandbox-3ds-api.qi.iq/api/v1/
QICARD_USERNAME=paymentgatewaytest
QICARD_PASSWORD=WHaNFE5C3qlChqNbAzH4
QICARD_TERMINAL_ID=237984
QICARD_WEBHOOK_URL=https://apibooking.tctate.com/api/customer/payment/webhook
QICARD_RETURN_URL=https://apibooking.tctate.com/api/customer/payment/callback
QICARD_CANCEL_URL=https://apibooking.tctate.com/api/customer/payment/cancel
QICARD_CURRENCY=IQD
QICARD_VERIFY_WEBHOOKS=false
```

### Step 3: Clear Configuration Cache on Production

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

### Step 4: Test the Connection

Run this command to verify credentials work:

```bash
php artisan qicard:test
```

You should see:

```
✅ Connection successful!
```

### Step 5: Deploy the Fixed Config File

Make sure to deploy the updated `config/services.php` file to production:

```bash
# From your local machine
git add config/services.php app/Console/Commands/TestQiCardConnection.php
git commit -m "Fix QiCard terminal_id configuration and add test command"
git push origin main

# Then on production server, pull the changes
cd /home/u357511305/domains/tctate.com/public_html/booking_back
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan optimize:clear
```

## Alternative: If Credentials Are Different for Production

If production needs different credentials (not sandbox), update these in production's `.env`:

```bash
# For PRODUCTION QiCard API (not sandbox)
QICARD_BASE_URL=https://3ds-api.qi.iq/api/v1/
QICARD_USERNAME=your_production_username
QICARD_PASSWORD=your_production_password
QICARD_TERMINAL_ID=your_production_terminal_id
```

## Quick Checklist

- [ ] SSH into production server
- [ ] Verify `.env` file has correct QICARD\_\* values
- [ ] Run `php artisan config:clear`
- [ ] Run `php artisan qicard:test` to verify
- [ ] Try payment initiation again
