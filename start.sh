#!/bin/sh

echo "==> Publishing Filament assets..."
php artisan filament:assets

echo "==> Caching config/routes/views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations..."
if ! php artisan migrate --force; then
    echo "!! Migración fallida — se aborta el arranque para no quedar con el esquema a medias."
    exit 1
fi

echo "==> Starting FrankenPHP on port ${PORT:-8080}..."
exec frankenphp run --config /etc/caddy/Caddyfile
