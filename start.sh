#!/bin/bash

# Exit on error
set -e

echo "[START] Starting Mendaur API deployment..."

# Copy .env from example if not exists
if [ ! -f .env ]; then
    echo "[ENV] Creating .env from .env.example..."
    cp .env.example .env
fi

# Generate APP_KEY if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "[KEY] Generating APP_KEY..."
    php artisan key:generate --force
fi

# Wait for database to be ready (max 30 seconds)
echo "[DB] Waiting for database connection..."
for i in {1..30}; do
    if php artisan db:show &>/dev/null; then
        echo "[DB] Database connected!"
        break
    fi
    echo "[DB] Attempt $i/30 - Database not ready yet..."
    sleep 1
done

# Run migrations
echo "[MIGRATE] Running database migrations..."
php artisan migrate --force

# Run all seeders
echo "[SEED] Running all database seeders..."
php artisan db:seed --force
echo "[SEED] Seeding completed!"

# Clear caches
echo "[CACHE] Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache configurations for performance
echo "[CACHE] Caching configurations..."
php artisan config:cache
php artisan route:cache

# Start the server
echo "[SERVER] Starting Laravel server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT
