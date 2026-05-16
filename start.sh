#!/bin/sh

echo "==> Publishing Filament assets..."
php artisan filament:assets

echo "==> Caching config/routes/views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations..."
php artisan migrate --force || echo "Migration failed, skipping"

echo "==> Starting FrankenPHP on port ${PORT:-8080}..."
exec frankenphp run --config /etc/caddy/Caddyfile
