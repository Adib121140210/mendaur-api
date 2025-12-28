#!/bin/bash

# Exit on error
set -e

echo "Starting Mendaur API deployment..."

# Configure PHP settings for larger file uploads
echo "Configuring PHP settings..."
export PHP_INI_SCAN_DIR="/app"

# Create php.ini override if needed
mkdir -p /tmp/php-conf
cat > /tmp/php-conf/uploads.ini << 'EOF'
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 60
max_input_time = 60
memory_limit = 256M
EOF

# Copy .env from example if not exists
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Generate APP_KEY if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Wait for database to be ready (max 30 seconds)
echo "Waiting for database connection..."
for i in {1..30}; do
    if php artisan db:show &>/dev/null; then
        echo "Database connected!"
        break
    fi
    echo "Attempt $i/30 - Database not ready yet..."
    sleep 1
done

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Create storage link for public access
echo "Creating storage symbolic link..."
php artisan storage:link --force 2>/dev/null || true

# Run seeders only on first deployment (check if users table is empty)
echo "🌱 Checking if seeding is needed..."
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1)
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "Running database seeders..."
    php artisan db:seed --force
    echo "Seeding completed!"
else
    echo "Database already has data (${USER_COUNT} users), skipping seeder."
fi

# Clear caches
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache configurations for performance
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache

# Start the server with custom PHP settings
echo "Starting Laravel server on port $PORT..."
exec php -d upload_max_filesize=10M -d post_max_size=12M -d max_execution_time=60 -d memory_limit=256M artisan serve --host=0.0.0.0 --port=$PORT
