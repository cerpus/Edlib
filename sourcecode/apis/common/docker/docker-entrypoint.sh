#!/bin/sh
set -e

php artisan optimize

echo "Waiting for database to be ready..."

until php artisan app:db-ready; do
    sleep 1
done

php artisan migrate --force

exec docker-php-entrypoint "$@"
