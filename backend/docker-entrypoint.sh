#!/bin/sh
set -e

if [ ! -f .env ]; then
  cp .env.example .env
fi

until nc -z "${DB_HOST:-127.0.0.1}" "${DB_PORT:-5432}"; do
  echo "Waiting for database ${DB_HOST:-127.0.0.1}:${DB_PORT:-5432}..."
  sleep 2
done

php artisan key:generate --force --no-interaction
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

exec php artisan serve --host=0.0.0.0 --port=8000
