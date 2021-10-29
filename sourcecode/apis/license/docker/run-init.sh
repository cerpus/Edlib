#!/bin/bash
mkdir -p /app/storage/app /app/storage/app/public /app/storage/logs /app/storage/framework /app/storage/framework/cache /app/storage/framework/cache/data /app/storage/framework/sessions /app/storage/framework/views
chown -R www-data:www-data /app/storage
