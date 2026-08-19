FROM php:8.4-fpm-alpine AS builder

WORKDIR /app

# Install PHP extensions and system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpq-dev \
    oniguruma-dev \
    libzip-dev \
    icu-dev \
    sqlite-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_sqlite \
    mbstring \
    zip \
    intl \
    opcache \
    && docker-php-ext-enable \
    pdo \
    pdo_pgsql \
    pdo_sqlite \
    mbstring \
    zip \
    intl \
    opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files
COPY composer.json ./

# Install PHP dependencies (with dev for build)
RUN composer install --prefer-dist --no-interaction --ignore-platform-reqs 2>&1 | tail -20

# Copy application code
COPY . .

# Create bootstrap cache dir
RUN mkdir -p /app/bootstrap/cache

# Install Node.js and build assets
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# Final production image
FROM php:8.4-fpm-alpine

WORKDIR /app

# Install runtime dependencies only (no build tools)
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpq \
    oniguruma \
    libzip \
    icu \
    sqlite-libs

# Copy PHP configuration and extensions from builder
COPY --from=builder /usr/local/etc /usr/local/etc
COPY --from=builder /usr/local/lib/php /usr/local/lib/php
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

# Copy PHP application from builder (includes vendor)
COPY --from=builder /app /app

# Copy compiled assets from frontend builder
COPY --from=frontend /app/public/build /app/public/build

# Create necessary directories and initialize database
RUN mkdir -p /app/bootstrap/cache /app/storage /app/database \
    && touch /app/database/database.sqlite \
    && chown -R www-data:www-data /app \
    && chmod -R 755 /app/bootstrap/cache /app/storage /app/database

# Copy nginx config
COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose port
EXPOSE 8000

# Set environment
ENV APP_ENV=production

# Run entrypoint
CMD ["/entrypoint.sh"]
