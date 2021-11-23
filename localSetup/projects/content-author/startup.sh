#!/bin/bash

set -eux

update-ca-certificates
/start-scripts/wait-for-multiple.sh mysql:3306 nginx:80
cd /app
chown -R www-data:www-data /app/storage /app/public
composer install
php artisan migrate --force
php-fpm -R -F -O
