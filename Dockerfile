# Stage 1: Builder (Node.js + PHP for frontend + backend dependencies)
FROM node:18-alpine AS builder

# Install PHP and PHP dependencies for builder stage
RUN apk add --no-cache \
    php \
    php-cli \
    php-pdo \
    php-pgsql \
    php-mbstring \
    php-zip \
    php-xml \
    php-opcache \
    composer \
    git \
    curl \
    build-base \
    autoconf

WORKDIR /app

# Copy all files
COPY . .

# Install Node.js dependencies and build frontend assets with Vite
RUN npm ci && npm run build

# Install PHP composer dependencies
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction

# Stage 2: Runtime (Production image)
FROM php:8.3-cli-alpine

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
    ca-certificates

# Install PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    build-base \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zlib-dev \
    libzip-dev \
    postgresql-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_pgsql \
    mbstring \
    zip \
    xml \
    opcache && \
    apk del .build-deps

# Set PHP configuration for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /app

# Copy from builder stage (built assets + composer dependencies)
COPY --from=builder /app /app
COPY --from=builder /app/public/build /app/public/build
COPY --from=builder /app/vendor /app/vendor
COPY --from=builder /app/node_modules /app/node_modules

# Set permissions for Laravel
RUN chown -R www-data:www-data /app && \
    chmod -R 755 /app && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create storage directory if it doesn't exist
RUN mkdir -p /app/storage/framework/sessions && \
    mkdir -p /app/storage/framework/views && \
    mkdir -p /app/storage/framework/cache && \
    mkdir -p /app/storage/logs && \
    chown -R www-data:www-data /app/storage

EXPOSE 8080

# Healthcheck
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost:${PORT:-8080} || exit 1

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]