#!/bin/sh
set -e

update-ca-certificates

composer install
chmod -R o+w bootstrap/cache storage

echo "Waiting for database to be ready..."

until php artisan app:db-ready; do
    sleep 1
done

php artisan migrate --force

exec docker-entrypoint.sh "$@"
