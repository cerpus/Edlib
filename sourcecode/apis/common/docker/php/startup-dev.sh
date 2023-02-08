#!/bin/sh
set -e

composer install
chmod -R o+w bootstrap/cache storage
php artisan migrate --force

exit 0
