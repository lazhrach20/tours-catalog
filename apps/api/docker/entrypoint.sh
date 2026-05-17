#!/bin/sh
set -e

if [ "${OPCACHE_ENABLE:-1}" = "0" ]; then
  docker-php-ext-disable opcache 2>/dev/null || true
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

if [ -f artisan ] && [ -f composer.json ]; then
  # Dev bind-mount: install only when vendor is missing (avoid slow autoload on every start).
  if [ ! -f vendor/autoload.php ] || [ ! -d vendor/laravel/sanctum ]; then
    echo "==> Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --no-progress
    php artisan package:discover --ansi 2>/dev/null || true
  fi

  if [ -f .env ] && { [ -z "${APP_KEY:-}" ] || [ "${#APP_KEY}" -lt 20 ]; }; then
    echo "==> Generating APP_KEY..."
    php artisan key:generate --force --no-interaction
  fi

  if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
    php artisan migrate --force --no-interaction
  fi

  if [ "${AUTO_SEED:-1}" = "1" ]; then
    php artisan catalog:seed-if-empty --no-interaction
  fi

  php artisan storage:link --force --no-interaction 2>/dev/null || true
fi

php-fpm -D
exec "$@"
