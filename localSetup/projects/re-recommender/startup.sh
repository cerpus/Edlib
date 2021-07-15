#!/bin/bash

/start-scripts/wait-for-multiple.sh mysql:3306 redis:6379

chown -R www-data:www-data /app/storage /app/bootstrap /app/public

bash -c "set -eux; cd /app; composer install; php artisan migrate --force; php-fpm7.4 -R -F -O"
