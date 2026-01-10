# 🔍 Production QiCard Configuration Checker

## Quick Check Production Configuration

I've added a diagnostic endpoint to help debug the 403 error on production.

### Step 1: Deploy the Code

```bash
git add -A
git commit -m "Add QiCard config checker and improve error logging"
git push origin main
```

Then on production server:

```bash
cd /home/u357511305/domains/tctate.com/public_html/booking_back
git pull origin main
php artisan config:clear
php artisan route:clear
```

### Step 2: Check Configuration on Production

Use this curl command (replace `YOUR_APP_KEY` with your actual APP_KEY from production .env):

```bash
curl -X GET "https://apibooking.tctate.com/api/config/check-qicard" \
  -H "X-Config-Check-Token: base64:tY/xkLRY9AklnPqav+YRRIrpWF42/dSl/WCjA5C137I=" \
  -H "Accept: application/json"
```

This will show you:

- ✅ What values are actually configured on production
- ✅ If configuration is cached
- ✅ Terminal ID value and type

### To test the connection on production:

```bash
curl -X GET "https://apibooking.tctate.com/api/config/check-qicard?test=1" \
  -H "X-Config-Check-Token: base64:tY/xkLRY9AklnPqav+YRRIrpWF42/dSl/WCjA5C137I=" \
  -H "Accept: application/json"
```

This will:

1. Show you the exact configuration values on production
2. Tell you if config is cached
3. Optionally test the actual QiCard API connection

## Summary - What to Do Next:

### Step 1: Deploy the Changes

```bash
git add -A
git commit -m "Add QiCard debugging and config check endpoint"
git push origin main
```

### Step 2: On Production Server

```bash
cd /home/u357511305/domains/tctate.com/public_html/booking_back
git pull origin main
composer install --no-dev
php artisan config:clear
php artisan route:clear
php artisan optimize:clear
```

### Step 3: Check Configuration via API

```bash
curl -X GET "https://apibooking.tctate.com/api/config/check-qicard?test=1" \
  -H "X-Config-Check-Token: base64:tY/xkLRY9AklnPqav+YRRIrpWF42/dSl/WCjA5C137I="
```

This will show you exactly what configuration production is using and test the actual connection.
