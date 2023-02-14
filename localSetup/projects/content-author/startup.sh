#!/bin/sh

set -eux

update-ca-certificates
composer install
php artisan migrate --force
chown -R www-data:www-data storage public
exec php-fpm -R -F -O
