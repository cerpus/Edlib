#!/bin/bash

set -eux

update-ca-certificates
/start-scripts/wait-for-multiple.sh mysql:3306 memcached:11211
cd /app
composer install
php artisan migrate --force
php-fpm -R -F -O
