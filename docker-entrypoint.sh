#!/bin/sh
set -e

#!/bin/sh
set -e

# Run Laravel migrations
php artisan migrate --force

# Start Laravel server
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
