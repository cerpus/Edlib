#!/bin/sh
set -e

php artisan optimize

exec docker-php-entrypoint $@
