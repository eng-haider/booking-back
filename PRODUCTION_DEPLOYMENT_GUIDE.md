# 🚀 Production Deployment - QiCard Payment Fix

## Quick Summary

Your local test **passed ✅** but production is failing with **403 error**. The credentials are correct, but the production server needs configuration cache cleared.

---

## 🔴 IMMEDIATE FIX - Run on Production Server

### Option 1: SSH Access (Recommended)

```bash
# SSH into production
ssh your_user@apibooking.tctate.com

# Navigate to project
cd /home/u357511305/domains/tctate.com/public_html/booking_back

# Pull latest changes (includes fixed config/services.php)
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader

# Clear ALL caches (CRITICAL!)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear

# Test the connection
php artisan qicard:test

# If test passes ✅, try the payment API again
```

### Option 2: cPanel or File Manager

If you don't have SSH access:

1. **Upload fixed files via cPanel File Manager:**

   - Upload `config/services.php`
   - Upload `app/Console/Commands/TestQiCardConnection.php`

2. **Run Artisan commands via cPanel Terminal or PHP:**
   ```bash
   cd /home/u357511305/domains/tctate.com/public_html/booking_back
   php artisan config:clear
   php artisan cache:clear
   ```

---

## 📝 Clean Your Production .env File

Your `.env` has **duplicate QiCard entries**. Clean it up:

### Remove These Lines (Duplicates):

```bash
# DELETE these duplicate entries (lines 89-95 approximately):
QICARD_BASE_URL=https://uat-sandbox-3ds-api.qi.iq/api/v1/
QICARD_USERNAME=paymentgatewaytest
QICARD_PASSWORD=WHaNFE5C3qlChqNbAzH4
QICARD_TERMINAL_ID=237984
QICARD_PUBLIC_KEY_PATH=storage/qicard/public-key.pem
QICARD_VERIFY_WEBHOOKS=false
QICARD_WEBHOOK_URL=https://ministryofinteriosv2.tctate.com/api/payment/notification
```

### Keep Only This Section (lines 117-131 - but add QICARD_CURRENCY):

```bash
# QI Card Payment Gateway Configuration
QICARD_BASE_URL=https://uat-sandbox-3ds-api.qi.iq/api/v1/
QICARD_USERNAME=paymentgatewaytest
QICARD_PASSWORD=WHaNFE5C3qlChqNbAzH4
QICARD_TERMINAL_ID=237984
QICARD_CURRENCY=IQD
QICARD_VERIFY_WEBHOOKS=false
QICARD_WEBHOOK_URL=https://apibooking.tctate.com/api/customer/payment/webhook
QICARD_RETURN_URL=https://booking.tctate.com/bookings
QICARD_CANCEL_URL=https://booking.tctate.com/bookings
```

---

## 🧪 Verify It Works

After clearing caches, test the connection:

```bash
php artisan qicard:test
```

**Expected output:**

```
Testing QiCard Payment Gateway Configuration...

Configuration:
API URL: https://uat-sandbox-3ds-api.qi.iq/api/v1/
Username: paymentgatewaytest
Password: ********************
Terminal ID: 237984

Testing API connection...
Sending test request to: https://uat-sandbox-3ds-api.qi.iq/api/v1/payment

Response Status: 200
✅ Connection successful!
Response: {"requestId":"TEST-...","paymentId":"...","status":"CREATED",...}
```

---

## 🎯 Test Payment API Endpoint

After fixing, test the actual payment endpoint:

```bash
curl -X POST https://apibooking.tctate.com/api/customer/payments/bookings/48/initiate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{}'
```

**Expected response:**

```json
{
  "success": true,
  "message": "Payment initiated successfully",
  "data": {
    "payment_id": "...",
    "payment_url": "https://uat-sandbox-3ds-api.qi.iq/api/v1/payment/...",
    "amount": 10000,
    "status": "pending"
  }
}
```

---

## 🔍 Troubleshooting

### Still Getting 403 Error?

1. **Check if config cache is really cleared:**

   ```bash
   php artisan config:clear
   php -r "echo config('services.qicard.terminal_id');"
   # Should output: 237984
   ```

2. **Verify .env file was saved:**

   ```bash
   grep "QICARD_TERMINAL_ID" .env
   # Should show: QICARD_TERMINAL_ID=237984
   ```

3. **Check file permissions:**

   ```bash
   ls -la config/services.php
   # Should be readable by web server
   ```

4. **Check PHP-FPM/Apache was restarted:**
   ```bash
   # Restart web server (may need sudo)
   sudo systemctl restart php-fpm
   # OR
   sudo service apache2 restart
   ```

### Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

Look for "QiCard Payment Failed" entries to see the exact error.

---

## 📦 Files Changed (Commit These)

```bash
git add config/services.php
git add app/Console/Commands/TestQiCardConnection.php
git add fix-production-qicard.sh
git commit -m "Fix QiCard terminal_id config and add connection test command"
git push origin main
```

---

## ✅ Success Checklist

- [ ] Pulled latest code to production server
- [ ] Cleared configuration cache (`php artisan config:clear`)
- [ ] Cleaned up duplicate entries in production `.env`
- [ ] Ran `php artisan qicard:test` - Got 200 response ✅
- [ ] Tested payment endpoint - No more 403 error ✅
- [ ] Payment initiation returns payment URL ✅

---

## 🆘 Still Not Working?

If you still get 403 after all these steps:

1. **Contact QiCard Support** - Credentials may have expired
2. **Check IP Whitelist** - QiCard may restrict by IP address
3. **Verify Environment** - Make sure you're using sandbox credentials on UAT environment
4. **Check SSL/TLS** - Ensure server can make HTTPS requests

Need help? Share the output of `php artisan qicard:test` from production server.
