#!/bin/sh
set -ex

chmod -R o+w bootstrap/cache storage

if [ ! -f "vendor/autoload.php" ]; then
    composer install
fi

if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate --quiet
fi

php artisan storage:link --quiet
php artisan migrate --quiet
php artisan scout:index 'App\Models\Content' --quiet
php artisan scout:sync-index-settings --quiet
