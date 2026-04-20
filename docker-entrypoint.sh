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

# Clear all stale caches before rebuilding
echo "Clearing stale caches..."
php artisan config:clear || echo "config:clear: skipped or failed, continuing."
php artisan cache:clear || echo "cache:clear: skipped or failed, continuing."
php artisan route:clear || echo "route:clear: skipped or failed, continuing."
php artisan view:clear || echo "view:clear: skipped or failed, continuing."

# Wait until the database is ready and migrate safely
echo "Waiting for database and running migrations..."
MAX_ATTEMPTS=10
COUNT=0
until php artisan migrate --force; do
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
if php -r "require 'vendor/autoload.php'; \$app=require 'bootstrap/app.php'; \$kernel=\$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); exit(Illuminate\Support\Facades\Schema::hasTable('utilisateurs') ? 0 : 1);"; then
  if ! php artisan db:seed --force; then
    echo 'Seeder failed, continuing startup.'
  fi
else
  echo 'Skipping db:seed because table utilisateurs does not exist.'
fi

# Rebuild configuration caches for production (these should be built fresh at runtime)
echo "Building fresh application caches..."
php artisan config:cache || echo 'config:cache failed, continuing.'
php artisan route:cache || echo 'route:cache failed, continuing.'
php artisan view:cache || echo 'view:cache failed, continuing.'

echo "Container startup complete. Starting PHP server..."

# Start the application server
php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
