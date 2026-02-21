#!/bin/sh

echo "==> PORT=${PORT:-8000}"
echo "==> APP_KEY set: $([ -n "$APP_KEY" ] && echo yes || echo NO - MISSING)"
echo "==> DB_HOST=${DB_HOST:-NOT SET}"

echo "==> Clearing config cache..."
php artisan config:clear 2>&1 || true

echo "==> Caching config..."
php artisan config:cache 2>&1 || true

echo "==> Running migrations in background..."
(timeout 60 php artisan migrate --force 2>&1 | sed 's/^/[migrate] /') &

echo "==> Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
