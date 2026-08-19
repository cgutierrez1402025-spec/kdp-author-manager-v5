#!/bin/sh

# Ensure vendor exists and migrations are run
cd /app

# Install dependencies if missing
if [ ! -d "/app/vendor" ] || [ ! -f "/app/vendor/autoload.php" ]; then
    echo "[ENTRYPOINT] Installing Composer dependencies..."
    composer install --prefer-dist --no-interaction --ignore-platform-reqs 2>&1 | tail -10 || true
fi

# Cache config if artisan exists
if [ -f "/app/artisan" ]; then
    echo "[ENTRYPOINT] Caching configuration..."
    php /app/artisan config:cache 2>&1 || echo "[ENTRYPOINT] Config cache failed, continuing..."
fi

# Execute supervisord
echo "[ENTRYPOINT] Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
