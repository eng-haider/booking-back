<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing OTP Service ===\n\n";

$otpService = app(\App\Services\OtpService::class);

// Test with different phone numbers
$testPhones = [
    '+9647700000000',
    '+9647700000001',
    '+9647712345678',
    '+9647798765432',
];

echo "📱 Testing OTP Generation:\n";
echo str_repeat("-", 50) . "\n\n";

foreach ($testPhones as $phone) {
    echo "Phone: $phone\n";
    $result = $otpService->generateOtp($phone);
    echo "  ✅ OTP: " . $result['otp'] . "\n";
    echo "  📅 Expires: " . $result['expires_at']->toDateTimeString() . "\n\n";
}

echo str_repeat("=", 50) . "\n\n";
echo "📝 Summary:\n";
echo "  • ALL phone numbers receive OTP: 123456\n";
echo "  • OTP is valid for 10 minutes\n";
echo "  • This is for DEMO/TESTING purposes only\n\n";

echo "🔐 To verify OTP, use:\n";
echo "  Phone: +9647700000000\n";
echo "  Code: 123456\n\n";

// Test verification
echo "Testing OTP Verification...\n";
$isValid = $otpService->verifyOtp($testPhones[0], '123456');
echo $isValid ? "  ✅ Verification successful!\n" : "  ❌ Verification failed!\n";
