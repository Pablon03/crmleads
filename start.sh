#!/bin/sh

echo "==> Running migrations..."
php artisan migrate --force || echo "Migration failed, skipping"

echo "==> Starting server on port ${PORT:-8080}..."
exec php -S 0.0.0.0:${PORT:-8080} -t public public/index.php
