#!/bin/sh

echo "==> Publishing Filament assets..."
php artisan filament:assets

echo "==> Caching config/routes/views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations..."
php artisan migrate --force || echo "Migration failed, skipping"

echo "==> Starting server on port ${PORT:-8080}..."
export PHP_CLI_SERVER_WORKERS=4
exec php -S 0.0.0.0:${PORT:-8080} -t public public/index.php
