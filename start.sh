#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting Mendaur API deployment..."

# Copy .env from example if not exists
if [ ! -f .env ]; then
    echo "📝 Creating .env from .env.example..."
    cp .env.example .env
fi

# Generate APP_KEY if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
fi

# Wait for database to be ready (max 30 seconds)
echo "⏳ Waiting for database connection..."
for i in {1..30}; do
    if php artisan db:show &>/dev/null; then
        echo "✅ Database connected!"
        break
    fi
    echo "⏳ Attempt $i/30 - Database not ready yet..."
    sleep 1
done

# Run migrations
echo "📦 Running database migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache configurations for performance
echo "⚡ Caching configurations..."
php artisan config:cache
php artisan route:cache

# Start the server
echo "🎉 Starting Laravel server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT
