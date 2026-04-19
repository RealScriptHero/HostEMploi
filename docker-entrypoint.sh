#!/bin/sh
set -e

php artisan config:clear
php artisan cache:clear
php artisan route:clear

php artisan migrate --force

if php -r "require 'vendor/autoload.php'; \$app=require 'bootstrap/app.php'; \$kernel=\$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); exit(Illuminate\Support\Facades\Schema::hasTable('utilisateurs') ? 0 : 1);"; then
  php artisan db:seed --force
else
  echo 'Skipping db:seed because table utilisateurs does not exist.'
fi

php artisan serve --host 0.0.0.0 --port $PORT
