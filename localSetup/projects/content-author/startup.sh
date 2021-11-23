#!/bin/bash

set -eux

update-ca-certificates
/start-scripts/wait-for-multiple.sh mysql:3306 nginx:80
cd /app
mkdir -p storage/framework/cache storage/framework/views storage/framework/sessions
composer install
php artisan migrate --force
chown -R www-data:www-data /app/storage /app/public
php-fpm -R -F -O
