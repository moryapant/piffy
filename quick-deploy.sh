#!/bin/bash

echo "🚀 Quick Shared Hosting Deployment"
echo "=================================="

# Check PHP version
echo "🔍 Checking PHP version..."
php -v | head -1

# Install dependencies (ignoring platform requirements that might not be available)
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-sodium --ignore-platform-req=ext-zip --ignore-platform-req=ext-gd --no-interaction

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache 2>/dev/null || true

# Generate app key if missing
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Generating app key..."
    php artisan key:generate --force
fi

# Cache configurations
echo "⚡ Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets if npm is available
if command -v npm &> /dev/null && [ -f "package.json" ]; then
    echo "🏗️  Building frontend assets..."
    npm ci
    npm run build
    rm -rf node_modules
fi

# Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo "✅ Deployment completed!"
echo "⚠️  Note: Running without sodium extension - Firebase auth may be limited"
echo "💡 Contact your hosting provider to enable ext-sodium for full functionality"