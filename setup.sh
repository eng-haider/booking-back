#!/bin/bash

# Sports Booking System - Setup Script

echo "🚀 Setting up Sports Booking System..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
else
    echo "✅ .env file already exists"
fi

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install

# Install Laravel Sanctum
echo "🔐 Installing Laravel Sanctum..."
composer require laravel/sanctum

# Publish Sanctum configuration
echo "⚙️ Publishing Sanctum configuration..."
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Install NPM dependencies
echo "📦 Installing NPM dependencies..."
npm install

# Run migrations
echo "💾 Running database migrations..."
read -p "Have you configured your database in .env? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]
then
    php artisan migrate
else
    echo "⚠️ Please configure your database in .env and run: php artisan migrate"
fi

# Build assets
echo "🎨 Building assets..."
npm run build

echo "✨ Setup complete!"
echo ""
echo "📚 Next steps:"
echo "1. Configure your database in .env"
echo "2. Run: php artisan migrate (if not done)"
echo "3. Run: php artisan serve"
echo "4. Visit: http://localhost:8000"
echo ""
echo "📖 API Documentation: see API_DOCUMENTATION.md"
