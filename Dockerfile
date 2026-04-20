# Stage 1: Builder (PHP + Node.js for frontend + backend dependencies)
FROM php:8.3-alpine AS builder

# Install Node.js and build tools
RUN apk add --no-cache \
    nodejs \
    npm

# Install build dependencies and PHP extension dependencies
RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    build-base \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    zlib-dev \
    libzip-dev \
    postgresql-dev

# Install required PHP extensions for Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    dom \
    fileinfo \
    gd \
    mbstring \
    opcache \
    pdo \
    pdo_pgsql \
    session \
    xml \
    zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Copy required metadata and application source before installing dependencies
COPY package.json package-lock.json composer.json composer.lock ./
COPY . .

# Ensure artisan exists before composer install
RUN if [ ! -f artisan ]; then echo 'artisan file missing; cannot run composer install' >&2; exit 1; fi

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction

# Install Node.js dependencies and build frontend assets
RUN npm ci && npm run build && \
    if [ ! -f public/build/manifest.json ]; then \
      echo 'ERROR: Vite build failed - manifest.json not found at public/build/manifest.json' >&2; \
      ls -la public/build/ 2>&1 || echo 'public/build directory does not exist'; \
      exit 1; \
    fi && \
    echo 'Vite build successful: manifest.json generated at public/build/manifest.json'

# Stage 2: Runtime (Production image)
FROM php:8.3-alpine

# Install runtime dependencies
RUN apk add --no-cache \
    curl \
    libpng \
    libjpeg-turbo \
    freetype \
    oniguruma \
    libxml2 \
    libzip \
    libpq \
    postgresql-libs \
    icu-libs \
    ca-certificates

# Install build dependencies for PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    build-base \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    zlib-dev \
    libzip-dev \
    postgresql-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    dom \
    fileinfo \
    gd \
    mbstring \
    opcache \
    pdo \
    pdo_pgsql \
    session \
    xml \
    zip && \
    apk del .build-deps

# Set PHP configuration for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /app

# Copy from builder stage (built assets + composer dependencies)
COPY --from=builder /app /app

# Set permissions for Laravel
RUN chown -R www-data:www-data /app && \
    chmod -R 755 /app && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Create all required Laravel cache and storage subdirectories with proper permissions
RUN mkdir -p /app/storage/framework/sessions && \
    mkdir -p /app/storage/framework/views && \
    mkdir -p /app/storage/framework/cache && \
    mkdir -p /app/storage/logs && \
    mkdir -p /app/bootstrap/cache && \
    chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080

# Healthcheck
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost:${PORT:-8080} || exit 1

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]