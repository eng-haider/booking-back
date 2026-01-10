#!/bin/bash

# QiCard Production Fix Script
# Run this on your production server

echo "🔧 Fixing QiCard Payment Gateway Configuration on Production"
echo "============================================================"
echo ""

# Step 1: Clear all caches
echo "📦 Step 1: Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "✅ Caches cleared"
echo ""

# Step 2: Test QiCard connection
echo "🧪 Step 2: Testing QiCard connection..."
php artisan qicard:test
echo ""

# Step 3: Check configuration
echo "📋 Step 3: Verifying configuration..."
php artisan tinker --execute="
    echo 'API URL: ' . config('services.qicard.api_url') . PHP_EOL;
    echo 'Username: ' . config('services.qicard.username') . PHP_EOL;
    echo 'Password: ' . (config('services.qicard.password') ? str_repeat('*', strlen(config('services.qicard.password'))) : 'NOT SET') . PHP_EOL;
    echo 'Terminal ID: ' . config('services.qicard.terminal_id') . PHP_EOL;
    echo 'Webhook URL: ' . config('services.qicard.webhook_url') . PHP_EOL;
    echo 'Return URL: ' . config('services.qicard.return_url') . PHP_EOL;
"
echo ""

echo "🎉 Configuration check complete!"
echo ""
echo "If you see any 'NOT SET' values above, check your .env file."
echo "If the qicard:test command failed, verify your credentials with QiCard support."
