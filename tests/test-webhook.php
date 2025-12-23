<?php

/**
 * Test QiCard Webhook
 * 
 * This script simulates a QiCard webhook call to test if your endpoint is working.
 * Run: php tests/test-webhook.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

echo "=== QiCard Webhook Test ===\n\n";

// Get the latest pending payment
$payment = \App\Models\Payment::where('status', 'pending')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$payment) {
    echo "❌ No pending payment found. Create a payment first.\n";
    exit(1);
}

echo "✅ Found pending payment:\n";
echo "   Payment ID: {$payment->id}\n";
echo "   Transaction Ref: {$payment->transaction_ref}\n";
echo "   Amount: {$payment->amount}\n";
echo "   Current Status: {$payment->status->value}\n";
echo "   Booking ID: {$payment->booking_id}\n\n";

// Simulate QiCard webhook payload (SUCCESS)
$webhookUrl = env('APP_URL') . '/api/customer/payment/webhook';
$successPayload = [
    'paymentId' => $payment->transaction_ref,
    'orderId' => $payment->transaction_ref,
    'status' => 'SUCCESS',
    'amount' => $payment->amount,
    'currency' => 'IQD',
    'timestamp' => now()->toISOString(),
];

echo "📡 Sending SUCCESS webhook to: {$webhookUrl}\n";
echo "   Payload: " . json_encode($successPayload, JSON_PRETTY_PRINT) . "\n\n";

try {
    $response = Http::post($webhookUrl, $successPayload);
    
    echo "📥 Response Status: {$response->status()}\n";
    echo "   Response Body: " . $response->body() . "\n\n";
    
    if ($response->successful()) {
        echo "✅ Webhook call successful!\n\n";
        
        // Check if payment was updated
        $payment->refresh();
        echo "📊 Payment Status After Webhook:\n";
        echo "   Status: {$payment->status->value}\n";
        echo "   Paid At: " . ($payment->paid_at ? $payment->paid_at->toDateTimeString() : 'null') . "\n\n";
        
        // Check booking status
        $booking = $payment->booking;
        echo "📊 Booking Status After Webhook:\n";
        echo "   Booking ID: {$booking->id}\n";
        echo "   Payment Status: {$booking->payment_status}\n";
        echo "   Status ID: {$booking->status_id}\n";
        
        if ($booking->status) {
            echo "   Status Name: {$booking->status->name}\n";
        }
        
        echo "   Confirmed At: " . ($booking->confirmed_at ? $booking->confirmed_at->toDateTimeString() : 'null') . "\n";
        
        if ($payment->status->value === 'completed' && $booking->payment_status === 'completed') {
            echo "\n🎉 SUCCESS! Both payment and booking were updated correctly!\n";
        } else {
            echo "\n⚠️  WARNING: Status not updated as expected\n";
        }
    } else {
        echo "❌ Webhook call failed!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "   Trace: {$e->getTraceAsString()}\n";
}

echo "\n=== Check logs for details: storage/logs/laravel.log ===\n";
