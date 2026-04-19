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
    tokenizer \
    xml \
    zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Copy application files
COPY . .

# Install Node.js dependencies (use npm install to ensure all devDependencies are installed)
RUN npm install

# Build frontend assets with Vite
RUN npm run build

# Install PHP composer dependencies
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction

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
    tokenizer \
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