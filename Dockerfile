# Stage 1: Builder
FROM php:8.3-cli-alpine AS builder

RUN apk add --no-cache \
    autoconf \
    build-base \
    curl \
    git \
    zip \
    unzip \
    ca-certificates \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zlib-dev \
    libzip-dev \
    postgresql-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_pgsql \
    mbstring \
    zip \
    xml \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction

# Stage 2: Runtime
FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    curl \
    libpng \
    libjpeg-turbo \
    freetype \
    oniguruma \
    libxml2 \
    libzip \
    libpq

# Copy PHP extensions
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

WORKDIR /app

# Copy app
COPY --from=builder /app /app

# Permissions
RUN chown -R www-data:www-data /app && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Copy entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080

# Healthcheck
HEALTHCHECK CMD curl -f http://localhost:${PORT:-8080} || exit 1

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]