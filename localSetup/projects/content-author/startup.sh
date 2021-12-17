#!/bin/bash

set -eux

update-ca-certificates
/start-scripts/wait-for-multiple.sh mysql:3306 nginx:80 rabbitmq:5672
cd /app
mkdir -p /buckets/main_bucket
composer install
php artisan migrate --force
chown -R www-data:www-data /app/storage /app/public /buckets/main_bucket
php-fpm -R -F -O
