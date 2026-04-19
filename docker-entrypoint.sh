#!/bin/sh
set -e

# Clear caches before caching fresh values
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear || true

# Wait until the database is ready and migrate safely
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
if php -r "require 'vendor/autoload.php'; \$app=require 'bootstrap/app.php'; \$kernel=\$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); exit(Illuminate\Support\Facades\Schema::hasTable('utilisateurs') ? 0 : 1);"; then
  if ! php artisan db:seed --force; then
    echo 'Seeder failed, continuing startup.'
  fi
else
  echo 'Skipping db:seed because table utilisateurs does not exist.'
fi

# Optimize configuration caches safely
php artisan config:cache || echo 'config:cache failed, continuing.'
php artisan route:cache || echo 'route:cache failed, continuing.'
php artisan view:cache || echo 'view:cache failed, continuing.'

# Start the application server
php artisan serve --host 0.0.0.0 --port "$PORT"
