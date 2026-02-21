#!/bin/sh
set -e

echo "==> Clearing config cache..."
php artisan config:clear || true

echo "==> Caching config..."
php artisan config:cache || true

echo "==> Running migrations..."
php artisan migrate --force || echo "WARNING: Migration failed, continuing anyway"

echo "==> Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
