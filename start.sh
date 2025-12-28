#!/bin/bash
# Updated 2025-12-29: Fixed "Class 'config' does not exist" error

# Don't exit on error - handle errors manually
set +e

echo "========================================"
echo "Starting Mendaur API deployment..."
echo "Timestamp: $(date)"
echo "========================================"

# Check PHP version
echo "PHP Version: $(php -v | head -1)"
echo "PORT: ${PORT:-8000}"

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
    cp .env.example .env 2>/dev/null || echo "Warning: Could not copy .env.example"
fi

# ========================================
# CRITICAL: Clear bootstrap cache FIRST
# This prevents "Class 'config' does not exist" error
# ========================================
echo "🧹 AGGRESSIVE CACHE CLEARING (fixing 'config' class error)..."
# Remove ALL bootstrap cache files
rm -rf bootstrap/cache/*.php 2>/dev/null
rm -rf bootstrap/cache/config.php 2>/dev/null
rm -rf bootstrap/cache/routes*.php 2>/dev/null
rm -rf bootstrap/cache/packages.php 2>/dev/null
rm -rf bootstrap/cache/services.php 2>/dev/null

# Remove compiled.php and services.json if they exist
rm -rf bootstrap/cache/compiled.php 2>/dev/null
rm -rf bootstrap/cache/services.json 2>/dev/null

# Recreate required directories
mkdir -p bootstrap/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
chmod -R 775 bootstrap/cache 2>/dev/null
chmod -R 775 storage 2>/dev/null

# Regenerate composer autoloader (CRITICAL for fixing reflection errors)
echo "🔄 Regenerating autoloader..."
composer dump-autoload --optimize --no-dev 2>/dev/null || composer dump-autoload --optimize 2>/dev/null || echo "⚠️  autoload regeneration skipped"

echo "✅ Bootstrap cache cleared & autoloader regenerated!"

# Generate APP_KEY if not set (use --no-interaction)
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force --no-interaction || echo "Warning: key:generate failed"
fi

# Clear Laravel caches before any artisan commands
echo "Pre-clearing Laravel caches..."
php artisan config:clear --no-interaction 2>&1 || echo "config:clear skipped"
php artisan cache:clear --no-interaction 2>&1 || echo "cache:clear skipped"
php artisan route:clear --no-interaction 2>&1 || echo "route:clear skipped"

# Wait for database to be ready (max 10 attempts with 1 second intervals = 10 seconds)
# Keep this short to avoid healthcheck timeout
echo "Waiting for database connection..."
DB_READY=0
for i in $(seq 1 10); do
    echo "Database connection attempt $i/10..."
    if php -r "
        \$host = getenv('DB_HOST') ?: 'localhost';
        \$port = getenv('DB_PORT') ?: '3306';
        \$db = getenv('DB_DATABASE') ?: 'mendaur';
        \$user = getenv('DB_USERNAME') ?: 'root';
        \$pass = getenv('DB_PASSWORD') ?: '';
        echo \"Connecting to \$host:\$port/\$db as \$user...\n\";
        try {
            \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, [PDO::ATTR_TIMEOUT => 3]);
            echo 'connected';
            exit(0);
        } catch (Exception \$e) {
            echo \"Error: \" . \$e->getMessage() . \"\n\";
            exit(1);
        }
    " 2>&1 | grep -q "connected"; then
        echo "✅ Database connected successfully!"
        DB_READY=1
        break
    fi
    sleep 1
done

if [ "$DB_READY" -eq 0 ]; then
    echo "⚠️  WARNING: Database connection not verified after 10 attempts."
    echo "Starting server anyway - migrations will be skipped"
    echo "You can run migrations manually with: php artisan migrate"
fi

# Run migrations ONLY if database is ready
if [ "$DB_READY" -eq 1 ]; then
    echo "Running database migrations..."
    php artisan migrate --force --no-interaction 2>&1 || echo "⚠️  Migration failed - check database connection"
else
    echo "⏭️  Skipping migrations (database not ready)"
fi

# Create storage link for public access
echo "Creating storage symbolic link..."
php artisan storage:link --force --no-interaction 2>&1 || echo "Storage link skipped"

# Skip seeding for faster startup - can be done manually later
echo "Skipping seeder for faster startup..."

# Clear all caches before rebuilding (without errors stopping us)
echo "Clearing caches for rebuild..."
php artisan config:clear --no-interaction 2>&1 || true
php artisan cache:clear --no-interaction 2>&1 || true
php artisan route:clear --no-interaction 2>&1 || true
php artisan view:clear --no-interaction 2>&1 || true

# Skip caching for now - safer for debugging
echo "Skipping config/route cache for debugging..."

echo "========================================"
echo "🚀 Deployment complete! Starting server..."
echo "========================================"

# Start the server with custom PHP settings
# Use ${PORT:-8000} to default to 8000 if PORT is not set
SERVER_PORT="${PORT:-8000}"
echo "🌐 Starting Laravel server on 0.0.0.0:$SERVER_PORT..."
echo "📍 Health check available at: http://0.0.0.0:$SERVER_PORT/api/health"
echo ""

# Use exec to replace bash process with php process
exec php -d upload_max_filesize=10M -d post_max_size=12M -d max_execution_time=60 -d memory_limit=256M artisan serve --host=0.0.0.0 --port="$SERVER_PORT" --no-reload
 