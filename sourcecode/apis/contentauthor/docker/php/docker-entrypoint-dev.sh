#!/bin/sh
set -e

if [ "$1" = "php-fpm" ]; then
    composer install
    chmod -R o+w bootstrap/cache storage
fi

exec "$@"
