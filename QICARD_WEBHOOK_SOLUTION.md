# QiCard Webhook Not Working - Solutions

## Problem

Payment status is not automatically updating after successful payment on QiCard.

## Root Causes

### 1. ❌ Wrong Webhook URL in .env

**Current (Wrong):**

```
QICARD_WEBHOOK_URL=https://apibbb.kindatravel.iq/api/payment/notification
```

**Should be:**

```
QICARD_WEBHOOK_URL=https://apibooking.tctate.com/api/customer/payment/webhook
```

### 2. ❌ QiCard Doesn't Call Webhooks Automatically

QiCard Iraq requires webhook configuration in their merchant dashboard. Without this configuration, they will NOT send automatic notifications.

### 3. ✅ Callback URL Works

The `returnUrl` is already configured correctly:

```
https://apibooking.tctate.com/api/customer/payment/callback
```

This URL is called when the user completes payment and gets redirected back.

## Solutions

### Solution 1: Use Callback URL (Recommended - Already Implemented)

When a customer completes payment, QiCard redirects them to your `returnUrl` (callback endpoint). This endpoint already:

1. ✅ Calls QiCard API to verify payment status
2. ✅ Updates payment status in database
3. ✅ Updates booking status to "confirmed"
4. ✅ Sets `payment_status` to "completed"
5. ✅ Sets `confirmed_at` timestamp

**How it works:**

```
Customer pays → QiCard redirects to callback URL →
Your server verifies status → Updates database →
Shows success page to customer
```

**No action needed** - This is already working in your code!

### Solution 2: Configure QiCard Webhooks (For Server-to-Server)

Contact QiCard support and provide them with your webhook URL:

```
Webhook URL: https://apibooking.tctate.com/api/customer/payment/webhook
Method: POST
```

**Benefits:**

- Payment status updates even if customer closes browser
- More reliable than callback URL
- Works for delayed payments

**Steps:**

1. Contact QiCard Iraq support: support@qi.iq
2. Request webhook configuration for Terminal ID: 237984
3. Provide webhook URL: `https://apibooking.tctate.com/api/customer/payment/webhook`
4. They will configure it in their system

### Solution 3: Add Cron Job for Status Verification (Backup)

For payments that might be missed by both callback and webhook, add a scheduled task to verify pending payments.

Create: `app/Console/Commands/VerifyPendingPayments.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use App\Services\QiCardPaymentService;
use Carbon\Carbon;

class VerifyPendingPayments extends Command
{
    protected $signature = 'payments:verify-pending';
    protected $description = 'Verify status of pending payments';

    public function handle(QiCardPaymentService $paymentService)
    {
        // Get pending payments older than 5 minutes
        $payments = Payment::where('status', PaymentStatus::PENDING)
            ->where('created_at', '>', Carbon::now()->subHours(24))
            ->where('created_at', '<', Carbon::now()->subMinutes(5))
            ->get();

        $this->info("Found {$payments->count()} pending payments to verify");

        foreach ($payments as $payment) {
            try {
                $this->info("Verifying payment #{$payment->id}...");
                $paymentService->verifyPayment($payment->transaction_ref);

                $payment->refresh();
                $this->info("Payment #{$payment->id} status: {$payment->status->value}");
            } catch (\Exception $e) {
                $this->error("Failed to verify payment #{$payment->id}: {$e->getMessage()}");
            }
        }

        $this->info('Done!');
    }
}
```

Add to `app/Console/Kernel.php` or `routes/console.php`:

```php
Schedule::command('payments:verify-pending')->everyFiveMinutes();
```

## Testing

### Test 1: Test Callback URL (Current Flow)

```bash
# After payment, QiCard redirects to:
https://apibooking.tctate.com/api/customer/payment/callback?paymentId=b6b564f1-38ad-4efd-97cc-15ee728c3821

# This should:
# 1. Verify payment with QiCard API
# 2. Update payment status to "completed"
# 3. Update booking status to "confirmed"
# 4. Return success JSON
```

### Test 2: Test Webhook Endpoint

```bash
# Run the test script:
php tests/test-webhook.php

# Or manually test with curl:
curl -X POST https://apibooking.tctate.com/api/customer/payment/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "paymentId": "b6b564f1-38ad-4efd-97cc-15ee728c3821",
    "status": "SUCCESS",
    "amount": 40000,
    "currency": "IQD"
  }'
```

### Test 3: Manual Verification

```bash
# Verify a specific payment:
curl https://apibooking.tctate.com/api/customer/payments/verify/b6b564f1-38ad-4efd-97cc-15ee728c3821 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## Monitoring

### Check Logs

```bash
# Webhook logs:
tail -f storage/logs/laravel.log | grep -i "webhook"

# Callback logs:
tail -f storage/logs/laravel.log | grep -i "callback"

# Payment verification logs:
tail -f storage/logs/laravel.log | grep -i "verification"
```

### Check Database

```sql
-- See recent payments
SELECT id, booking_id, status, transaction_ref, paid_at, created_at
FROM payments
ORDER BY created_at DESC
LIMIT 10;

-- See recent bookings
SELECT id, payment_status, status_id, confirmed_at, created_at
FROM bookings
ORDER BY created_at DESC
LIMIT 10;
```

## Current Status

### ✅ What's Working:

1. Payment creation with QiCard ✅
2. Payment form URL generation ✅
3. Callback endpoint exists ✅
4. Payment verification logic ✅
5. Booking status update logic ✅
6. CSRF protection disabled for webhooks ✅

### ⚠️ What Needs Attention:

1. Update QICARD_WEBHOOK_URL in .env
2. Configure webhooks in QiCard dashboard (optional but recommended)
3. Test that customers are being redirected to callback URL after payment
4. Verify callback endpoint is accessible from QiCard servers

### 🔧 What to Fix Now:

**1. Update .env file:**

```bash
QICARD_WEBHOOK_URL=https://apibooking.tctate.com/api/customer/payment/webhook
```

**2. Deploy changes to production:**

```bash
git add .
git commit -m "Fix webhook endpoint and add comprehensive logging"
git push origin main

# On production server:
cd /path/to/booking-back
git pull
php artisan config:clear
php artisan cache:clear
```

**3. Test payment flow:**

- Create a test booking
- Initiate payment
- Complete payment on QiCard
- Verify you're redirected to callback URL
- Check payment status updated to "completed"
- Check booking status updated to "confirmed"

## Expected Flow

```
1. Customer initiates payment
   ↓
2. Your API creates payment record (status: pending)
   ↓
3. Your API calls QiCard API to create payment
   ↓
4. QiCard returns payment form URL
   ↓
5. Customer completes payment on QiCard
   ↓
6. QiCard redirects to: /api/customer/payment/callback?paymentId=xxx
   ↓
7. Your callback endpoint:
   - Calls QiCard API to verify status
   - Updates payment status to "completed"
   - Updates booking payment_status to "completed"
   - Updates booking status to "confirmed"
   - Sets confirmed_at timestamp
   ↓
8. Returns success response to customer
```

## Contact QiCard Support

**Email:** support@qi.iq  
**Request:** Configure webhook notifications for Terminal ID 237984  
**Webhook URL:** https://apibooking.tctate.com/api/customer/payment/webhook  
**Method:** POST  
**Format:** JSON

**What to ask:**

- Enable webhook notifications for successful payments
- Enable webhook notifications for failed payments
- Confirm webhook payload structure
- Test webhook with sample data
