#!/bin/sh
set -ex

php artisan migrate --force
php artisan scout:index 'App\Models\Content'
php artisan scout:sync-index-settings
