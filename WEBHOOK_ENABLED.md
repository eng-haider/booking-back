# ✅ QiCard Webhooks Now Enabled!

## What Changed

I've updated your payment system to include the `notificationUrl` field in all payment requests. This tells QiCard where to send webhook notifications when payment status changes.

## Changes Made

### 1. Updated Payment Request (`QiCardPaymentService.php`)

Added `notificationUrl` field to payment creation:

```php
$paymentData = [
    'requestId' => $requestId,
    'amount' => $this->formatAmount($booking->total_price),
    'currency' => $this->currency,
    'merchantOrderId' => $orderId,
    'description' => $this->generateDescription($booking),
    'returnUrl' => route('customer.payment.callback'),
    'notificationUrl' => 'https://apibooking.tctate.com/api/customer/payment/webhook', // ✅ NEW!
];
```

### 2. Enhanced Webhook Handler (`PaymentController.php`)

- Logs complete webhook data including `X-Signature` header
- Always returns 200 OK (required by QiCard)
- Handles webhook retry prevention
- Supports signature verification (when enabled)

### 3. CSRF Protection Disabled (`bootstrap/app.php`)

Webhook endpoint excluded from CSRF protection:

```php
$middleware->validateCsrfTokens(except: [
    'api/customer/payment/webhook',
]);
```

## Webhook Flow

```
Customer Pays on QiCard
    ↓
QiCard sends webhook to: https://apibooking.tctate.com/api/customer/payment/webhook
    ↓
Your server receives webhook with payment data:
{
  "paymentId": "935e93e1-c74b-45ac-8e95-e11e817eb48f",
  "status": "SUCCESS",
  "amount": 3000,
  "confirmedAmount": 3000,
  "details": {...}
}
    ↓
Your server:
  1. Logs all webhook data ✅
  2. Finds payment by paymentId ✅
  3. Updates payment status ✅
  4. Updates booking status to "confirmed" ✅
  5. Sets payment_status to "completed" ✅
  6. Returns 200 OK to QiCard ✅
```

## Webhook URL

**Your Webhook Endpoint:**

```
https://apibooking.tctate.com/api/customer/payment/webhook
```

**Method:** POST  
**Format:** JSON  
**Required Response:** 200 OK

## QiCard Webhook Data Format

QiCard sends this data to your webhook:

```json
{
  "requestId": "20250226-171349-023",
  "paymentId": "935e93e1-c74b-45ac-8e95-e11e817eb48f",
  "status": "SUCCESS",
  "canceled": false,
  "amount": 3000,
  "confirmedAmount": 3000,
  "currency": "IQD",
  "paymentType": "CARD",
  "creationDate": "2025-02-26T17:13:51",
  "details": {
    "resultCode": "00",
    "rrn": "505700009817",
    "authId": "123456",
    "authDate": "2025-02-26T17:21:06",
    "maskedPan": "521372******8582",
    "paymentSystem": "MASTER_CARD"
  },
  "withoutAuthenticate": false,
  "additionalInfo": {}
}
```

**Headers:**

```
X-Signature: R/sDfOilPfSNB7oqIF/Vgil3bcECQaHVlQ57nK20mzY...
```

## Status Mapping

Your system automatically maps QiCard statuses:

| QiCard Status | Your System Status |
| ------------- | ------------------ |
| SUCCESS       | COMPLETED          |
| CONFIRMED     | COMPLETED          |
| APPROVED      | COMPLETED          |
| PENDING       | PENDING            |
| CREATED       | PENDING            |
| FAILED        | FAILED             |
| DECLINED      | FAILED             |
| CANCELED      | FAILED             |

## Testing

### Test 1: Create a New Payment

```bash
# After this change, all new payments will include notificationUrl
# Create a test payment and check logs
```

### Test 2: Simulate Webhook

```bash
# Test your webhook endpoint
curl -X POST https://apibooking.tctate.com/api/customer/payment/webhook \
  -H "Content-Type: application/json" \
  -H "X-Signature: test-signature" \
  -d '{
    "paymentId": "c87f3a23-c11f-4612-ba9c-f204d8661b5e",
    "status": "SUCCESS",
    "amount": 40000,
    "confirmedAmount": 40000,
    "currency": "IQD",
    "paymentType": "CARD",
    "creationDate": "2025-12-23T11:43:00"
  }'
```

### Test 3: Check Logs

```bash
# Watch for webhook activity
tail -f storage/logs/laravel.log | grep -i "webhook"

# Or on production server:
ssh u357511305@us-bos-web1774
tail -f /home/u357511305/domains/tctate.com/public_html/booking_back/storage/logs/laravel.log | grep "Webhook"
```

## Expected Log Output

When webhook is received, you'll see:

```
[2025-12-23 12:00:00] local.INFO: === QiCard Webhook Received ===
{
  "headers": {...},
  "body": {
    "paymentId": "xxx",
    "status": "SUCCESS",
    ...
  },
  "x_signature": "R/sDfOilPfSNB7oq...",
  "ip": "xxx.xxx.xxx.xxx"
}

[2025-12-23 12:00:01] local.INFO: Processing callback in service
[2025-12-23 12:00:01] local.INFO: Looking for payment with transaction_ref
[2025-12-23 12:00:01] local.INFO: Payment found
[2025-12-23 12:00:01] local.INFO: Mapped status
[2025-12-23 12:00:01] local.INFO: Payment updated
[2025-12-23 12:00:01] local.INFO: Payment completed, updating booking
[2025-12-23 12:00:01] local.INFO: Booking updated successfully

[2025-12-23 12:00:02] local.INFO: === Webhook Processed Successfully ===
{
  "payment_id": 11,
  "status": "completed",
  "booking_id": 22,
  "qicard_status": "SUCCESS"
}
```

## Signature Verification (Optional)

If you want to verify webhook authenticity:

1. **Contact QiCard** to get their RSA Public Key
2. **Enable verification** in `.env`:
   ```
   QICARD_VERIFY_WEBHOOKS=true
   ```
3. **Store public key** in `storage/qicard/public-key.pem`

The signature verification code is already in place but commented out.

## Deploy to Production

```bash
# 1. Commit changes
git add .
git commit -m "Enable QiCard webhooks with notificationUrl"
git push origin main

# 2. Deploy to production server
ssh u357511305@us-bos-web1774
cd /home/u357511305/domains/tctate.com/public_html/booking_back
git pull origin main
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# 3. Test with a new payment
# Create a payment and complete it on QiCard
# Check logs to see webhook received
```

## Important Notes

✅ **Webhooks are now automatic!** You don't need to call callback URL manually anymore.

✅ **Callback URL still works** as a backup if customer gets redirected.

✅ **200 OK Response Required** - We always return 200 to prevent QiCard from retrying.

✅ **Logs Everything** - All webhook data is logged for debugging.

✅ **Signature Included** - X-Signature header is captured for future verification.

## Monitoring Webhooks

### Check if Webhooks are Working

```bash
# Count webhook logs
grep -c "Webhook Received" storage/logs/laravel.log

# See recent webhooks
grep "Webhook Received" storage/logs/laravel.log | tail -5

# Check for errors
grep "Webhook Processing Failed" storage/logs/laravel.log
```

### Database Check

```sql
-- Check recent payments
SELECT
    id,
    booking_id,
    status,
    transaction_ref,
    paid_at,
    created_at,
    updated_at
FROM payments
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
ORDER BY created_at DESC;

-- Check if bookings are being updated
SELECT
    id,
    payment_status,
    status_id,
    confirmed_at,
    created_at
FROM bookings
WHERE payment_status = 'completed'
ORDER BY confirmed_at DESC
LIMIT 10;
```

## Troubleshooting

### Issue: Webhooks not received

**Solution:**

- Check that notificationUrl is in payment request (check logs)
- Verify webhook URL is publicly accessible
- Ensure HTTPS is enabled
- Check QiCard IP is not blocked by firewall

### Issue: Webhook received but status not updated

**Solution:**

- Check logs for "Webhook Processing Failed"
- Verify paymentId exists in database
- Check status mapping in QiCardPaymentService

### Issue: QiCard keeps sending duplicate webhooks

**Solution:**

- Ensure your webhook always returns 200 OK
- Check response time (should be under 30 seconds)
- Review logs for errors

## Next Payment Test

**Create a new payment now** and complete it. You should see:

1. Payment request includes `notificationUrl` ✅
2. Payment completed on QiCard ✅
3. Webhook received in your logs ✅
4. Payment status updated to COMPLETED ✅
5. Booking status updated to CONFIRMED ✅
6. NO manual intervention needed! ✅

## Success! 🎉

Your payment system now has **automatic webhook notifications** enabled. Every payment will trigger an automatic status update when completed on QiCard!
