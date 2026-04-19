#!/bin/sh
set -e

# Clear all caches during startup
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Run migrations
php artisan migrate --force

# Seed database if needed
if php -r "require 'vendor/autoload.php'; \$app=require 'bootstrap/app.php'; \$kernel=\$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); exit(Illuminate\Support\Facades\Schema::hasTable('utilisateurs') ? 0 : 1);"; then
  php artisan db:seed --force
else
  echo 'Skipping db:seed because table utilisateurs does not exist.'
fi

# Cache config and routes for production optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the application server
php artisan serve --host 0.0.0.0 --port $PORT
