#!/bin/sh
set -e

if [ "$1" = "php-fpm" ]; then
    update-ca-certificates
    composer install
    chmod -R o+w bootstrap/cache storage
fi

exec "$@"
