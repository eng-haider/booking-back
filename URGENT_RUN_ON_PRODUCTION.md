# ⚠️ URGENT - Production Server Commands Needed

## The 403 Error Will Continue Until You Run These Commands On Production

Your code is already pushed to GitHub, but **the production server hasn't pulled the changes yet**.

---

## 🔴 OPTION 1: SSH Access (Fastest - 2 minutes)

If you have SSH access to the production server:

```bash
# SSH into production
ssh your_user@tctate.com

# Navigate to project directory
cd /home/u357511305/domains/tctate.com/public_html/booking_back

# Run the deployment script
./deploy-qicard-fix.sh

# OR run commands manually:
git pull origin main
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan qicard:test
```

---

## 🟡 OPTION 2: cPanel Terminal (5 minutes)

1. Log into cPanel at your hosting provider
2. Open **Terminal** from cPanel
3. Run these commands:

```bash
cd /home/u357511305/domains/tctate.com/public_html/booking_back
git pull origin main
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 🟢 OPTION 3: cPanel File Manager (10 minutes)

If you can't access terminal:

### Step 1: Clear Config Cache Manually

1. Go to cPanel → File Manager
2. Navigate to: `/home/u357511305/domains/tctate.com/public_html/booking_back/bootstrap/cache/`
3. Delete file: `config.php` (if it exists)

### Step 2: Upload Fixed Files

Download and upload these files via FTP/File Manager:

- `config/services.php`
- `app/Services/QiCardPaymentService.php`
- `app/Console/Commands/TestQiCardConnection.php`
- `app/Http/Controllers/ConfigCheckController.php`
- `routes/api.php`

### Step 3: Verify .env File

1. Open `/home/u357511305/domains/tctate.com/public_html/booking_back/.env`
2. Make sure it has these lines (NO DUPLICATES):

```env
QICARD_BASE_URL=https://uat-sandbox-3ds-api.qi.iq/api/v1/
QICARD_USERNAME=paymentgatewaytest
QICARD_PASSWORD=WHaNFE5C3qlChqNbAzH4
QICARD_TERMINAL_ID=237984
QICARD_CURRENCY=IQD
QICARD_WEBHOOK_URL=https://apibooking.tctate.com/api/customer/payment/webhook
QICARD_RETURN_URL=https://apibooking.tctate.com/api/customer/payment/callback
QICARD_CANCEL_URL=https://apibooking.tctate.com/api/customer/payment/cancel
```

---

## 🧪 Test After Deployment

After running the commands, test with this curl:

```bash
curl -X GET "https://apibooking.tctate.com/api/config/check-qicard?test=1" \
  -H "X-Config-Check-Token: base64:tY/xkLRY9AklnPqav+YRRIrpWF42/dSl/WCjA5C137I="
```

**Expected result:** `"success": true`

---

## ❓ Why Is This Happening?

1. ✅ Your local code has the fixes → **Working fine locally**
2. ✅ Your code is pushed to GitHub → **Confirmed**
3. ❌ Production server hasn't pulled the code → **Still using old code**
4. ❌ Production config cache is stale → **Using cached old config**

**The fix exists, it just needs to be deployed to production!**

---

## 🆘 Still Not Working After Deployment?

If you still get 403 after running the commands, share:

1. Output of: `php artisan qicard:test` (from production)
2. Output of the config check curl command above
3. Last 50 lines of: `tail -50 storage/logs/laravel.log`

This will help diagnose if it's a different issue (expired credentials, IP blocking, etc.)

---

## ⏱️ Time Estimate

- **Option 1 (SSH):** 2 minutes
- **Option 2 (cPanel Terminal):** 5 minutes
- **Option 3 (File Manager):** 10 minutes

**Choose whichever option you have access to and run it NOW to fix the 403 error!**
