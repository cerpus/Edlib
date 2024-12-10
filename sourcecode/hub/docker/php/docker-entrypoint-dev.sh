#!/bin/sh
set -ex

update-ca-certificates

if [ "$1" = "php-fpm" ]; then
    chmod -R o+w bootstrap/cache storage
    composer install
    php artisan migrate --force
    php artisan scout:index 'App\Models\Content'
    php artisan scout:sync-index-settings
fi

exec "$@"
