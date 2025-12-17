<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking Payment #68...\n\n";

$payment = \App\Models\Payment::find(68);
if (!$payment) {
    echo "Payment not found!\n";
    exit;
}

echo "Before verification:\n";
echo "  Status: {$payment->status->value}\n";
echo "  Transaction: {$payment->transaction_ref}\n\n";

try {
    $service = app(\App\Services\QiCardPaymentService::class);
    $result = $service->verifyPayment($payment->transaction_ref);
    
    echo "QiCard Status: " . ($result['status'] ?? 'unknown') . "\n\n";
    
    $payment->refresh();
    echo "After verification:\n";
    echo "  Status: {$payment->status->value}\n";
    echo "  Paid At: " . ($payment->paid_at ? $payment->paid_at->toDateTimeString() : 'Not paid') . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
