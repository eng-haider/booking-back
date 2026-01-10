#!/bin/bash

echo "================================================"
echo "🚀 PRODUCTION SERVER - QiCard Fix Deployment"
echo "================================================"
echo ""
echo "Run this script ON THE PRODUCTION SERVER at:"
echo "/home/u357511305/domains/tctate.com/public_html/booking_back"
echo ""
read -p "Press ENTER to continue or Ctrl+C to cancel..."
echo ""

# Step 1: Pull latest code
echo "📥 Step 1: Pulling latest code from GitHub..."
git pull origin main
if [ $? -eq 0 ]; then
    echo "✅ Code pulled successfully"
else
    echo "❌ Failed to pull code. Check git access."
    exit 1
fi
echo ""

# Step 2: Install/Update dependencies
echo "📦 Step 2: Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
echo "✅ Dependencies installed"
echo ""

# Step 3: Clear ALL caches (CRITICAL!)
echo "🧹 Step 3: Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "✅ All caches cleared"
echo ""

# Step 4: Verify configuration
echo "🔍 Step 4: Verifying QiCard configuration..."
echo ""
echo "API URL: $(php artisan tinker --execute="echo config('services.qicard.api_url');")"
echo "Username: $(php artisan tinker --execute="echo config('services.qicard.username');")"
echo "Terminal ID: $(php artisan tinker --execute="echo config('services.qicard.terminal_id');")"
echo "Password Set: $(php artisan tinker --execute="echo !empty(config('services.qicard.password')) ? 'YES' : 'NO';")"
echo ""

# Step 5: Test QiCard connection
echo "🧪 Step 5: Testing QiCard connection..."
php artisan qicard:test
echo ""

# Step 6: Set proper permissions
echo "🔐 Step 6: Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || echo "⚠️  Could not change ownership (may need sudo)"
echo "✅ Permissions set"
echo ""

echo "================================================"
echo "✅ DEPLOYMENT COMPLETE!"
echo "================================================"
echo ""
echo "📋 Next Steps:"
echo "1. Try the payment API endpoint again"
echo "2. If still getting 403, check the logs:"
echo "   tail -f storage/logs/laravel.log"
echo ""
echo "3. Or check config via API:"
echo "   curl https://apibooking.tctate.com/api/config/check-qicard?test=1 \\"
echo "     -H 'X-Config-Check-Token: base64:tY/xkLRY9AklnPqav+YRRIrpWF42/dSl/WCjA5C137I='"
echo ""
