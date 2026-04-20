#!/bin/sh
set -e

# Ensure critical directories exist with proper permissions
echo "Setting up storage and cache directories..."
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/framework/cache
mkdir -p /app/storage/logs
mkdir -p /app/bootstrap/cache

# Fix permissions for storage and bootstrap cache directories
echo "Fixing permissions for Laravel directories..."
chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || echo "Warning: Could not set permissions, continuing."
chown -R www-data:www-data /app/storage /app/bootstrap/cache 2>/dev/null || echo "Warning: Could not set ownership, continuing."

# Verify Redis connectivity if REDIS_URL is set
validate_redis() {
    if [ -z "$REDIS_URL" ] || [ "$REDIS_URL" = "rediss://your-upstash-url-here" ]; then
        echo "✗ Redis not configured (REDIS_URL not set or placeholder)"
        return 1
    fi
    
    # Test Redis connection with a simple timeout
    echo "Testing Redis connection to: ${REDIS_URL%:*@*:*}..."
    
    # Use php to test connection via Predis
    php -r "
    try {
        \$url = getenv('REDIS_URL');
        if (empty(\$url) || strpos(\$url, 'placeholder') !== false) {
            exit(1);
        }
        require 'vendor/autoload.php';
        \$redis = new Predis\Client(\$url);
        \$redis->ping();
        echo 'Redis connection successful' . PHP_EOL;
        exit(0);
    } catch (\Throwable \$e) {
        echo 'Redis connection failed: ' . \$e->getMessage() . PHP_EOL;
        exit(1);
    }
    " 2>/dev/null
    return $?
}

# Clear stale caches before rebuilding
echo "Clearing stale Laravel caches..."
php artisan optimize:clear --quiet 2>/dev/null || true
php artisan config:clear --quiet 2>/dev/null || true
php artisan cache:clear --quiet 2>/dev/null || true
php artisan route:clear --quiet 2>/dev/null || true
php artisan view:clear --quiet 2>/dev/null || true

# Check if Redis is available
if validate_redis; then
    echo "✓ Redis is available - using Redis for caching"
    export CACHE_DRIVER=redis
    export SESSION_DRIVER=redis
    export QUEUE_CONNECTION=redis
else
    echo "⚠ Redis is NOT available - falling back to file-based caching"
    echo "⚠ Update REDIS_URL environment variable with your actual Upstash URL"
    export CACHE_DRIVER=file
    export SESSION_DRIVER=file
    export QUEUE_CONNECTION=sync
fi

# Wait until the database is ready and migrate safely
echo "Waiting for database and running migrations..."
MAX_ATTEMPTS=10
COUNT=0
until php artisan migrate --force 2>/dev/null; do
  COUNT=$((COUNT + 1))
  if [ "$COUNT" -ge "$MAX_ATTEMPTS" ]; then
    echo "Database migration failed after $MAX_ATTEMPTS attempts. Continuing startup without blocking deployment."
    break
  fi
  echo "Database unavailable or migration failed; retrying in 5 seconds... ($COUNT/$MAX_ATTEMPTS)"
  sleep 5
done

# Run seeding only if the utilisateurs table exists, but do not crash deployment on failure
echo "Checking if seeding is needed..."
if php -r "require 'vendor/autoload.php'; \$app=require 'bootstrap/app.php'; \$kernel=\$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); exit(Illuminate\Support\Facades\Schema::hasTable('utilisateurs') ? 0 : 1);" 2>/dev/null; then
  if ! php artisan db:seed --force 2>/dev/null; then
    echo 'Seeder failed, continuing startup.'
  fi
else
  echo 'Skipping db:seed because table utilisateurs does not exist.'
fi

# Rebuild configuration caches for production (with correct drivers based on Redis availability)
echo "Building fresh application caches with correct drivers..."
php artisan config:cache --quiet 2>/dev/null || true
php artisan route:cache --quiet 2>/dev/null || true
php artisan view:cache --quiet 2>/dev/null || true

echo "✓ Container startup complete. Starting PHP server..."

# Start the application server
php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
