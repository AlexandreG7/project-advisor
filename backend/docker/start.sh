#!/bin/sh
set -e

# Default port if Railway doesn't inject one
export PORT="${PORT:-8080}"

# Render nginx config from template (substitutes ${PORT})
envsubst '${PORT}' < /app/docker/nginx.conf.template > /etc/nginx/http.d/default.conf

# Remove default nginx config if present
rm -f /etc/nginx/conf.d/default.conf

# Run DB migrations (non-blocking — fails gracefully if DB not ready yet)
php bin/console doctrine:migrations:migrate --no-interaction --env=prod --allow-no-migration 2>&1 || \
    echo "[start.sh] Migration skipped or failed — continuing"

# Warm up Symfony cache
php bin/console cache:warmup --env=prod --no-debug 2>&1 || \
    echo "[start.sh] Cache warmup failed — continuing"

echo "[start.sh] Starting PHP-FPM + Nginx on port ${PORT}"
exec /usr/bin/supervisord -c /app/docker/supervisord.conf
