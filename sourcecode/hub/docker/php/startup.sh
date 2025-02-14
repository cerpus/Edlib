#!/bin/sh
set -ex

php artisan migrate --force
php artisan scout:index 'App\Models\Content' --quiet
php artisan scout:sync-index-settings --quiet
