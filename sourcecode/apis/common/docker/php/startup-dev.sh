#!/bin/sh
set -e

composer install
chmod -R o+w bootstrap/cache storage

echo "Waiting for database to be ready..."

until php artisan app:db-ready; do
    sleep 1
done

php artisan migrate --force

exit 0
