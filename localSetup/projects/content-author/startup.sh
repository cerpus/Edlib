#!/bin/bash

set -eux

update-ca-certificates
cd /app
composer install
php artisan migrate --force
chown -R www-data:www-data /app/storage /app/public
php-fpm -R -F -O
