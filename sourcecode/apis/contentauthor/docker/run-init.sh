#!/bin/bash

set -eux

mkdir -p /storage/storage /storage/h5plibs /storage/storage/app /storage/storage/logs /storage/storage/framework /storage/storage/framework/cache /storage/storage/framework/sessions /storage/storage/framework/views
chown www-data:www-data /storage/storage /storage/h5plibs /storage/storage/app /storage/storage/logs /storage/storage/framework /storage/storage/framework/cache /storage/storage/framework/sessions /storage/storage/framework/views
chown -R www-data:www-data /app/bootstrap
cd /app
sudo -E -u www-data php artisan cerpus:copy-remote-libraries