#!/bin/bash

# Exit on error (but handle gracefully)
set -e

echo "========================================"
echo "Starting Mendaur API deployment..."
echo "========================================"

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

# ========================================
# CRITICAL: Clear bootstrap cache FIRST
# This prevents "Class 'config' does not exist" error
# ========================================
echo "Clearing bootstrap cache files..."
rm -rf bootstrap/cache/*.php 2>/dev/null || true
rm -rf bootstrap/cache/config.php 2>/dev/null || true
rm -rf bootstrap/cache/routes*.php 2>/dev/null || true
rm -rf bootstrap/cache/packages.php 2>/dev/null || true
rm -rf bootstrap/cache/services.php 2>/dev/null || true
mkdir -p bootstrap/cache
chmod -R 775 bootstrap/cache 2>/dev/null || true
chmod -R 775 storage 2>/dev/null || true
echo "Bootstrap cache cleared!"

# Generate APP_KEY if not set (use --no-interaction)
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force --no-interaction
fi

# Clear Laravel caches before any artisan commands
echo "Pre-clearing Laravel caches..."
php artisan config:clear --no-interaction 2>/dev/null || true
php artisan cache:clear --no-interaction 2>/dev/null || true
php artisan route:clear --no-interaction 2>/dev/null || true

# Wait for database to be ready (max 60 seconds with 2 second intervals)
echo "Waiting for database connection..."
DB_READY=0
for i in {1..60}; do
    # Use direct PDO connection test instead of artisan
    if php -r "
        \$host = getenv('DB_HOST') ?: 'localhost';
        \$port = getenv('DB_PORT') ?: '3306';
        \$db = getenv('DB_DATABASE') ?: 'mendaur';
        \$user = getenv('DB_USERNAME') ?: 'root';
        \$pass = getenv('DB_PASSWORD') ?: '';
        try {
            \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, [PDO::ATTR_TIMEOUT => 3]);
            echo 'connected';
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null | grep -q "connected"; then
        echo "Database connected successfully!"
        DB_READY=1
        break
    fi
    echo "Attempt $i/60 - Database not ready yet..."
    sleep 2
done

if [ "$DB_READY" -eq 0 ]; then
    echo "WARNING: Database connection not verified after 60 attempts."
    echo "Proceeding with deployment anyway..."
fi

# Run migrations with better error handling
echo "Running database migrations..."
if ! php artisan migrate --force --no-interaction 2>&1; then
    echo "Migration warning - continuing anyway (may already be up to date)"
fi

# Create storage link for public access
echo "Creating storage symbolic link..."
php artisan storage:link --force --no-interaction 2>/dev/null || true

# Run seeders only on first deployment (check if users table is empty)
echo "Checking if seeding is needed..."
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1 || echo "0")
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "Running database seeders..."
    php artisan db:seed --force --no-interaction 2>/dev/null || true
    echo "Seeding completed!"
else
    echo "Database already has data (${USER_COUNT} users), skipping seeder."
fi

# Clear all caches again before rebuilding
echo "Clearing caches for rebuild..."
php artisan config:clear --no-interaction 2>/dev/null || true
php artisan cache:clear --no-interaction 2>/dev/null || true
php artisan route:clear --no-interaction 2>/dev/null || true
php artisan view:clear --no-interaction 2>/dev/null || true

# Cache configurations for performance (skip if fails)
echo "Caching configurations for performance..."
php artisan config:cache --no-interaction 2>/dev/null || echo "Config cache skipped"
php artisan route:cache --no-interaction 2>/dev/null || echo "Route cache skipped"

echo "========================================"
echo "Deployment complete!"
echo "========================================"

# Start the server with custom PHP settings
echo "Starting Laravel server on port $PORT..."
exec php -d upload_max_filesize=10M -d post_max_size=12M -d max_execution_time=60 -d memory_limit=256M artisan serve --host=0.0.0.0 --port=$PORT
